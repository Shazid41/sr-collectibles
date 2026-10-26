<?php
/**
 * Post-process Simply Static export in docs/ for GitHub Pages subpath hosting.
 * Pure PHP (Windows-safe). Run: php.exe tools-rewrite.php
 * 1. Remove junk pages.
 * 2. Rewrite root-absolute URLs ("/wp-content/...", "/shop/") to relative paths.
 * 3. Prune unreferenced files under wp-content/uploads.
 */
$docs = __DIR__ . '/docs';

// ---- 1. junk pages ----
foreach ( array( 'hello-world', 'author', 'category', 'my-account', 'wp-sitemap.xsl', 'wp-sitemap-index.xsl' ) as $junk ) {
	$p = $docs . '/' . $junk;
	if ( is_dir( $p ) ) {
		$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $p, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $it as $f ) { $f->isDir() ? @rmdir( $f->getPathname() ) : unlink( $f->getPathname() ); }
		@rmdir( $p );
	} elseif ( file_exists( $p ) ) {
		unlink( $p );
	}
}

// ---- 2. URL rewriting ----
$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $docs, FilesystemIterator::SKIP_DOTS ) );
$refMap = array(); // upload refs for pruning

foreach ( $files as $f ) {
	if ( 'html' !== $f->getExtension() ) { continue; }
	$path  = str_replace( '\\', '/', $f->getPathname() );
	$rel   = substr( $path, strlen( str_replace( '\\', '/', $docs ) ) + 1 );
	$dir   = dirname( $rel );
	$depth = ( '.' === $dir ) ? 0 : substr_count( $dir, '/' ) + 1;
	$up    = str_repeat( '../', $depth );

	$html = file_get_contents( $path );

	// Assets under root-mounted dirs (src/href/action attrs).
	$html = preg_replace( '#(src|href|action)="/(wp-content|wp-includes)#', '$1="' . $up . '$2', $html );

	// url(/wp-...) in inline styles / CSS blocks.
	$html = str_replace( 'url(/wp-content/', 'url(' . $up . 'wp-content/', $html );
	$html = str_replace( 'url(/wp-includes/', 'url(' . $up . 'wp-includes/', $html );

	// Internal page links: href="/something/" -> relative dir link.
	$html = preg_replace_callback(
		'#href="/([^"]*)"#',
		function ( $m ) use ( $up, $docs ) {
			$target = $m[1];
			if ( '' === $target ) {
				return 'href="' . ( '' === $up ? './' : rtrim( $up, '/' ) . '/' ) . '"';
			}
			if ( preg_match( '#^(https?:|mailto:|tel:|//)#', $target ) ) { return $m[0]; }
			$clean = preg_replace( '/\?.*$/', '', $target );
			if ( file_exists( $docs . '/' . $clean ) || file_exists( $docs . '/' . $clean . '/index.html' ) ) {
				return 'href="' . $up . $target . '"';
			}
			return $m[0];
		},
		$html
	);

	file_put_contents( $path, $html );

	// Collect upload references (strip query/fragment, stop at quote/space/).
	if ( preg_match_all( '#wp-content/uploads/[^"\' )>]+#', $html, $mm ) ) {
		foreach ( $mm[0] as $r ) { $refMap[ preg_replace( '/[?#].*$/', '', $r ) ] = true; }
	}
	echo "rewrote $rel (depth $depth)\n";
}

// ---- 3. prune unreferenced uploads ----
if ( is_dir( $docs . '/wp-content/uploads' ) ) {
	$upFiles = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $docs . '/wp-content/uploads', FilesystemIterator::SKIP_DOTS ) );
	$kept = $dropped = 0;
	foreach ( $upFiles as $f ) {
		if ( ! $f->isFile() ) { continue; }
		$rel = 'wp-content/uploads/' . str_replace( '\\', '/', substr( $f->getPathname(), strlen( $docs . '/wp-content/uploads' ) + 1 ) );
		if ( isset( $refMap[ $rel ] ) ) { $kept++; continue; }
		unlink( $f->getPathname() );
		$dropped++;
	}
	// Remove empty dirs, deepest first.
	$dirs = array();
	$dit = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $docs . '/wp-content/uploads', FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $dit as $d ) { if ( $d->isDir() ) { $dirs[] = $d->getPathname(); } }
	foreach ( $dirs as $d ) { @rmdir( $d ); }
	echo "uploads kept=$kept dropped=$dropped\n";
}

// ---- 4. Pages hygiene file ----
file_put_contents( $docs . '/.nojekyll', '' );
echo "done\n";
