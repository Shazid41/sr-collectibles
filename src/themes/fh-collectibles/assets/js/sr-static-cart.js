/* SR Collectibles — static-site commerce layer (GitHub Pages build).
   Provides cart, checkout, search and sorting entirely client-side.
   Activated only when window.SR_STATIC is set by the injected bootstrap. */
(function () {
  'use strict';
  if (!window.SR_STATIC) return;

  var UP = window.SR_UP || '';
  var STORE_KEY = 'sr_cart_v1';
  var QUERY_KEY = 'sr_shop_query';
  var money = function (n) { return '$' + Number(n).toFixed(2); };

  /* ---------------- cart state ---------------- */
  function readCart() {
    try { return JSON.parse(localStorage.getItem(STORE_KEY)) || {}; }
    catch (e) { return {}; }
  }
  function writeCart(c) {
    localStorage.setItem(STORE_KEY, JSON.stringify(c));
    updateBadge();
  }
  function cartEntries() {
    var c = readCart();
    return Object.keys(c).map(Number).filter(function (id) { return c[id] > 0; })
      .map(function (id) { return { id: id, qty: c[id] }; });
  }
  function addToCart(id, qty) {
    var c = readCart();
    c[id] = (c[id] || 0) + (qty || 1);
    writeCart(c);
  }
  function setQty(id, qty) {
    var c = readCart();
    qty = Math.max(0, Math.min(99, qty | 0));
    if (qty === 0) delete c[id]; else c[id] = qty;
    writeCart(c);
  }
  function cartCount() {
    return cartEntries().reduce(function (s, e) { return s + e.qty; }, 0);
  }

  /* ---------------- products ---------------- */
  var productsPromise = null;
  function products() {
    if (!productsPromise) {
      productsPromise = fetch(UP + 'wp-content/uploads/sr-static/products.json')
        .then(function (r) { return r.json(); })
        .then(function (j) { return j.products; })
        .catch(function () { return []; });
    }
    return productsPromise;
  }
  function byId(list, id) {
    for (var i = 0; i < list.length; i++) if (list[i].id === id) return list[i];
    return { id: id, name: 'Item #' + id, price: 0, img: '', url: 'shop/' };
  }

  /* ---------------- header badge ---------------- */
  function updateBadge() {
    var n = cartCount();
    var el = document.querySelector('.fh-cart-count');
    if (!el) return;
    el.textContent = n;
    el.classList.toggle('fh-cart-count--empty', n === 0);
  }

  /* ---------------- toast ---------------- */
  function injectStyles() {
    if (document.getElementById('sr-static-css')) return;
    var s = document.createElement('style');
    s.id = 'sr-static-css';
    s.textContent = [
      '.sr-toast{position:fixed;left:50%;bottom:28px;transform:translate(-50%,20px);z-index:9999;',
      'background:linear-gradient(120deg,#2a0f14,#1c0a0e);border:1px solid rgba(233,198,90,.45);color:#fff;',
      'padding:13px 22px;border-radius:12px;font-size:14.5px;box-shadow:0 14px 40px rgba(0,0,0,.6);',
      'opacity:0;pointer-events:none;transition:opacity .25s,transform .25s;max-width:88vw;text-align:center}',
      '.sr-toast.show{opacity:1;transform:translate(-50%,0)}',
      '.sr-toast a{color:#e9c65a;font-weight:600;margin-left:10px;text-decoration:underline}',
      '.sr-cart-empty{text-align:center;padding:40px 0}',
      '.sr-cart-empty p{font-size:17px;color:#fff;margin:0 0 22px}',
      '.sr-line{display:grid;grid-template-columns:84px 1fr auto;gap:16px;align-items:center;',
      'padding:18px 0;border-bottom:1px solid rgba(255,255,255,.08)}',
      '.sr-line img{width:84px;height:84px;object-fit:cover;border-radius:10px;background:#1a0b0e}',
      '.sr-line__name{color:#fff;font-weight:600;font-size:15px;line-height:1.4;text-decoration:none}',
      '.sr-line__name:hover{color:#e9c65a}',
      '.sr-line__price{color:#b9a89c;font-size:13px;margin-top:4px}',
      '.sr-qty{display:inline-flex;align-items:center;border:1px solid rgba(233,198,90,.35);border-radius:99px;margin-top:10px}',
      '.sr-qty button{width:30px;height:30px;border:0;background:transparent;color:#e9c65a;font-size:16px;cursor:pointer;border-radius:99px}',
      '.sr-qty button:hover{background:rgba(233,198,90,.12)}',
      '.sr-qty span{min-width:34px;text-align:center;color:#fff;font-size:14px}',
      '.sr-line__right{text-align:right}',
      '.sr-line__total{color:#e9c65a;font-weight:700;font-size:16px}',
      '.sr-line__rm{background:none;border:0;color:#8a7078;font-size:12.5px;cursor:pointer;text-decoration:underline;margin-top:8px}',
      '.sr-line__rm:hover{color:#ff7a7a}',
      '.sr-summary{margin-top:26px;display:flex;flex-direction:column;gap:10px;align-items:flex-end}',
      '.sr-summary__row{color:#cbb9b0;font-size:15px}',
      '.sr-summary__row strong{color:#fff;font-size:20px;margin-left:14px}',
      '.sr-checkout__grid{display:grid;gap:34px}',
      '@media(min-width:900px){.sr-checkout__grid{grid-template-columns:1.25fr .85fr}}',
      '.sr-panel{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.09);border-radius:16px;padding:24px}',
      '.sr-panel h2{margin:0 0 18px;font-size:19px;color:#fff;letter-spacing:.02em;text-transform:uppercase}',
      '.sr-field{margin-bottom:14px}',
      '.sr-field label{display:block;font-size:12.5px;color:#b9a89c;margin-bottom:6px;letter-spacing:.04em;text-transform:uppercase}',
      '.sr-field input,.sr-field select{width:100%;box-sizing:border-box;background:#160a0d;border:1px solid rgba(255,255,255,.14);',
      'border-radius:10px;color:#fff;padding:12px 14px;font-size:15px}',
      '.sr-field input:focus,.sr-field select:focus{outline:none;border-color:#e9c65a}',
      '.sr-pay{display:flex;flex-direction:column;gap:8px;margin:6px 0 18px}',
      '.sr-pay label{display:flex;align-items:center;gap:10px;border:1px solid rgba(255,255,255,.12);border-radius:12px;',
      'padding:13px 16px;cursor:pointer;color:#e8e2e3;font-size:14.5px}',
      '.sr-pay label:has(input:checked){border-color:#e9c65a;background:rgba(233,198,90,.07)}',
      '.sr-pay input{accent-color:#e9c65a}',
      '.sr-ordersum__row{display:flex;justify-content:space-between;gap:14px;color:#e8e2e3;font-size:14.5px;padding:7px 0}',
      '.sr-ordersum__row small{color:#9b8a83}',
      '.sr-done{text-align:center;padding:46px 10px}',
      '.sr-done__icon{font-size:46px;color:#e9c65a;margin-bottom:14px}',
      '.sr-done h2{font-size:26px;margin:0 0 10px}',
      '.sr-done p{color:#cbb9b0;margin:6px 0}',
      '.sr-done__num{color:#e9c65a;font-weight:700}',
      '.sr-banner{background:rgba(233,198,90,.09);border:1px solid rgba(233,198,90,.35);color:#f3e7c6;',
      'border-radius:12px;padding:12px 18px;margin:0 0 20px;font-size:14.5px;display:flex;justify-content:space-between;gap:16px;align-items:center}',
      '.sr-banner a{color:#e9c65a}',
      '.sr-mobile-line{display:block}',
      '.woocommerce a.fh-btn--gold, .woocommerce-orders-wrapper a.fh-btn--gold{color:#17070a !important}',
      'a.fh-btn--gold{color:#17070a !important}',
      'a.fh-btn--dark{color:#fff !important}'
    ].join('\n');
    document.head.appendChild(s);
  }

  var toastTimer = null;
  function toast(msg, actionLabel, actionHref) {
    var t = document.querySelector('.sr-toast');
    if (!t) { t = document.createElement('div'); t.className = 'sr-toast'; document.body.appendChild(t); }
    t.innerHTML = msg + (actionLabel ? '<a href="' + actionHref + '">' + actionLabel + '</a>' : '');
    setTimeout(function () { t.classList.add('show'); }, 0);
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { t.classList.remove('show'); }, 2600);
  }

  /* ---------------- interception: add to cart ---------------- */
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a.add_to_cart_button, a[href*="add-to-cart="]');
    if (a) {
      var m = a.getAttribute('href') && a.getAttribute('href').match(/add-to-cart=(\d+)/);
      var id = m ? +m[1] : +(a.getAttribute('data-product_id') || 0);
      if (!id) return;
      e.preventDefault();
      var qty = +(a.getAttribute('data-quantity') || 1);
      addToCart(id, qty);
      products().then(function (list) {
        toast('Added to cart: ' + byId(list, id).name, 'View cart', UP + 'cart/');
      });
      return;
    }
    var rm = e.target.closest && e.target.closest('[data-sr-remove]');
    if (rm) { e.preventDefault(); setQty(+rm.getAttribute('data-sr-remove'), 0); renderCartPage(); return; }
    var inc = e.target.closest && e.target.closest('[data-sr-inc]');
    if (inc) { e.preventDefault(); var i = +inc.getAttribute('data-sr-inc'); setQty(i, (readCart()[i] || 0) + 1); renderCartPage(); return; }
    var dec = e.target.closest && e.target.closest('[data-sr-dec]');
    if (dec) { e.preventDefault(); var d = +dec.getAttribute('data-sr-dec'); setQty(d, (readCart()[d] || 0) - 1); renderCartPage(); return; }
  });

  document.addEventListener('submit', function (e) {
    var f = e.target;
    // Single product add-to-cart form
    if (f.classList.contains('cart')) {
      e.preventDefault();
      var btn = f.querySelector('button[name="add-to-cart"], input[name="add-to-cart"]');
      var id = btn ? +btn.value : 0;
      var q = f.querySelector('input[name="quantity"]');
      var qty = q ? Math.max(1, parseInt(q.value, 10) || 1) : 1;
      if (!id) return;
      addToCart(id, qty);
      products().then(function (list) { toast('Added to cart: ' + byId(list, id).name, 'View cart', UP + 'cart/'); });
      return;
    }
    // Search -> client-side shop filter
    if (f.classList.contains('fh-search')) {
      e.preventDefault();
      var inp = f.querySelector('input[type="search"], input[name="s"]');
      var term = inp ? inp.value.trim() : '';
      try { sessionStorage.setItem(QUERY_KEY, JSON.stringify({ q: term })); } catch (err) {}
      location.href = UP + 'shop/';
      return;
    }
    // Newsletter -> local subscribe
    if (f.classList.contains('fh-newsletter__form')) {
      e.preventDefault();
      var em = f.querySelector('input[type="email"]');
      if (em && /\S+@\S+\.\S+/.test(em.value)) {
        localStorage.setItem('sr_subscriber', em.value);
        toast('Subscribed! You will hear about the next drop first.');
        em.value = '';
      } else {
        toast('Please enter a valid email address.');
      }
      return;
    }
    // Woo ordering form
    if (f.classList.contains('woocommerce-ordering')) {
      e.preventDefault();
      applyOrder(f.querySelector('select[name="orderby"]').value);
      return;
    }
    // Checkout place order
    if (f.id === 'sr-checkout-form') { e.preventDefault(); placeOrder(f); return; }
    // Comment / review form on static: local only
    if (f.id === 'commentform') {
      e.preventDefault();
      toast('Reviews are disabled on the demo build.');
      return;
    }
  });

  /* ---------------- shop: search filter + ordering ---------------- */
  function shopCards() {
    return Array.prototype.slice.call(document.querySelectorAll('.fh-card'));
  }
  function cardId(card) {
    var a = card.querySelector('a[href*="add-to-cart="]');
    if (!a) return 0;
    var m = a.getAttribute('href').match(/add-to-cart=(\d+)/);
    return m ? +m[1] : 0;
  }
  function cardListWrap() {
    var c = document.querySelector('.fh-card');
    return c ? (c.closest('ul') || c.parentElement) : null;
  }
  function applyBanner(html) {
    var old = document.querySelector('.sr-banner');
    if (old) old.remove();
    if (!html) return;
    var wrap = cardListWrap();
    if (!wrap) return;
    var b = document.createElement('div');
    b.className = 'sr-banner';
    b.innerHTML = html + ' <a href="' + UP + 'shop/">Clear</a>';
    wrap.parentNode.insertBefore(b, wrap);
  }
  function applyQueryFilter() {
    var raw = null;
    try { raw = sessionStorage.getItem(QUERY_KEY); sessionStorage.removeItem(QUERY_KEY); } catch (e) { return; }
    if (!raw) return;
    var term = (JSON.parse(raw).q || '').toLowerCase();
    if (!term) return;
    var shown = 0;
    shopCards().forEach(function (c) {
      var hit = c.textContent.toLowerCase().indexOf(term) !== -1;
      var li = c.closest('li') || c;
      li.style.display = hit ? '' : 'none';
      if (hit) shown++;
    });
    applyBanner('<span>' + shown + ' result' + (shown === 1 ? '' : 's') + ' for &ldquo;' + term + '&rdquo;</span>');
  }
  function applyOrder(mode) {
    var wrap = cardListWrap();
    if (!wrap) return;
    products().then(function (list) {
      var cards = shopCards().map(function (c) { return c.closest('li') || c; });
      var keyed = cards.map(function (li) {
        var id = cardId(li);
        var p = byId(list, id);
        return { li: li, price: p.price, name: p.name };
      });
      if (mode === 'price') keyed.sort(function (a, b) { return a.price - b.price; });
      else if (mode === 'price-desc') keyed.sort(function (a, b) { return b.price - a.price; });
      else if (mode === 'title') keyed.sort(function (a, b) { return a.name.localeCompare(b.name); });
      keyed.forEach(function (k) { wrap.appendChild(k.li); });
    });
  }

  /* ---------------- cart page ---------------- */
  function isCartPage() { return /\/cart\/?$/.test(location.pathname); }
  function isCheckoutPage() { return /\/checkout\/?$/.test(location.pathname); }
  function cartContainer() { return document.querySelector('.fh-page__content .woocommerce'); }

  function renderCartPage() {
    if (!isCartPage()) return;
    var box = cartContainer();
    if (!box) return;
    var entries = cartEntries();
    products().then(function (list) {
      if (!entries.length) {
        box.innerHTML = '<div class="sr-cart-empty"><p>Your cart is currently empty.</p>' +
          '<a class="fh-btn fh-btn--gold" href="' + UP + 'shop/">Return to shop</a></div>';
        return;
      }
      var subtotal = 0;
      var html = '<h2 style="margin:6px 0 4px;font-size:22px;text-transform:uppercase;letter-spacing:.02em">Your cart</h2>';
      entries.forEach(function (en) {
        var p = byId(list, en.id);
        var line = p.price * en.qty;
        subtotal += line;
        html += '<div class="sr-line">' +
          '<a href="' + UP + p.url + '"><img src="' + UP + p.img.replace(/^\//, '') + '" alt=""></a>' +
          '<div><a class="sr-line__name" href="' + UP + p.url + '">' + p.name + '</a>' +
          '<div class="sr-line__price">' + money(p.price) + ' each</div>' +
          '<div class="sr-qty"><button data-sr-dec="' + p.id + '" aria-label="Decrease">&minus;</button>' +
          '<span>' + en.qty + '</span>' +
          '<button data-sr-inc="' + p.id + '" aria-label="Increase">+</button></div></div>' +
          '<div class="sr-line__right"><div class="sr-line__total">' + money(line) + '</div>' +
          '<button class="sr-line__rm" data-sr-remove="' + p.id + '">Remove</button></div>' +
          '</div>';
      });
      html += '<div class="sr-summary"><div class="sr-summary__row">Subtotal <strong>' + money(subtotal) + '</strong></div>' +
        '<div class="sr-summary__row" style="color:#9b8a83;font-size:13px">Shipping &amp; taxes calculated at checkout</div>' +
        '<a class="fh-btn fh-btn--gold" href="' + UP + 'checkout/" style="margin-top:12px">Proceed to checkout</a></div>';
      box.innerHTML = html;
    });
  }

  /* ---------------- checkout page ---------------- */
  function placeOrder(form) {
    var data = new FormData(form);
    var required = ['name', 'phone', 'address', 'city'];
    for (var i = 0; i < required.length; i++) {
      if (!(data.get(required[i]) || '').trim()) {
        toast('Please fill in all required fields.');
        form.querySelector('[name="' + required[i] + '"]').focus();
        return;
      }
    }
    var entries = cartEntries();
    if (!entries.length) { toast('Your cart is empty.'); return; }
    var num = 'SR-' + Date.now().toString().slice(-8);
    localStorage.setItem('sr_last_order', num);
    localStorage.setItem(STORE_KEY, '{}');
    updateBadge();
    var root = document.getElementById('sr-checkout-root');
    products().then(function (list) {
      var total = entries.reduce(function (s, en) { var p = byId(list, en.id); return s + p.price * en.qty; }, 0);
      root.innerHTML = '<div class="sr-done"><div class="sr-done__icon">&#10003;</div>' +
        '<h2>Order placed</h2>' +
        '<p>Order number <span class="sr-done__num">' + num + '</span></p>' +
        '<p>Total ' + money(total) + ' &middot; ' + data.get('method') + '</p>' +
        '<p style="font-size:13.5px;color:#9b8a83;margin-top:14px">This is a static demo build &mdash; no payment was processed. ' +
        'On the live store, ' + (data.get('method') || 'your payment') + ' would be confirmed here.</p>' +
        '<a class="fh-btn fh-btn--gold" style="margin-top:22px;display:inline-block" href="' + UP + 'shop/">Continue shopping</a></div>';
      window.scrollTo(0, 0);
    });
  }

  function renderCheckoutPage() {
    if (!isCheckoutPage()) return;
    var root = document.getElementById('sr-checkout-root');
    if (!root) return;
    var entries = cartEntries();
    products().then(function (list) {
      if (!entries.length) {
        root.innerHTML = '<div class="sr-cart-empty"><p>Your cart is currently empty.</p>' +
          '<a class="fh-btn fh-btn--gold" href="' + UP + 'shop/">Return to shop</a></div>';
        return;
      }
      var subtotal = 0, rows = '';
      entries.forEach(function (en) {
        var p = byId(list, en.id);
        var line = p.price * en.qty;
        subtotal += line;
        rows += '<div class="sr-ordersum__row"><span>' + p.name + ' <small>&times;' + en.qty + '</small></span><span>' + money(line) + '</span></div>';
      });
      var shipping = subtotal >= 100 ? 0 : 4.99;
      var total = subtotal + shipping;
      root.innerHTML = '<form id="sr-checkout-form"><div class="sr-checkout__grid">' +
        '<div class="sr-panel"><h2>Billing details</h2>' +
        '<div class="sr-field"><label>Full name *</label><input name="name" autocomplete="name" required></div>' +
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">' +
        '<div class="sr-field"><label>Phone *</label><input name="phone" type="tel" autocomplete="tel" required></div>' +
        '<div class="sr-field"><label>Email</label><input name="email" type="email" autocomplete="email"></div></div>' +
        '<div class="sr-field"><label>Address *</label><input name="address" autocomplete="street-address" required></div>' +
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">' +
        '<div class="sr-field"><label>City *</label><input name="city" autocomplete="address-level2" required></div>' +
        '<div class="sr-field"><label>District</label><input name="district"></div></div>' +
        '<h2 style="margin-top:22px">Payment method</h2>' +
        '<div class="sr-pay">' +
        '<label><input type="radio" name="method" value="bKash" checked> bKash (merchant 01XXXXXXXXX)</label>' +
        '<label><input type="radio" name="method" value="Nagad"> Nagad</label>' +
        '<label><input type="radio" name="method" value="Card on delivery"> Card / Cash on delivery</label>' +
        '</div>' +
        '<button type="submit" class="fh-btn fh-btn--gold" style="width:100%;justify-content:center">Place order</button>' +
        '<p style="font-size:12.5px;color:#9b8a83;margin:12px 0 0">Demo checkout on a static GitHub Pages build &mdash; no real charge is made.</p>' +
        '</div>' +
        '<div class="sr-panel"><h2>Your order</h2>' + rows +
        '<div class="sr-ordersum__row" style="border-top:1px solid rgba(255,255,255,.1);margin-top:8px;padding-top:14px"><span>Subtotal</span><span>' + money(subtotal) + '</span></div>' +
        '<div class="sr-ordersum__row"><span>Shipping</span><span>' + (shipping ? money(shipping) : 'Free') + '</span></div>' +
        '<div class="sr-ordersum__row" style="font-weight:700;color:#e9c65a"><span>Total</span><span>' + money(total) + '</span></div>' +
        '</div></div></form>';
    });
  }

  /* ---------------- boot ---------------- */
  function boot() {
    injectStyles();
    updateBadge();
    if (isCartPage()) renderCartPage();
    if (isCheckoutPage()) renderCheckoutPage();
    if (/\/shop\/?$/.test(location.pathname) || /\/product-category\//.test(location.pathname)) applyQueryFilter();
    // observe cart badge changes from other tabs
    window.addEventListener('storage', updateBadge);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
