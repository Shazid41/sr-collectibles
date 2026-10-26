/**
 * SR Secure Pay — branded card popup (demo mode).
 *
 * Demo mode only: card data is validated locally (Luhn / expiry / CVV shape),
 * never transmitted and never stored. When the buyer confirms, the hidden
 * sr_card_demo_confirmed flag is set and the checkout form posts normally.
 */
(function () {
	'use strict';

	if ( typeof window.srPay === 'undefined' ) {
		return;
	}

	var modal, panel, form, lastFocus, confirmed = false, submitting = false;
	var numInput, expInput, cvvInput, nameInput, confirmBtn;

	function qs(sel, root) { return (root || document).querySelector(sel); }

	function openModal() {
		if (!modal) { return; }
		lastFocus = document.activeElement;
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('fh-paymodal-open');
		resetSteps();
		window.setTimeout(function () {
			if (numInput) { numInput.focus(); }
		}, 240);
	}

	function closeModal() {
		if (!modal) { return; }
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('fh-paymodal-open');
		if (lastFocus && typeof lastFocus.focus === 'function') { lastFocus.focus(); }
	}

	function resetSteps() {
		var body = qs('[data-srpay-step="form"]', modal);
		var done = qs('[data-srpay-step="done"]', modal);
		if (body) { body.hidden = false; }
		if (done) { done.hidden = true; }
		modal.classList.remove('is-busy', 'is-error');
	}

	function showDone() {
		var body = qs('[data-srpay-step="form"]', modal);
		var done = qs('[data-srpay-step="done"]', modal);
		if (body) { body.hidden = true; }
		if (done) {
			done.hidden = false;
			// Restart the check-draw animation.
			var circle = qs('.fh-paycheck__circle', done);
			var tick = qs('.fh-paycheck__tick', done);
			[circle, tick].forEach(function (el) {
				if (!el) { return; }
				var clone = el.cloneNode(true);
				el.parentNode.replaceChild(clone, el);
			});
		}
	}

	function setError(field, key, message) {
		var wrap = field.closest('.fh-payfield');
		if (!wrap) { return; }
		var slot = qs('[data-srpay-error="' + key + '"]', wrap);
		if (message) {
			wrap.classList.add('has-error');
			if (slot) { slot.textContent = message; }
		} else {
			wrap.classList.remove('has-error');
			if (slot) { slot.textContent = ''; }
		}
	}

	function clearAllErrors() {
		['num', 'exp', 'cvv', 'name'].forEach(function (key) {
			var slot = qs('[data-srpay-error="' + key + '"]', modal);
			if (slot && slot.closest('.fh-payfield')) {
				slot.closest('.fh-payfield').classList.remove('has-error');
				slot.textContent = '';
			}
		});
	}

	function digitsOnly(value) { return (value || '').replace(/\D+/g, ''); }

	/* ---- formatting ---- */

	function formatCardNumber() {
		var d = digitsOnly(numInput.value).slice(0, 16);
		var groups = d.replace(/(\d{4})(?=\d)/g, '$1 ');
		numInput.value = groups;
		updateBrand(d);
	}

	function detectBrand(d) {
		if (/^4/.test(d)) { return 'visa'; }
		if (/^(5[1-5]|2[2-7])/.test(d)) { return 'mc'; }
		if (/^3[47]/.test(d)) { return 'amex'; }
		return '';
	}

	function updateBrand(d) {
		var brandEl = qs('[data-srpay-brand]', modal);
		if (!brandEl) { return; }
		var brand = detectBrand(d);
		brandEl.classList.remove('is-visa', 'is-mc', 'is-amex');
		brandEl.textContent = '';
		if ('visa' === brand) { brandEl.classList.add('is-visa'); brandEl.textContent = 'VISA'; }
		if ('amex' === brand) { brandEl.classList.add('is-amex'); brandEl.textContent = 'AMEX'; }
		if ('mc' === brand) { brandEl.classList.add('is-mc'); }
	}

	function formatExpiry() {
		var d = digitsOnly(expInput.value).slice(0, 4);
		if (d.length >= 3) {
			expInput.value = d.slice(0, 2) + ' / ' + d.slice(2);
		} else if (d.length === 2 && expInput.value.length > 2) {
			expInput.value = d + ' / ';
		} else {
			expInput.value = d;
		}
	}

	function formatCvv() {
		var brand = detectBrand(digitsOnly(numInput.value));
		var max = 'amex' === brand ? 4 : 3;
		cvvInput.value = digitsOnly(cvvInput.value).slice(0, max);
	}

	/* ---- validation ---- */

	function luhnValid(d) {
		var sum = 0, alt = false, i = d.length - 1, n;
		for (; i >= 0; i--) {
			n = parseInt(d.charAt(i), 10);
			if (alt) {
				n *= 2;
				if (n > 9) { n -= 9; }
			}
			sum += n;
			alt = !alt;
		}
		return d.length > 0 && 0 === sum % 10;
	}

	function validate() {
		clearAllErrors();
		var ok = true;
		var brand = detectBrand(digitsOnly(numInput.value));
		var num = digitsOnly(numInput.value);

		if (!brand) {
			setError(numInput, 'num', 'Enter a valid Visa, Mastercard or Amex number.');
			ok = false;
		} else if ('amex' === brand && 15 !== num.length) {
			setError(numInput, 'num', 'Amex numbers have 15 digits.');
			ok = false;
		} else if ('amex' !== brand && 16 !== num.length) {
			setError(numInput, 'num', 'Card numbers have 16 digits.');
			ok = false;
		} else if (!luhnValid(num)) {
			setError(numInput, 'num', 'This card number is not valid — please check it.');
			ok = false;
		}

		var exp = digitsOnly(expInput.value);
		if (4 !== exp.length) {
			setError(expInput, 'exp', 'Use MM / YY.');
			ok = false;
		} else {
			var mm = parseInt(exp.slice(0, 2), 10);
			var yy = 2000 + parseInt(exp.slice(2), 10);
			var now = new Date();
			var expEnd = new Date(yy, mm, 1); // First day after the expiry month.
			if (mm < 1 || mm > 12) {
				setError(expInput, 'exp', 'Month must be 01–12.');
				ok = false;
			} else if (expEnd <= now) {
				setError(expInput, 'exp', 'This card has expired.');
				ok = false;
			}
		}

		var cvv = digitsOnly(cvvInput.value);
		var cvvLen = 'amex' === brand ? 4 : 3;
		if (cvv.length !== cvvLen) {
			setError(cvvInput, 'cvv', 'amex' === brand ? 'CVV is the 4 digits on the front.' : 'CVV is the 3 digits on the back.');
			ok = false;
		}

		var nm = (nameInput.value || '').trim();
		if (nm.length < 2 || !/^[A-Za-z .'\-]+$/.test(nm)) {
			setError(nameInput, 'name', 'Enter the name as printed on the card.');
			ok = false;
		}

		return ok;
	}

	/* ---- amount ---- */

	function fillAmount() {
		var totalEl = qs('.order-total .amount') || qs('.woocommerce-Price-amount');
		var text = totalEl ? totalEl.textContent.replace(/\s+/g, ' ').trim() : '$0.00';
		var amountEl = qs('#fh-paymodal-amount');
		var paySpan = qs('[data-srpay-amount]');
		if (amountEl) { amountEl.textContent = text; }
		if (paySpan) { paySpan.textContent = text; }
	}

	/* ---- submit flow ---- */

	function cardSelected() {
		var radio = qs('input#payment_method_sr_card');
		return !!(radio && radio.checked);
	}

	function onFormSubmit(e) {
		if (e.target !== form) { return; }
		if (submitting || confirmed || !cardSelected()) { return; }
		// Capture phase on document: must run before WooCommerce's jQuery submit
		// handler on the form, otherwise its AJAX submit races this popup.
		e.preventDefault();
		e.stopImmediatePropagation();
		fillAmount();
		openModal();
	}

	function onConfirm() {
		if (!validate()) {
			modal.classList.remove('is-error');
			void panel.offsetWidth; // Restart shake animation.
			modal.classList.add('is-error');
			return;
		}
		modal.classList.remove('is-error');
		modal.classList.add('is-busy');
		window.setTimeout(function () {
			showDone();
			window.setTimeout(function () {
				if (window.srPay.preview) {
					closeModal();
					return;
				}
				confirmed = true;
				var flag = qs('input[name="sr_card_demo_confirmed"]', form);
				if (flag) { flag.value = '1'; }
				submitting = true;
				resetSteps();
				// A programmatic submit() drops submit-button values — WC's
				// checkout_action() requires this field to process the order.
				if (!qs('input[name="woocommerce_checkout_place_order"]', form)) {
					var trigger = document.createElement('input');
					trigger.type = 'hidden';
					trigger.name = 'woocommerce_checkout_place_order';
					trigger.value = '1';
					form.appendChild(trigger);
				}
				// Native submit: skips WC's AJAX handler, posts the classic form.
				if (typeof form.submit === 'function') { form.submit(); }
			}, 1500);
		}, 350);
	}

	/* ---- focus trap ---- */

	function trapTab(e) {
		if ('Tab' !== e.key || !modal.classList.contains('is-open')) { return; }
		var focusables = panel.querySelectorAll('button, input, [href]');
		var list = [];
		focusables.forEach(function (el) {
			if (!el.disabled && el.offsetParent !== null) { list.push(el); }
		});
		if (!list.length) { return; }
		var first = list[0], last = list[list.length - 1];
		if (e.shiftKey && document.activeElement === first) {
			e.preventDefault(); last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault(); first.focus();
		}
	}

	function init() {
		modal = qs('#fh-paymodal');
		if (!modal) { return; }
		panel = qs('.fh-paymodal__panel', modal);
		form = qs('form.checkout');
		numInput = qs('#fh-pay-num', modal);
		expInput = qs('#fh-pay-exp', modal);
		cvvInput = qs('#fh-pay-cvv', modal);
		nameInput = qs('#fh-pay-name', modal);
		confirmBtn = qs('#fh-pay-confirm', modal);

		numInput.addEventListener('input', formatCardNumber);
		expInput.addEventListener('input', formatExpiry);
		cvvInput.addEventListener('input', formatCvv);

		confirmBtn.addEventListener('click', onConfirm);
		[ numInput, expInput, cvvInput, nameInput ].forEach(function (input) {
			input.addEventListener('keydown', function (e) {
				if ('Enter' === e.key) { e.preventDefault(); onConfirm(); }
			});
		});

		modal.addEventListener('click', function (e) {
			if (e.target.closest('[data-srpay-close]')) { closeModal(); }
		});
		document.addEventListener('keydown', function (e) {
			if ('Escape' === e.key && modal.classList.contains('is-open') && !modal.classList.contains('is-busy')) {
				closeModal();
			}
			trapTab(e);
		});

		// Capture on document: runs before any listener bound on the form itself.
		document.addEventListener('submit', onFormSubmit, true);

		if (window.srPay.preview) {
			fillAmount();
			openModal();
		}
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
