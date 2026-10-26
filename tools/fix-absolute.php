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
	$html = preg_replace( '#(?<![\w.])/(wp-content|wp-includes)/#', $up . '$1/', $html );
	file_put_contents( $path, $html );
	echo "fixed $rel\n";
}
echo "done\n";
