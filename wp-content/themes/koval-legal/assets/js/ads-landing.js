/**
 * Google Ads landings (template-parts/ads-landing.php).
 *
 * Every [data-al-call] element — and the header's "Консультація" link,
 * which points at #contact-form — opens the Binotel GetCall "Передзвоніть
 * мені" window via its public openPassiveForm() (verified in the widget
 * code 2026-09-27; Binotel itself sends the GA4 event
 * binotel_gc_opened_passive_form). The widget script loads async, so a
 * click that comes before it's ready waits a few seconds for it.

 * (A mobile sticky "Передзвоніть мені" bar was removed 2026-09-27 at the
 * user's request — Binotel's own call button is always on screen anyway.)
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
})();
