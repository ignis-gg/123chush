/**
 * Google Ads landings (template-parts/ads-landing.php).
 *
 * Every [data-al-call] element — and the header's "Консультація" link,
 * which points at #contact-form — opens the Binotel GetCall "Передзвоніть
 * мені" window via its public openPassiveForm() (verified in the widget
 * code 2026-09-27; Binotel itself sends the GA4 event
 * binotel_gc_opened_passive_form). The widget script loads async, so a
 * click that comes before it's ready waits a few seconds for it.
 *
 * Mobile: the sticky bottom bar shows once the hero button has scrolled
 * away, and hides again over the final CTA block (no two buttons at once).
 */
(function () {
	'use strict';

	var cfg = window.kovalAdsLanding || {};
	var widgetId = cfg.getcallId || '88044';
	var toast = document.querySelector('[data-al-toast]');
	var toastTimer = null;
	var pending = null;

	function widget() {
		var all = window.BinotelGetCall;
		if (!all) {
			return null;
		}
		var w = all[widgetId];
		if (!w) {
			// Fallback: the first registered GetCall widget on the page.
			for (var key in all) {
				if (Object.prototype.hasOwnProperty.call(all, key)) {
					w = all[key];
					break;
				}
			}
		}
		return w && typeof w.openPassiveForm === 'function' ? w : null;
	}

	function showToast(text) {
		if (!toast) {
			return;
		}
		toast.textContent = text;
		toast.hidden = false;
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () {
			toast.hidden = true;
		}, 6000);
	}

	function openCallback() {
		var w = widget();
		if (w) {
			w.openPassiveForm();
			return;
		}
		if (pending) {
			return;
		}
		var started = Date.now();
		pending = setInterval(function () {
			var ready = widget();
			if (ready) {
				clearInterval(pending);
				pending = null;
				ready.openPassiveForm();
			} else if (Date.now() - started > 6000) {
				clearInterval(pending);
				pending = null;
				showToast('Не вдалося відкрити вікно зворотного дзвінка. Оновіть сторінку й спробуйте ще раз.');
			}
		}, 200);
	}

	document.addEventListener('click', function (e) {
		var trigger = e.target.closest('[data-al-call], a[href$="#contact-form"]');
		if (!trigger) {
			return;
		}
		e.preventDefault();
		// Open after this click has finished bubbling: Binotel closes its
		// window on a document click outside it, so opening synchronously
		// here got the window closed again by this very click.
		e.stopPropagation();
		setTimeout(openCallback, 0);
	}, true);

	// ---- sticky bottom bar (CSS hides it on desktop) ----
	var sticky = document.querySelector('[data-al-sticky]');
	var heroCta = document.querySelector('.al-hero__cta');
	var finalBlock = document.querySelector('.al-final');
	if (!sticky || !heroCta || !('IntersectionObserver' in window)) {
		return;
	}
	sticky.hidden = false;
	document.body.classList.add('al-has-sticky');

	var heroVisible = true;
	var finalVisible = false;
	function update() {
		sticky.classList.toggle('is-visible', !heroVisible && !finalVisible);
	}
	new IntersectionObserver(function (entries) {
		heroVisible = entries[0].isIntersecting || entries[0].boundingClientRect.top > 0;
		update();
	}).observe(heroCta);
	if (finalBlock) {
		new IntersectionObserver(function (entries) {
			finalVisible = entries[0].isIntersecting;
			update();
		}).observe(finalBlock);
	}
})();
