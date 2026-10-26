<?php
// Bump ?ver= on theme style.css and main.js across all static pages.
$root = rtrim($argv[1], '/\\');
$v = $argv[2];
$n = 0;
foreach (array($root . '/docs', $root) as $tree) {
	if (!is_dir($tree)) continue;
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tree, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $f) {
		if (strtolower($f->getFilename()) !== 'index.html') continue;
		$rel = str_replace('\\', '/', substr($f->getPathname(), strlen($tree) + 1));
		if (preg_match('~^(src|tools|screenshots|\.git)/~', $rel)) continue;
		$h = file_get_contents($f->getPathname());
		$x = preg_replace('~fh-collectibles/style\.css\?ver=\d+~', 'fh-collectibles/style.css?ver=' . $v, $h);
		$x = preg_replace('~fh-collectibles/assets/js/main\.js\?ver=[0-9.]+~', 'fh-collectibles/assets/js/main.js?ver=' . $v, $x);
		if ($x !== $h) { file_put_contents($f->getPathname(), $x); $n++; }
	}
}
echo "bumped=$n ver=$v\n";
