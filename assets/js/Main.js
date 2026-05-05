/**
 * A-List Club — front-end script
 * Vanilla JS, no jQuery dependency.
 */
(function () {
	'use strict';

	const $  = (sel, ctx = document) => ctx.querySelector(sel);
	const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

	const escapeHTML = (str = '') =>
		String(str).replace(/[&<>"']/g, (c) => ({
			'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
		}[c]));

	/**
	 * Mobile nav toggle.
	 */
	function initMobileNav() {
		const toggle = $('.nav__toggle');
		const menu   = $('#primary-menu');
		if (!toggle || !menu) return;

		toggle.addEventListener('click', () => {
			const open = toggle.getAttribute('aria-expanded') === 'true';
			toggle.setAttribute('aria-expanded', String(!open));
			menu.classList.toggle('is-open', !open);
		});

		// Close on resize to desktop.
		const mq = window.matchMedia('(min-width: 1000px)');
		const onChange = (e) => {
			if (e.matches) {
				toggle.setAttribute('aria-expanded', 'false');
				menu.classList.remove('is-open');
			}
		};
		mq.addEventListener ? mq.addEventListener('change', onChange) : mq.addListener(onChange);
	}

	/**
	 * Touch-friendly account submenu (replaces hover-only).
	 */
	function initSubmenuTap() {
		$$('.has-submenu > a').forEach((trigger) => {
			trigger.addEventListener('click', (e) => {
				const li = trigger.parentElement;
				const sub = li.querySelector('.sub-menu');
				if (!sub) return;
				if (window.matchMedia('(hover: none)').matches) {
					e.preventDefault();
					const open = trigger.getAttribute('aria-expanded') === 'true';
					trigger.setAttribute('aria-expanded', String(!open));
					li.classList.toggle('submenu-open', !open);
				}
			});
		});
	}

	/**
	 * Search overlay with focus management.
	 */
	class Search {
		constructor() {
			this.overlay     = $('section#search');
			this.openBtn     = $('#open-search-overlay');
			this.closeBtn    = $('#close-search-overlay');
			this.input       = $('#search-input');
			this.results     = $('#search-results');
			if (!this.overlay || !this.input) return;

			this.prev    = '';
			this.timer   = null;
			this.lastFocus = null;
			this.bind();
		}

		bind() {
			if (this.openBtn) {
				this.openBtn.addEventListener('click', (e) => { e.preventDefault(); this.open(); });
			}
			if (this.closeBtn) {
				this.closeBtn.addEventListener('click', () => this.close());
			}
			this.input.addEventListener('input', () => this.handleInput());
			document.addEventListener('keyup', (e) => {
				if (e.key === 'Escape' && this.isOpen()) this.close();
			});
			this.overlay.addEventListener('click', (e) => {
				if (e.target === this.overlay) this.close();
			});
		}

		isOpen() {
			return this.overlay.classList.contains('is-open');
		}

		open() {
			this.lastFocus = document.activeElement;
			this.overlay.classList.add('is-open');
			this.overlay.setAttribute('aria-hidden', 'false');
			document.body.classList.add('lock');
			setTimeout(() => this.input.focus(), 50);
		}

		close() {
			this.overlay.classList.remove('is-open');
			this.overlay.setAttribute('aria-hidden', 'true');
			this.input.value = '';
			this.results.innerHTML = '';
			document.body.classList.remove('lock');
			if (this.lastFocus && typeof this.lastFocus.focus === 'function') {
				this.lastFocus.focus();
			}
		}

		handleInput() {
			const val = this.input.value;
			if (val === this.prev) return;
			this.prev = val;
			clearTimeout(this.timer);
			this.timer = setTimeout(() => this.fetchResults(val), 400);
		}

		async fetchResults(query) {
			if (!query || !query.trim()) {
				this.results.innerHTML = `<div class="container"><p>${escapeHTML('Nothing to show')}</p></div>`;
				return;
			}
			this.results.innerHTML = `<div class="container"><div class="row">${escapeHTML('Searching…')}</div></div>`;
			try {
				const url = (window.localData && window.localData.restUrl)
					? `${window.localData.restUrl}?query=${encodeURIComponent(query)}`
					: `${window.localData.siteUrl}/wp-json/alistclub/v1/search?query=${encodeURIComponent(query)}`;
				const res = await fetch(url, {
					headers: { 'X-WP-Nonce': (window.localData && window.localData.restNonce) || '' }
				});
				if (!res.ok) throw new Error('Search request failed');
				const data = await res.json();
				this.render(data);
			} catch (err) {
				this.results.innerHTML = `<div class="container"><p>${escapeHTML('Something went wrong. Please try again.')}</p></div>`;
			}
		}

		render(results) {
			const products = results.products || [];
			const posts    = results.posts    || [];
			const faqs     = results.faqs     || [];
			this.results.innerHTML = `
				<div class="container">
					<div class="row">
						<div class="search-result-wrapper full">
							<h3 class="title">Products</h3>
							${products.length ? products.map((p) => this.product(p)).join('') : '<p>No products found</p>'}
						</div>
						<div class="search-result-wrapper">
							<h3 class="title">Articles</h3>
							${posts.length ? posts.map((p) => this.post(p)).join('') : '<p>No articles found</p>'}
						</div>
						<div class="search-result-wrapper">
							<h3 class="title">FAQs</h3>
							${faqs.length ? faqs.map((f) => this.faq(f)).join('') : '<p>No FAQs found</p>'}
						</div>
					</div>
				</div>`;
		}

		product(p) {
			const price = p.sale_price ? p.sale_price : p.regular_price;
			const href  = `${window.localData.siteUrl}/product/${encodeURIComponent(p.slug || '')}`;
			return `
				<div class="search-product">
					<a href="${escapeHTML(href)}">
						${p.image ? `<img src="${escapeHTML(p.image)}" alt="${escapeHTML(p.name || '')}" loading="lazy" decoding="async">` : ''}
						<div class="product-details">
							<h4 class="title">${escapeHTML(p.name || '')}</h4>
							<span class="price">$${escapeHTML(price || '')}</span>
						</div>
					</a>
				</div>`;
		}

		post(p) {
			return `<div class="search-post"><h4>${escapeHTML(p.title || '')}</h4><p>${escapeHTML(p.content || '')}</p></div>`;
		}

		faq(f) {
			return `<div class="search-faq"><h4>${escapeHTML(f.title || '')}</h4><p>${escapeHTML(f.content || '')}</p></div>`;
		}
	}

	/**
	 * Optional WooCommerce gift-card extras.
	 * Only runs when the gift-card options table is present.
	 */
	function initWCExtras() {
		const gcTable = $('table.gift_card_options');
		if (!gcTable) return;

		const gcMessage    = $('#gc_message');
		const gcMailMethod = $('#gc_mail_method');
		if (!gcMailMethod) return;

		const rows      = $$('table.gift_card_options tr');
		const emailRow  = rows.find((tr) => tr.querySelector('input#gc_email'));
		const addrRow   = rows.find((tr) => tr.querySelector('textarea#gc_address'));

		const apply = () => {
			const isEmail = gcMailMethod.value === 'E-Mail';
			if (addrRow)  addrRow.style.display  = isEmail ? 'none' : '';
			if (emailRow) emailRow.style.display = isEmail ? '' : 'none';
		};

		gcMailMethod.addEventListener('change', apply);
		apply();
		if (gcMessage) gcMessage.setAttribute('maxlength', '80');
	}

	/**
	 * Banner carousel.
	 */
	class BannerCarousel {
		constructor(root) {
			this.root      = root;
			this.slides    = $$('.banner-carousel__slide', root);
			this.dots      = $$('.banner-carousel__dot', root);
			this.prevBtn   = $('.banner-carousel__prev', root);
			this.nextBtn   = $('.banner-carousel__next', root);
			this.index     = 0;
			this.timer     = null;
			this.autoplay  = root.dataset.autoplay === '1';
			this.interval  = parseInt(root.dataset.interval, 10) || 5000;

			if (this.slides.length <= 1) return;

			this.bind();
			if (this.autoplay && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
				this.start();
			}
		}

		bind() {
			if (this.prevBtn) this.prevBtn.addEventListener('click', () => { this.go(this.index - 1); this.restart(); });
			if (this.nextBtn) this.nextBtn.addEventListener('click', () => { this.go(this.index + 1); this.restart(); });
			this.dots.forEach((dot) => {
				dot.addEventListener('click', () => {
					this.go(parseInt(dot.dataset.slide, 10) || 0);
					this.restart();
				});
			});
			this.root.addEventListener('mouseenter', () => this.stop());
			this.root.addEventListener('mouseleave', () => this.autoplay && this.start());
			this.root.addEventListener('focusin', () => this.stop());
			this.root.addEventListener('focusout', () => this.autoplay && this.start());

			// Touch swipe.
			let startX = 0, dx = 0;
			this.root.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; dx = 0; }, { passive: true });
			this.root.addEventListener('touchmove',  (e) => { dx = e.touches[0].clientX - startX; }, { passive: true });
			this.root.addEventListener('touchend',   () => {
				if (Math.abs(dx) > 40) {
					this.go(this.index + (dx < 0 ? 1 : -1));
					this.restart();
				}
			});
		}

		go(target) {
			const len = this.slides.length;
			const next = ((target % len) + len) % len;
			this.slides[this.index].classList.remove('is-active');
			this.slides[this.index].setAttribute('aria-hidden', 'true');
			if (this.dots[this.index]) {
				this.dots[this.index].classList.remove('is-active');
				this.dots[this.index].setAttribute('aria-selected', 'false');
			}
			this.index = next;
			this.slides[this.index].classList.add('is-active');
			this.slides[this.index].setAttribute('aria-hidden', 'false');
			if (this.dots[this.index]) {
				this.dots[this.index].classList.add('is-active');
				this.dots[this.index].setAttribute('aria-selected', 'true');
			}
		}

		start() { this.stop(); this.timer = setInterval(() => this.go(this.index + 1), this.interval); }
		stop()  { if (this.timer) { clearInterval(this.timer); this.timer = null; } }
		restart() { if (this.autoplay) this.start(); }
	}

	function initBanners() {
		$$('#banner-carousel.banner-carousel').forEach((el) => new BannerCarousel(el));
	}

	/**
	 * Init.
	 */
	function init() {
		initMobileNav();
		initSubmenuTap();
		initBanners();
		new Search();
		initWCExtras();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
