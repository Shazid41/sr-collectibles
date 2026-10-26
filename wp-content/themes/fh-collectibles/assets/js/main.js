/**
 * SR Collectibles — front-end interactions.
 */
( function () {
	'use strict';

	var prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ------------------------------------------------------------------
	 * Header: shadow on scroll
	 * ------------------------------------------------------------------ */
	var header = document.getElementById( 'fh-header' );
	function onScroll() {
		if ( ! header ) { return; }
		header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
	}
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	onScroll();

	/* ------------------------------------------------------------------
	 * Mobile menu
	 * ------------------------------------------------------------------ */
	var toggle = document.getElementById( 'fh-menu-toggle' );
	var panel = document.getElementById( 'fh-mobile-panel' );
	if ( toggle && panel ) {
		toggle.addEventListener( 'click', function () {
			var open = panel.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			toggle.setAttribute( 'aria-label', open ? 'Close menu' : 'Open menu' );
		} );
		window.addEventListener( 'resize', function () {
			if ( window.innerWidth >= 768 && panel.classList.contains( 'is-open' ) ) {
				panel.classList.remove( 'is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}

	/* ------------------------------------------------------------------
	 * Hero carousel — reliable autoplay, swipe, keyboard
	 * ------------------------------------------------------------------ */
	var hero = document.querySelector( '[data-hero]' );
	if ( hero ) {
		var track = hero.querySelector( '[data-hero-track]' );
		var slides = Array.prototype.slice.call( hero.querySelectorAll( '[data-hero-slide]' ) );
		var dots = Array.prototype.slice.call( hero.querySelectorAll( '[data-hero-dot]' ) );
		var current = 0;
		var timer = null;
		var paused = false;
		var inView = true;
		var INTERVAL = 5500;

		function goTo( index ) {
			if ( ! track || ! slides.length ) { return; }
			current = ( index + slides.length ) % slides.length;
			track.style.transform = 'translateX(-' + current * 100 + '%)';
			slides.forEach( function ( slide, i ) {
				var active = i === current;
				slide.classList.toggle( 'is-inactive', ! active );
				slide.setAttribute( 'aria-hidden', active ? 'false' : 'true' );
				if ( active ) {
					slide.removeAttribute( 'inert' );
				} else {
					slide.setAttribute( 'inert', '' );
				}
			} );
			dots.forEach( function ( dot, i ) {
				dot.setAttribute( 'aria-selected', i === current ? 'true' : 'false' );
			} );
		}

		function play() {
			if ( prefersReducedMotion || slides.length < 2 || timer ) { return; }
			timer = window.setInterval( function () {
				if ( ! paused && inView && ! document.hidden ) {
					goTo( current + 1 );
				}
			}, INTERVAL );
		}
		function stop() {
			if ( timer ) { window.clearInterval( timer ); timer = null; }
		}

		dots.forEach( function ( dot ) {
			dot.addEventListener( 'click', function () {
				goTo( parseInt( dot.getAttribute( 'data-hero-dot' ), 10 ) || 0 );
			} );
		} );

		// Pause on hover / focus, resume after.
		hero.addEventListener( 'mouseenter', function () { paused = true; } );
		hero.addEventListener( 'mouseleave', function () { paused = false; } );
		hero.addEventListener( 'focusin', function () { paused = true; } );
		hero.addEventListener( 'focusout', function () { paused = false; } );

		// Pause when the hero scrolls out of view.
		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					inView = entry.isIntersecting;
				} );
			}, { threshold: 0.2 } ).observe( hero );
		}

		// Keyboard arrows on the carousel region.
		hero.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'ArrowRight' ) { goTo( current + 1 ); }
			if ( event.key === 'ArrowLeft' ) { goTo( current - 1 ); }
		} );

		// Touch swipe.
		var touchX = null;
		hero.addEventListener( 'pointerdown', function ( event ) {
			touchX = event.clientX;
		}, { passive: true } );
		hero.addEventListener( 'pointerup', function ( event ) {
			if ( null === touchX ) { return; }
			var delta = event.clientX - touchX;
			touchX = null;
			if ( Math.abs( delta ) > 45 ) {
				goTo( current + ( delta < 0 ? 1 : -1 ) );
			}
		}, { passive: true } );

		goTo( 0 );
		play();

		// 3D tilt: the hero scene leans subtly toward the cursor.
		var tiltRaf = null, tiltPX = 0, tiltPY = 0;
		hero.addEventListener( 'pointermove', function ( event ) {
			if ( prefersReducedMotion || event.pointerType === 'touch' ) { return; }
			var rect = hero.getBoundingClientRect();
			tiltPX = ( ( event.clientX - rect.left ) / rect.width - 0.5 ) * 2;
			tiltPY = ( ( event.clientY - rect.top ) / rect.height - 0.5 ) * 2;
			if ( tiltRaf ) { return; }
			tiltRaf = requestAnimationFrame( function () {
				tiltRaf = null;
				hero.style.setProperty( '--fh-tilt-y', ( tiltPX * 3.4 ).toFixed( 2 ) + 'deg' );
				hero.style.setProperty( '--fh-tilt-x', ( tiltPY * -2.4 ).toFixed( 2 ) + 'deg' );
			} );
		} );
		hero.addEventListener( 'pointerleave', function () {
			hero.style.setProperty( '--fh-tilt-y', '0deg' );
			hero.style.setProperty( '--fh-tilt-x', '0deg' );
		} );
	}

	/* ------------------------------------------------------------------
	 * Scroll spy for the primary nav (front page only)
	 * ------------------------------------------------------------------ */
	var spyNav = document.querySelector( '[data-fh-scrollspy]' );
	if ( spyNav && document.body.classList.contains( 'home' ) ) {
		var navLinks = Array.prototype.slice.call( spyNav.querySelectorAll( 'a[href*="#"]' ) );
		var sections = navLinks
			.map( function ( link ) {
				var id = link.getAttribute( 'href' ).split( '#' )[ 1 ];
				return id ? document.getElementById( id ) : null;
			} )
			.filter( Boolean );

		if ( 'IntersectionObserver' in window && sections.length ) {
			var spy = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) { return; }
					var id = entry.target.id;
					navLinks.forEach( function ( link ) {
						var linkId = link.getAttribute( 'href' ).split( '#' )[ 1 ];
						link.classList.toggle( 'is-active', linkId === id );
					} );
				} );
			}, { rootMargin: '-40% 0px -55% 0px' } );

			sections.forEach( function ( section ) { spy.observe( section ); } );
		}
	}

	/* ------------------------------------------------------------------
	 * Reveal on scroll
	 * ------------------------------------------------------------------ */
	var revealEls = document.querySelectorAll( '[data-reveal]' );
	if ( revealEls.length ) {
		if ( 'IntersectionObserver' in window && ! prefersReducedMotion ) {
			var revealObserver = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
						revealObserver.unobserve( entry.target );
					}
				} );
			}, { threshold: 0.12 } );
			revealEls.forEach( function ( el ) { revealObserver.observe( el ); } );
		} else {
			revealEls.forEach( function ( el ) { el.classList.add( 'is-visible' ); } );
		}
	}

	/* ------------------------------------------------------------------
	 * Wishlist (localStorage)
	 * ------------------------------------------------------------------ */
	var WISHLIST_KEY = 'fh_wishlist';
	function getWishlist() {
		try {
			return JSON.parse( window.localStorage.getItem( WISHLIST_KEY ) || '[]' );
		} catch ( e ) {
			return [];
		}
	}
	function saveWishlist( ids ) {
		try {
			window.localStorage.setItem( WISHLIST_KEY, JSON.stringify( ids ) );
		} catch ( e ) { /* storage unavailable */ }
	}
	var wishlist = getWishlist();
	document.querySelectorAll( '[data-wishlist-id]' ).forEach( function ( button ) {
		var id = button.getAttribute( 'data-wishlist-id' );
		if ( wishlist.indexOf( id ) !== -1 ) {
			button.setAttribute( 'aria-pressed', 'true' );
		}
		button.addEventListener( 'click', function () {
			var ids = getWishlist();
			var pressed = button.getAttribute( 'aria-pressed' ) === 'true';
			if ( pressed ) {
				ids = ids.filter( function ( item ) { return item !== id; } );
			} else if ( ids.indexOf( id ) === -1 ) {
				ids.push( id );
			}
			saveWishlist( ids );
			button.setAttribute( 'aria-pressed', pressed ? 'false' : 'true' );
		} );
	} );

	/* ------------------------------------------------------------------
	 * Add-to-cart feedback (works with WooCommerce AJAX)
	 * ------------------------------------------------------------------ */
	var toast = document.getElementById( 'fh-toast' );
	var toastTimer = null;
	function showToast( message ) {
		if ( ! toast ) { return; }
		var text = document.getElementById( 'fh-toast-text' );
		if ( text && message ) { text.textContent = message; }
		toast.classList.add( 'is-visible' );
		if ( toastTimer ) { window.clearTimeout( toastTimer ); }
		toastTimer = window.setTimeout( function () {
			toast.classList.remove( 'is-visible' );
		}, 3500 );
	}

	if ( window.jQuery && toast ) {
		window.jQuery( document.body ).on( 'added_to_cart', function ( event, fragments, cartHash, $button ) {
			var cartLink = document.getElementById( 'fh-cart-link' );
			if ( cartLink ) {
				cartLink.classList.add( 'is-bumped' );
				window.setTimeout( function () { cartLink.classList.remove( 'is-bumped' ); }, 350 );
			}
			if ( $button && $button.length ) {
				var original = $button.text();
				$button.addClass( 'is-added' ).text( 'Added ✓' );
				window.setTimeout( function () {
					$button.removeClass( 'is-added' ).text( original );
				}, 1800 );
			}
			showToast( 'Added to cart' );
		} );
	}

	/* ------------------------------------------------------------------
	 * Cursor statement: letters rise and glow gold near the cursor.
	 * Falls back to a slow idle wave on touch / when the pointer rests.
	 * ------------------------------------------------------------------ */
	( function () {
		var lines = document.querySelectorAll( '[data-cursor-text]' );
		if ( ! lines.length || prefersReducedMotion ) { return; }

		var section = lines[ 0 ].closest( 'section' );
		if ( ! section ) { return; }

		var letters = [];
		Array.prototype.forEach.call( lines, function ( line ) {
			var text = line.textContent;
			line.textContent = '';
			line.setAttribute( 'aria-label', text );
			Array.prototype.forEach.call( text, function ( ch ) {
				var span = document.createElement( 'span' );
				span.className = 'fh-cursortext__ch';
				span.setAttribute( 'aria-hidden', 'true' );
				span.textContent = ch === ' ' ? '\u00A0' : ch;
				line.appendChild( span );
				letters.push( { el: span, cx: 0, cy: 0, t: 0, index: letters.length } );
			} );
		} );

		function measure() {
			Array.prototype.forEach.call( letters, function ( l ) {
				l.cx = l.el.offsetLeft + l.el.offsetWidth / 2;
				l.cy = l.el.offsetTop + l.el.offsetHeight / 2;
			} );
		}
		measure();
		window.addEventListener( 'resize', measure );

		var pointer = { x: -9999, y: -9999 };
		var active = false;
		var visible = true;
		var container = lines[ 0 ].offsetParent || section;

		if ( 'IntersectionObserver' in window ) {
			visible = false;
			new IntersectionObserver( function ( entries ) {
				visible = entries[ 0 ].isIntersecting;
			}, { threshold: 0.05 } ).observe( section );
		}

		section.addEventListener( 'pointermove', function ( e ) {
			var rect = container.getBoundingClientRect();
			pointer.x = e.clientX - rect.left;
			pointer.y = e.clientY - rect.top;
			active = true;
		} );
		section.addEventListener( 'pointerleave', function () {
			active = false;
		} );

		var RADIUS = 150;
		var RISE = 26;
		var GLOW = 'rgba(233, 198, 90, ';

		function frame( now ) {
			if ( visible ) {
				var sx = 0, sy = 0;
				if ( active ) {
					sx = pointer.x;
					sy = pointer.y;
				}
				for ( var i = 0; i < letters.length; i++ ) {
					var l = letters[ i ];
					var target = 0;
					if ( active ) {
						var dx = l.cx - sx;
						var dy = l.cy - sy;
						var d = Math.sqrt( dx * dx + dy * dy );
						target = Math.max( 0, 1 - d / RADIUS );
					} else {
						// Idle wave: keeps the section alive on touch devices.
						target = ( Math.sin( now / 900 + l.index * 0.33 ) + 1 ) / 2 * 0.35;
					}
					l.t += ( target - l.t ) * 0.14;
					var t = l.t;
					var style = l.el.style;
					if ( t < 0.012 ) {
						if ( style.transform ) {
							style.transform = '';
							style.color = '';
							style.textShadow = '';
						}
						continue;
					}
					style.transform = 'translateY(' + ( -RISE * t ).toFixed( 1 ) + 'px) scale(' + ( 1 + 0.22 * t ).toFixed( 3 ) + ')';
					style.color = GLOW + ( 0.25 + 0.75 * t ).toFixed( 2 ) + ')';
					style.textShadow = t > 0.35 ? '0 0 ' + Math.round( 26 * t ) + 'px rgba(233, 198, 90, ' + ( 0.35 * t ).toFixed( 2 ) + ')' : '';
				}
			}
			window.requestAnimationFrame( frame );
		}
		window.requestAnimationFrame( frame );
	}() );
}() );
