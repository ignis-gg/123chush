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

	// Sticky mobile CTA bar — appears after scrolling past the hero, hides
	// again near the very bottom (footer is right there anyway).
	var stickyCta = document.querySelector('.sticky-cta');
	if (stickyCta) {
		var toggleSticky = function () {
			var pastHero = window.scrollY > window.innerHeight * 0.6;
			var nearBottom = window.scrollY + window.innerHeight > document.body.scrollHeight - 200;
			stickyCta.classList.toggle('is-visible', pastHero && !nearBottom);
			document.body.classList.toggle('has-sticky-cta', pastHero && !nearBottom);
		};
		window.addEventListener('scroll', toggleSticky, { passive: true });
		toggleSticky();
	}

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
})();
