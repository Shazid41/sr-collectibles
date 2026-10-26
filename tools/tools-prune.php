<?php
/**
 * Transitive prune: keep only wp-includes / wp-content files reachable from
 * the HTML pages (following CSS url() references), delete the rest.
 */
$docs = __DIR__ . '/docs';

// 1. Seed: every wp-includes / wp-content path referenced from any HTML file.
$queue = array();
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $docs, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $f ) {
	if ( 'html' !== $f->getExtension() ) { continue; }
	$html = file_get_contents( $f->getPathname() );
	if ( preg_match_all( '#(?:\.\./)*(wp-(?:includes|content)/[^"\' )>]+)#', $html, $m ) ) {
		foreach ( $m[0] as $r ) {
			$r = preg_replace( '/[?#].*$/', '', $r );
			// Normalize ../ segments relative to docs root is approximate: these
			// refs appear with varying depths. Resolve by stripping leading ../ and
			// checking existence under docs/.
			$clean = preg_replace( '#^(\.\./)+#', '', $r );
			if ( file_exists( $docs . '/' . $clean ) ) { $queue[ $clean ] = true; }
		}
	}
}

// 2. Transitive closure through CSS url() references.
$keep = array();
while ( $queue ) {
	foreach ( array_keys( $queue ) as $rel ) {
		unset( $queue[ $rel ] );
		if ( isset( $keep[ $rel ] ) ) { continue; }
		$keep[ $rel ] = true;
		$ext = pathinfo( $rel, PATHINFO_EXTENSION );
		if ( ! in_array( strtolower( $ext ), array( 'css' ), true ) ) { continue; }
		$css = @file_get_contents( $docs . '/' . $rel );
		if ( false === $css ) { continue; }
		$dir = dirname( $rel );
		if ( preg_match_all( '#url\(([^)]+)\)#', $css, $m ) ) {
			foreach ( $m[1] as $u ) {
				$u = trim( trim( $u ), '"\'' );
				if ( preg_match( '#^(https?:|data:|/)#', $u ) ) { continue; }
				// Resolve relative to the CSS file location.
				$base = explode( '/', $dir );
				foreach ( explode( '/', $u ) as $seg ) {
					if ( '' === $seg || '.' === $seg ) { continue; }
					if ( '..' === $seg ) { array_pop( $base ); continue; }
					$base[] = $seg;
				}
				$resolved = implode( '/', $base );
				if ( file_exists( $docs . '/' . $resolved ) && ! isset( $keep[ $resolved ] ) ) {
					$queue[ $resolved ] = true;
				}
			}
		}
	}
}

// 3. Delete non-kept files under wp-includes and wp-content (uploads already pruned).
$kept = $dropped = 0;
foreach ( array( 'wp-includes', 'wp-content' ) as $root ) {
	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $docs . '/' . $root, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $files as $f ) {
		if ( $f->isDir() ) { continue; }
		$rel = str_replace( '\\', '/', substr( $f->getPathname(), strlen( $docs ) + 1 ) );
		if ( isset( $keep[ $rel ] ) ) { $kept++; continue; }
		unlink( $f->getPathname() );
		$dropped++;
	}
	// Clean empty dirs.
	$dirs = array();
	$dit = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $docs . '/' . $root, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $dit as $d ) { if ( $d->isDir() ) { $dirs[] = $d->getPathname(); } }
	foreach ( $dirs as $d ) { @rmdir( $d ); }
}
echo "kept=$kept dropped=$dropped\n";
echo "docs size: ";
$size = 0;
$ait = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $docs, FilesystemIterator::SKIP_DOTS ) );
foreach ( $ait as $f ) { $size += $f->getSize(); }
echo round( $size / 1048576, 1 ), " MB\n";
