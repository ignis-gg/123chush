(function () {
	'use strict';

	// FAQ accordion.
	document.querySelectorAll('.faq-q').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var item = btn.parentElement;
			var answer = item.querySelector('.faq-a');
			var isOpen = item.classList.contains('open');

			document.querySelectorAll('.faq-item.open').forEach(function (open) {
				open.classList.remove('open');
				open.querySelector('.faq-a').style.maxHeight = null;
				open.querySelector('.faq-q').setAttribute('aria-expanded', 'false');
			});

			if (!isOpen) {
				item.classList.add('open');
				answer.style.maxHeight = answer.scrollHeight + 'px';
				btn.setAttribute('aria-expanded', 'true');
			}
		});
	});

	// Article "print" button (single.php).
	var printBtn = document.querySelector('.print-article-btn');
	if (printBtn) {
		printBtn.addEventListener('click', function () {
			window.print();
		});
	}

	// Mobile menu toggle.
	var toggle = document.querySelector('.menu-toggle');
	var mobileNav = document.getElementById('mobile-nav');
	if (toggle && mobileNav) {
		toggle.addEventListener('click', function () {
			var isOpen = mobileNav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && mobileNav.classList.contains('is-open')) {
				mobileNav.classList.remove('is-open');
				toggle.setAttribute('aria-expanded', 'false');
				toggle.focus();
			}
		});
	}

	// Services catalog (/poslugy/) — category accordion + tabs + live text
	// search + sticky mini-nav, all combined. A card shows only if it
	// matches BOTH the active tab and the search text.
	var svcTabs = document.getElementById('svcTabs');
	var svcSearch = document.getElementById('svcSearch');
	var svcGroups = document.getElementById('svcGroups');
	var svcEmpty = document.getElementById('svcEmpty');
	var svcMiniNav = document.getElementById('svcMiniNav');
	if (svcGroups) {
		var activeFilter = 'all';

		// Accordion: same expand/collapse principle as the FAQ accordion
		// above (max-height driven by scrollHeight, so it actually
		// animates instead of snapping via max-height:none).
		var svcExpandGroup = function (group, expand) {
			var toggle = group.querySelector('.svc-group-toggle');
			var grid = group.querySelector('.svc-grid');
			if (!toggle || !grid) return;
			var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
			if (expand === isExpanded) return;

			toggle.setAttribute('aria-expanded', String(expand));

			if (expand) {
				grid.hidden = false;
				grid.classList.add('is-open');
				requestAnimationFrame(function () {
					grid.style.maxHeight = grid.scrollHeight + 'px';
				});
			} else {
				grid.style.maxHeight = grid.scrollHeight + 'px';
				requestAnimationFrame(function () {
					grid.style.maxHeight = '0px';
				});
			}
		};

		svcGroups.querySelectorAll('.svc-group').forEach(function (group) {
			var toggle = group.querySelector('.svc-group-toggle');
			var grid = group.querySelector('.svc-grid');
			if (!toggle || !grid) return;

			toggle.addEventListener('click', function () {
				svcExpandGroup(group, toggle.getAttribute('aria-expanded') !== 'true');
			});

			grid.addEventListener('transitionend', function (e) {
				if (e.propertyName !== 'max-height') return;
				if (toggle.getAttribute('aria-expanded') === 'true') {
					grid.style.maxHeight = 'none';
				} else {
					grid.hidden = true;
					grid.classList.remove('is-open');
				}
			});
		});

		var applySvcFilters = function () {
			var query = svcSearch ? svcSearch.value.trim().toLowerCase() : '';
			var anyVisible = false;

			svcGroups.querySelectorAll('.svc-group').forEach(function (group) {
				var groupMatches = activeFilter === 'all' || group.dataset.group === activeFilter;
				var groupHasVisibleCard = false;

				group.querySelectorAll('.svc-card').forEach(function (card) {
					var matchesSearch = !query || card.dataset.search.indexOf(query) !== -1;
					var visible = groupMatches && matchesSearch;
					card.style.display = visible ? '' : 'none';
					if (visible) {
						groupHasVisibleCard = true;
						anyVisible = true;
					}
				});

				group.style.display = groupHasVisibleCard ? '' : 'none';

				// A single active category tab needs no accordion — its
				// group just shows expanded outright.
				if (activeFilter !== 'all' && group.dataset.group === activeFilter) {
					svcExpandGroup(group, true);
				}
			});

			if (svcEmpty) {
				svcEmpty.classList.toggle('is-visible', !anyVisible);
			}

			if (svcMiniNav) {
				svcMiniNav.hidden = activeFilter !== 'all' || window.scrollY < 400;
			}
		};

		if (svcTabs) {
			svcTabs.querySelectorAll('.svc-tab').forEach(function (tab) {
				tab.addEventListener('click', function () {
					svcTabs.querySelectorAll('.svc-tab').forEach(function (t) { t.classList.remove('is-active'); });
					tab.classList.add('is-active');
					activeFilter = tab.dataset.filter;
					applySvcFilters();
				});
			});
		}

		if (svcSearch) {
			svcSearch.addEventListener('input', applySvcFilters);
		}

		// Deep link from a category card elsewhere on the site
		// (/poslugy/#group-dracs etc.) — applySvcFilters() above never runs on
		// load (only on tab click / search input), so every group is already
		// visible by default and a plain browser anchor-jump would work on its
		// own; this just also highlights the matching tab for context, and
		// covers browsers/cases where the native jump lands before layout
		// (fonts, images) finishes shifting the page. The linked group is
		// also auto-expanded, the rest stay collapsed.
		if (location.hash) {
			var svcTarget = document.querySelector(location.hash);
			if (svcTarget && svcTarget.classList.contains('svc-group')) {
				svcExpandGroup(svcTarget, true);
				svcTarget.scrollIntoView();
				if (svcTabs) {
					var svcTargetTab = svcTabs.querySelector('[data-filter="' + svcTarget.dataset.group + '"]');
					if (svcTargetTab) {
						svcTabs.querySelectorAll('.svc-tab').forEach(function (t) { t.classList.remove('is-active'); });
						svcTargetTab.classList.add('is-active');
					}
				}
			}
		}

		// Sticky mini-nav: appears once scrolled past the hero/tabs, only
		// in the "Усі послуги" view, and highlights the category currently
		// in view.
		if (svcMiniNav) {
			window.addEventListener('scroll', function () {
				svcMiniNav.hidden = activeFilter !== 'all' || window.scrollY < 400;
			}, { passive: true });

			svcMiniNav.querySelectorAll('a').forEach(function (link) {
				link.addEventListener('click', function () {
					var group = document.querySelector(link.getAttribute('href'));
					if (group) svcExpandGroup(group, true);
				});
			});

			var svcNavObserver = new IntersectionObserver(function (entries) {
				entries.forEach(function (entry) {
					var link = svcMiniNav.querySelector('a[href="#' + entry.target.id + '"]');
					if (!link || !entry.isIntersecting) return;
					svcMiniNav.querySelectorAll('a').forEach(function (a) { a.classList.remove('is-active'); });
					link.classList.add('is-active');
				});
			}, { rootMargin: '-40% 0px -40% 0px' });

			svcGroups.querySelectorAll('.svc-group').forEach(function (group) {
				svcNavObserver.observe(group);
			});
		}
	}

	// "Дякуємо" popup — shown after a real, server-validated consultation-form
	// submit. koval_legal_handle_consultation_submit() (inc/shortcodes.php)
	// redirects back with ?koval_sent=1 only once the nonce + honeypot checks
	// passed, so this never fires on a client-side-only "submit" (e.g. a
	// failed required-field check never reaches the server at all).
	var thanksPopup = document.getElementById('koval-thanks-popup');
	if (thanksPopup && new URLSearchParams(window.location.search).get('koval_sent') === '1') {
		thanksPopup.hidden = false;
		document.body.style.overflow = 'hidden';

		var closeThanksPopup = function () {
			thanksPopup.hidden = true;
			document.body.style.overflow = '';
		};

		document.getElementById('koval-thanks-close').addEventListener('click', closeThanksPopup);
		document.getElementById('koval-thanks-ok').addEventListener('click', closeThanksPopup);
		thanksPopup.addEventListener('click', function (e) {
			if (e.target === thanksPopup) closeThanksPopup();
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !thanksPopup.hidden) closeThanksPopup();
		});

		// Strip koval_sent (and lt, the one-time GA4/Meta lead token) from
		// the URL so a page refresh doesn't re-open the popup or leave the
		// spent token visible — the lead was already recorded server-side
		// on first load.
		var cleanUrl = new URL(window.location.href);
		cleanUrl.searchParams.delete('koval_sent');
		cleanUrl.searchParams.delete('lt');
		window.history.replaceState({}, '', cleanUrl.pathname + cleanUrl.search + cleanUrl.hash);
	}
})();

// "Back to top" button (footer.php): shown once the page is scrolled,
// smooth-scrolls to the top (instant when the user prefers reduced motion).
(function () {
	var btn = document.getElementById('to-top');
	if (!btn) {
		return;
	}
	var ticking = false;
	var update = function () {
		btn.classList.toggle('is-visible', window.scrollY > 600);
		ticking = false;
	};
	window.addEventListener('scroll', function () {
		if (!ticking) {
			ticking = true;
			window.requestAnimationFrame(update);
		}
	}, { passive: true });
	update();
	btn.addEventListener('click', function (e) {
		e.preventDefault();
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
		btn.blur();
	});
})();

// Every "consultation" CTA on the site (header "Консультація", service hero
// buttons, blog/catalog/price CTAs — all link to #contact-form) opens the
// Binotel "Передзвоніть мені" callback window instead of scrolling to the
// form (user request 2026-09-27; the forms themselves stay on the pages).
// Without JS, or if Binotel never loads, the link still leads to the form.
// Google Ads landings have their own handler (assets/js/ads-landing.js).
(function () {
	if (document.body.classList.contains('ads-landing')) {
		return;
	}
	var WIDGET_ID = '88044'; // BinotelGetCall id of the footer.php GetCall widget
	var getWidget = function () {
		var all = window.BinotelGetCall;
		if (!all) {
			return null;
		}
		var w = all[WIDGET_ID];
		if (!w) {
			for (var key in all) {
				if (Object.prototype.hasOwnProperty.call(all, key)) {
					w = all[key];
					break;
				}
			}
		}
		return w && typeof w.openPassiveForm === 'function' ? w : null;
	};
	var pending = null;
	document.addEventListener('click', function (e) {
		var link = e.target.closest('a[href*="#contact-form"], [data-binotel-call]');
		if (!link) {
			return;
		}
		var w = getWidget();
		if (!w && !window.BinotelGetCall) {
			return; // widget script blocked or not there: fall back to the form
		}
		e.preventDefault();
		// Open after this click finishes bubbling — Binotel closes its window
		// on any document click outside it, including this one.
		e.stopPropagation();
		if (w) {
			setTimeout(function () { w.openPassiveForm(); }, 0);
			return;
		}
		if (pending) {
			return;
		}
		var started = Date.now();
		pending = setInterval(function () {
			var ready = getWidget();
			if (ready || Date.now() - started > 5000) {
				clearInterval(pending);
				pending = null;
				if (ready) {
					ready.openPassiveForm();
				} else {
					var form = document.getElementById('contact-form');
					if (form) {
						form.scrollIntoView({ behavior: 'smooth' });
					}
				}
			}
		}, 200);
	}, true);
})();

// Viber button (footer.php .msg-fab-vb). Its href has the phone-app format
// (number with %2B, an encoded "+"); Viber Desktop only understands a
// literal "+" — with %2B it opens but never switches to the chat — so
// desktops get that format. Where Viber isn't installed the browser
// silently does nothing: if the page is still in front ~1.5s after the
// click (no app took over), a small hint offers the number to copy instead.
(function () {
	var btn = document.querySelector('.msg-fab-vb');
	if (!btn) {
		return;
	}
	var digits = (btn.getAttribute('href').match(/\d{10,}/) || [''])[0];
	var mobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent) ||
		(navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
	if (!mobile) {
		btn.setAttribute('href', 'viber://chat?number=+' + digits);
	}
	var pretty = '+' + digits.replace(/^(\d{3})(\d{2})(\d{3})(\d{2})(\d{2})$/, '$1 $2 $3 $4 $5');
	var hint = null;
	var timer = null;
	var hideTimer = null;
	var hide = function () {
		if (hint) {
			hint.classList.remove('is-visible');
		}
	};
	var show = function () {
		if (!hint) {
			hint = document.createElement('div');
			hint.className = 'viber-hint';
			hint.setAttribute('role', 'status');
			hint.innerHTML = '<button type="button" class="viber-hint-close" aria-label="Закрити">&times;</button>' +
				'<p>Якщо Viber не відкрився — напишіть нам у Viber на номер <strong>' + pretty + '</strong></p>' +
				'<button type="button" class="viber-hint-copy">Скопіювати номер</button>';
			document.body.appendChild(hint);
			hint.querySelector('.viber-hint-close').addEventListener('click', hide);
			hint.querySelector('.viber-hint-copy').addEventListener('click', function (e) {
				var copyBtn = e.currentTarget;
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText('+' + digits).then(function () {
						copyBtn.textContent = 'Скопійовано ✓';
					}, function () {});
				}
			});
		}
		hint.querySelector('.viber-hint-copy').textContent = 'Скопіювати номер';
		// next frame, so the fade-in transition runs on first show too
		window.requestAnimationFrame(function () {
			hint.classList.add('is-visible');
		});
		clearTimeout(hideTimer);
		hideTimer = setTimeout(hide, 12000);
	};
	var cancel = function () {
		clearTimeout(timer);
	};
	btn.addEventListener('click', function () {
		hide();
		cancel();
		timer = setTimeout(function () {
			if (!document.hidden && document.hasFocus()) {
				show();
			}
		}, 1500);
	});
	window.addEventListener('blur', cancel);
	document.addEventListener('visibilitychange', function () {
		if (document.hidden) {
			cancel();
		}
	});
})();
