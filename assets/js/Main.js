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
	 * Store grid (homepage + shop archives) — sort, filter and search (server),
	 * with infinite scroll.
	 *
	 * Further pages come from one of two places:
	 * - the products REST endpoint, by page number (homepage, and any archive
	 *   once the visitor changes sort / filter / search);
	 * - WooCommerce's own next archive page, read from its pagination links,
	 *   while an archive is still showing its original query (keeps category,
	 *   ordering and page size exactly as WooCommerce has them).
	 */
	const MORE_LABEL  = 'Load more products';
	const RETRY_LABEL = 'Couldn’t load more products. Try again';

	class Store {
		constructor() {
			this.grid    = $('#store__products');
			this.title   = $('[data-store-title]');
			this.sortLbl = $('[data-sort-label]');
			this.search  = $('#main-search');
			this.sortBtns = $$('.sort-btn');
			this.filterInputs = $$('.filter-input');
			if (!this.grid) return;

			this.controller = null;
			this.loading = false;
			this.autoPaused = false;
			this.searchTimer = null;
			this.filterTimer = null;

			// The query the grid is currently showing; later pages must use
			// the same one, even if the inputs have changed since.
			this.query = new URLSearchParams({ orderby: this.grid.dataset.sort || 'all' });
			this.page = 1;
			this.totalPages = parseInt(this.grid.dataset.totalPages, 10) || 1;
			this.nextUrl = null;

			// Archive pages: take over WooCommerce's numbered pagination.
			const pager = $('.woocommerce-pagination');
			if (pager) {
				const next = $('a.next', pager);
				this.nextUrl = next ? this.sameOrigin(next.getAttribute('href'), location.href) : null;
				pager.remove();
			}

			this.buildMore();
			this.bind();
			this.updateMore();
		}

		buildMore() {
			this.more = document.createElement('div');
			this.more.className = 'store__more';
			this.more.innerHTML = `<button type="button" class="store__more-btn">${escapeHTML(MORE_LABEL)}</button>`;
			this.moreBtn = $('button', this.more);
			this.grid.after(this.more);

			this.moreBtn.addEventListener('click', () => {
				this.autoPaused = false;
				this.loadMore({ focusNew: true });
			});

			// Auto-load when the button comes within reach of the viewport.
			if ('IntersectionObserver' in window) {
				this.observer = new IntersectionObserver((entries) => {
					if (!this.autoPaused && entries.some((e) => e.isIntersecting)) this.loadMore();
				}, { rootMargin: '0px 0px 600px 0px' });
				this.observer.observe(this.more);
			}
		}

		hasMore() {
			return Boolean(this.nextUrl) || this.page < this.totalPages;
		}

		updateMore() {
			this.more.hidden = !this.hasMore();
			this.moreBtn.disabled = this.loading;
			this.moreBtn.textContent = this.loading ? 'Loading…' : (this.autoPaused ? RETRY_LABEL : MORE_LABEL);
		}

		/**
		 * Re-observing makes the observer report the button's current state,
		 * so a batch that didn't fill the screen is followed by the next one.
		 */
		recheck() {
			if (!this.observer) return;
			this.observer.unobserve(this.more);
			this.observer.observe(this.more);
		}

		sameOrigin(href, base) {
			try {
				const url = new URL(href, base);
				return url.origin === location.origin ? url.href : null;
			} catch (e) {
				return null;
			}
		}

		bind() {
			this.sortBtns.forEach((btn) => {
				btn.addEventListener('click', () => this.onSortClick(btn));
			});
			this.filterInputs.forEach((inp) => {
				inp.addEventListener('change', () => {
					clearTimeout(this.filterTimer);
					this.filterTimer = setTimeout(() => this.fetchProducts(), 120);
				});
			});
			if (this.search) {
				this.search.addEventListener('input', () => {
					clearTimeout(this.searchTimer);
					this.searchTimer = setTimeout(() => this.fetchProducts(), 250);
				});
			}
		}

		onSortClick(btn) {
			if (btn.classList.contains('is-active')) return;
			this.sortBtns.forEach((b) => b.classList.remove('is-active'));
			btn.classList.add('is-active');
			if (this.sortLbl) {
				this.sortLbl.textContent = 'Sort by';
			}
			if (this.title) {
				this.title.textContent = btn.dataset.sortName || 'Store';
			}
			this.grid.dataset.sort = btn.dataset.sort || 'all';
			this.fetchProducts();
		}

		activeSort() {
			const active = this.sortBtns.find((b) => b.classList.contains('is-active'));
			return active ? (active.dataset.sort || 'all') : 'all';
		}

		checkedFilters(kind) {
			return this.filterInputs
				.filter((i) => i.dataset.filter === kind && i.checked)
				.map((i) => i.value);
		}

		currentQuery() {
			const params = new URLSearchParams();
			params.set('orderby', this.activeSort());
			this.checkedFilters('brands').forEach((b) => params.append('brands[]', b));
			this.checkedFilters('categories').forEach((c) => params.append('categories[]', c));
			const search = (this.search ? this.search.value : '').trim();
			if (search) params.set('search', search);
			return params;
		}

		async fetchRestPage(page, signal) {
			const base = (window.localData && window.localData.productsUrl)
				? window.localData.productsUrl
				: `${(window.localData && window.localData.siteUrl) || ''}/wp-json/alistclub/v1/products`;
			// Merge into the base URL's own query (it is ?rest_route=… without pretty permalinks).
			const url = new URL(base, location.href);
			this.query.forEach((value, key) => url.searchParams.append(key, value));
			if (page > 1) url.searchParams.set('page', String(page));

			const res = await fetch(url.href, {
				signal,
				headers: { 'X-WP-Nonce': (window.localData && window.localData.restNonce) || '' }
			});
			if (!res.ok) throw new Error('Products request failed');
			return res.json();
		}

		async fetchArchivePage(url, signal) {
			const res = await fetch(url, { signal });
			if (!res.ok) throw new Error('Archive page request failed');
			const doc  = new DOMParser().parseFromString(await res.text(), 'text/html');
			const next = $('.woocommerce-pagination a.next', doc);
			return {
				items: $$('#store__products .product-item', doc),
				next: next ? this.sameOrigin(next.getAttribute('href'), url) : null,
			};
		}

		/**
		 * Starts a request, cancelling any in flight. `task` gets the abort
		 * signal; state is only touched if this is still the latest request.
		 */
		async run(task, onError) {
			if (this.controller) this.controller.abort();
			const controller = this.controller = new AbortController();
			this.loading = true;
			this.updateMore();

			try {
				await task(controller.signal);
			} catch (err) {
				if (err.name !== 'AbortError' && this.controller === controller) onError(err);
			} finally {
				if (this.controller === controller) {
					this.controller = null;
					this.loading = false;
					this.grid.classList.remove('is-loading');
					this.updateMore();
					this.recheck();
				}
			}
		}

		/**
		 * Sort / filter / search changed: replace the grid with page 1.
		 */
		fetchProducts() {
			this.query = this.currentQuery();
			this.page = 1;
			this.totalPages = 1;
			this.nextUrl = null;
			this.autoPaused = false;
			this.grid.classList.add('is-loading');

			return this.run(async (signal) => {
				const data = await this.fetchRestPage(1, signal);
				this.totalPages = data.total_pages || 1;
				this.render(data.products || []);
			}, () => {
				this.grid.innerHTML = `<p class="store__error">${escapeHTML('Could not load products. Please try again.')}</p>`;
			});
		}

		/**
		 * Append the next page of the current query.
		 */
		loadMore({ focusNew = false } = {}) {
			if (this.loading || !this.hasMore()) return;
			const firstNew = this.grid.children.length;

			return this.run(async (signal) => {
				if (this.nextUrl) {
					const { items, next } = await this.fetchArchivePage(this.nextUrl, signal);
					this.grid.append(...items);
					this.nextUrl = next;
				} else {
					const data = await this.fetchRestPage(this.page + 1, signal);
					this.grid.insertAdjacentHTML('beforeend', (data.products || []).map((p) => this.tile(p)).join(''));
					this.page += 1;
					this.totalPages = data.total_pages || this.page;
				}
				// Keyboard users who pressed the button continue from the first new product.
				if (focusNew) {
					const link = this.grid.children[firstNew] && $('a', this.grid.children[firstNew]);
					if (link) link.focus();
				}
			}, () => {
				// Stop auto-loading so a failing request isn't retried in a loop;
				// the button stays for a manual retry.
				this.autoPaused = true;
			});
		}

		render(products) {
			if (!products.length) {
				this.grid.innerHTML = `<p class="store__empty">${escapeHTML('No products match these filters.')}</p>`;
				return;
			}
			this.grid.innerHTML = products.map((p) => this.tile(p)).join('');
		}

		tile(p) {
			const name  = p.name || '';
			const brand = p.brand || '';
			return `
				<div class="product-item"
					data-name="${escapeHTML(name.toLowerCase())}"
					data-brand="${escapeHTML(brand.toLowerCase())}">
					<a class="product-link" href="${escapeHTML(p.permalink || '#')}">
						<div class="product-image">
							${p.image ? `<img src="${escapeHTML(p.image)}" alt="${escapeHTML(name)}" loading="lazy" decoding="async">` : ''}
						</div>
						<div class="product-name">${escapeHTML(name)}</div>
						${brand ? `<div class="product-brand">${escapeHTML(brand)}</div>` : ''}
						<div class="product-price">${p.price_html || ''}</div>
					</a>
					<div class="product-cart">${p.add_to_cart_html || ''}</div>
				</div>`;
		}

	}

	/**
	 * Flash Notice modal — shows a configurable popup until dismissed.
	 * Cookie value = content version, so editing the message re-shows it.
	 */
	const FLASH_COOKIE = 'alistclub_flash_dismissed';

	function readCookie(name) {
		const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[$()*+./?[\\\]^{|}]/g, '\\$&') + '=([^;]*)'));
		return match ? decodeURIComponent(match[1]) : '';
	}

	function writeCookie(name, value, days) {
		const parts = [name + '=' + encodeURIComponent(value), 'path=/', 'SameSite=Lax'];
		if (days > 0) {
			const d = new Date();
			d.setTime(d.getTime() + days * 86400000);
			parts.push('expires=' + d.toUTCString());
		}
		document.cookie = parts.join('; ');
	}

	class FlashNotice {
		constructor() {
			this.overlay = $('#flash-notice');
			this.configEl = $('#flash-notice-config');
			if (!this.overlay || !this.configEl) return;

			try {
				this.config = JSON.parse(this.configEl.textContent || '{}');
			} catch (e) {
				return;
			}
			if (!this.config || !this.config.version) return;
			if (readCookie(FLASH_COOKIE) === this.config.version) return;

			this.closeBtn = $('#flash-notice-close', this.overlay);
			this.lastFocus = null;
			this.bind();
			setTimeout(() => this.open(), 400);
		}

		bind() {
			if (this.closeBtn) {
				this.closeBtn.addEventListener('click', () => this.dismiss());
			}
			this.overlay.addEventListener('click', (e) => {
				if (e.target === this.overlay) this.dismiss();
			});
			document.addEventListener('keyup', (e) => {
				if (e.key === 'Escape' && this.isOpen()) this.dismiss();
			});
			$$('[data-flash-action]', this.overlay).forEach((btn) => {
				btn.addEventListener('click', () => this.dismiss());
			});
		}

		isOpen() { return this.overlay.classList.contains('is-open'); }

		open() {
			this.lastFocus = document.activeElement;
			this.overlay.classList.add('is-open');
			this.overlay.setAttribute('aria-hidden', 'false');
			document.body.classList.add('lock');
			if (this.closeBtn) setTimeout(() => this.closeBtn.focus(), 50);
		}

		dismiss() {
			this.overlay.classList.remove('is-open');
			this.overlay.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('lock');
			const days = this.config.showOnce ? 365 : (parseInt(this.config.cookieDays, 10) || 0);
			writeCookie(FLASH_COOKIE, this.config.version, days);
			if (this.lastFocus && typeof this.lastFocus.focus === 'function') {
				this.lastFocus.focus();
			}
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
		new Store();
		new FlashNotice();
		initWCExtras();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
