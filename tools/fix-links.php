<?php
/**
 * Fix root-absolute page links for GitHub Pages subpath hosting:
 *  - href="/#anchor"  -> href="#anchor"          (same-page anchor)
 *  - href="/target/"  -> relative path if target exists in the tree
 *  - otherwise        -> fall back to the site root (stay on-site)
 *  - drop xmlrpc/rsd head links
 * Run on both the root export and the docs/ copy.
 */
$rootTree = rtrim( str_replace( chr(92), '/', realpath( __DIR__ . '/..' ) ), '/' );
$docsTree = $rootTree . '/docs';
$targets  = array( $rootTree, $docsTree );

foreach ( $targets as $base ) {
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $base, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $f ) {
		if ( 'html' !== $f->getExtension() ) { continue; }
		$path = str_replace( chr(92), '/', $f->getPathname() );
		// When processing the root tree, skip the docs/ subtree (handled separately).
		if ( $base === $rootTree && 0 === strpos( $path, $docsTree . '/' ) ) { continue; }

		$rel   = substr( $path, strlen( $base ) + 1 );
		$dir   = dirname( $rel );
		$depth = ( '.' === $dir ) ? 0 : substr_count( $dir, '/' ) + 1;
		$up    = str_repeat( '../', $depth );
		$root  = ( '' === $up ) ? './' : rtrim( $up, '/' ) . '/';

		$html = file_get_contents( $path );

		// Same-page anchors: href="/#features" -> href="#features".
		$html = preg_replace( '~href="/#([A-Za-z0-9_.-]+)"~', 'href="#$1"', $html );

		// Remaining root-absolute links.
		$html = preg_replace_callback(
			'~href="/([^"]+)"~',
			function ( $m ) use ( $up, $base, $root ) {
				$value = $m[1];
				if ( preg_match( '~^(?:https?:|mailto:|tel:)~', $value ) ) { return $m[0]; }
				$frag = '';
				if ( ( $h = strpos( $value, '#' ) ) !== false ) {
					$frag  = substr( $value, $h );
					$value = substr( $value, 0, $h );
				}
				if ( '' === $value ) { return 'href="' . $root . $frag . '"'; }
				$clean = preg_replace( '/\?.*$/', '', $value );
				if ( file_exists( $base . '/' . $clean ) || file_exists( $base . '/' . $clean . '/index.html' ) ) {
					return 'href="' . $up . $value . $frag . '"';
				}
				return 'href="' . $root . $frag . '"';
			},
			$html
		);

		// Remove service-discovery head links.
		$html = preg_replace( '~<link rel="EditURI[^>]*/?>\s*~', '', $html );
		$html = preg_replace( '~<link rel="wlwmanifest[^>]*/?>\s*~', '', $html );

		// Nav anchors that only exist on the homepage: point them at the homepage
		// from every other page (Home link behaviour).
		if ( 'index.html' !== $rel ) {
			$html = preg_replace( '~href="#(features|products|categories)"~', 'href="' . $up . '#$1"', $html );
		}

		file_put_contents( $path, $html );
	}
	echo "fixed $base\n";
}
echo "done\n";
