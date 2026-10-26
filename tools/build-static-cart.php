<?php
// Injects the SR static commerce bootstrap (window.SR_UP + sr-static-cart.js)
// into every exported HTML page, and rebuilds the checkout redirect stub into
// a real page shell. Idempotent: pages already carrying the marker are skipped.
//
// Usage: php build-static-cart.php <repo-root>

$root = rtrim($argv[1] ?? __DIR__ . '/..', '/\\');
$js_rel = 'wp-content/themes/fh-collectibles/assets/js/sr-static-cart.js';

function trees($root) {
	return array($root . '/docs', $root); // docs first, root last
}

function walk($dir, callable $cb) {
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $f) {
		if (strtolower($f->getFilename()) === 'index.html') $cb($f->getPathname());
	}
}

function is_excluded($path, $treeDir) {
	$rel = str_replace('\\', '/', substr($path, strlen($treeDir) + 1));
	foreach (array('src/', 'docs/', 'tools/', 'screenshots/', '.git/', 'node_modules/') as $skip) {
		if (strpos($rel, $skip) === 0) return true;
	}
	return false;
}

function depth_of($path, $treeDir) {
	$rel = str_replace('\\', '/', substr($path, strlen($treeDir) + 1));
	$segs = explode('/', dirname($rel));
	return ($segs[0] === '.') ? 0 : count($segs);
}

function rebuild_checkout($treeDir) {
	$cart = $treeDir . '/cart/index.html';
	$out  = $treeDir . '/checkout/index.html';
	if (!is_file($cart)) return 0;
	$html = file_get_contents($cart);
	// swap the page content between fh-page__content and </article>
	$start = strpos($html, '<div class="fh-page__content">');
	$end   = strpos($html, '</article>', $start);
	if ($start === false || $end === false) return 0;
	$html = substr($html, 0, $start)
		. '<div class="fh-page__content"><div class="woocommerce"><div id="sr-checkout-root"></div></div></div>' . "\n\t\t\t\t"
		. substr($html, $end);
	$html = str_replace('<h1 class="fh-page__title">Cart</h1>', '<h1 class="fh-page__title">Checkout</h1>', $html);
	$html = str_replace('>Cart<', '>Checkout<', $html); // breadcrumb link text
	$html = preg_replace('~<title>.*?</title>~s', '<title>Checkout &#8211; SR Collectibles</title>', $html, 1);
	file_put_contents($out, $html);
	return 1;
}

$injected = 0; $skipped = 0; $checkout = 0;
foreach (trees($root) as $treeDir) {
	if (!is_dir($treeDir)) continue;
	$checkout += rebuild_checkout($treeDir) ? 1 : 0;
	walk($treeDir, function ($path) use ($treeDir, $js_rel, &$injected, &$skipped) {
		if (is_excluded($path, $treeDir)) return;
		$html = file_get_contents($path);
		if (strpos($html, 'sr-static-cart.js') !== false) { $skipped++; return; }
		$up = str_repeat('../', depth_of($path, $treeDir));
		$boot = "\n<!-- sr-static commerce -->\n"
			. '<script>window.SR_STATIC=1;window.SR_UP=' . "'" . $up . "';</script>\n"
			. '<script src="' . $up . $js_rel . '?v=1"></script>\n';
		$pos = strrpos($html, '</body>');
		if ($pos === false) { $skipped++; return; }
		$html = substr($html, 0, $pos) . $boot . substr($html, $pos);
		file_put_contents($path, $html);
		$injected++;
	});
}
echo "injected=$injected skipped=$skipped checkout_rebuilt=$checkout\n";
