<?php
$docs = __DIR__ . '/../docs';
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $docs, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $f ) {
	if ( 'html' !== $f->getExtension() ) { continue; }
	$path = str_replace( chr(92), '/', $f->getPathname() );
	$rel  = substr( $path, strlen( str_replace( chr(92), '/', $docs ) ) + 1 );
	$dir  = dirname( $rel );
	$depth = ( '.' === $dir ) ? 0 : substr_count( $dir, '/' ) + 1;
	$up = str_repeat( '../', $depth );
	$html = file_get_contents( $path );

	// Dead backend endpoints in the static demo: neutralize.
	$html = str_replace(
		array( 'action="http://localhost:8080/c6821575/wp-admin/admin-post.php"', 'action="http://localhost:8080/c6821575/wp-comments-post.php"' ),
		'action="#"',
		$html
	);

	// Root-relative form actions -> relative.
	$html = preg_replace( '#action="/product/([^"]+)"#', 'action="' . $up . 'product/$1"', $html );
	$html = str_replace( 'action="/"', 'action="' . ( '' === $up ? './' : rtrim( $up, '/' ) . '/' ) . '"', $html );

	file_put_contents( $path, $html );
}
echo "done\n";
