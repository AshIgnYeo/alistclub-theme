/* global jQuery, wp, alistclubOptions */
(function ($) {
	'use strict';

	/* ---------------------------------------------------------- *
	 * Sidebar nav — scrollspy + smooth scroll
	 * ---------------------------------------------------------- */
	const $navLinks = $('.alistclub-nav-link');
	if ($navLinks.length) {
		const sections = $navLinks.toArray().map((a) => {
			const id = a.getAttribute('href');
			return id && id.charAt(0) === '#' ? document.querySelector(id) : null;
		}).filter(Boolean);

		if ('IntersectionObserver' in window && sections.length) {
			const observer = new IntersectionObserver((entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						$navLinks.removeClass('is-active');
						$navLinks.filter('[href="#' + entry.target.id + '"]').addClass('is-active');
					}
				});
			}, { rootMargin: '-40% 0px -55% 0px', threshold: 0 });
			sections.forEach((s) => observer.observe(s));
		}
	}

	/* ---------------------------------------------------------- *
	 * Generic sortable repeater
	 * ---------------------------------------------------------- */
	/**
	 * Wire up an add / delete / drag-to-reorder repeater. Each call guards
	 * on its own list, so a repeater absent from the current page is simply
	 * skipped without affecting the others.
	 *
	 * @param {Object} cfg
	 * @param {string} cfg.list     Selector for the row container.
	 * @param {string} cfg.template Selector for the <script> row template.
	 * @param {string} cfg.add      Selector for the "add row" button.
	 * @param {string} cfg.row      Class selector (incl. dot) for a single row.
	 * @param {string} cfg.handle   Selector for the drag handle.
	 * @param {string} cfg.del      Selector for a row's delete button.
	 * @param {string} cfg.key      Option array key, e.g. 'banners'.
	 * @param {string} cfg.confirm  Confirmation message shown before delete.
	 * @return {jQuery|null} The list element, or null when absent from the page.
	 */
	function initRepeater(cfg) {
		const $list = $(cfg.list);
		if (!$list.length) return null;

		const $tpl    = $(cfg.template);
		const $addBtn = $(cfg.add);
		const keyRe   = new RegExp('\\[' + cfg.key + '\\]\\[[^\\]]+\\]');

		// Re-key all input names so server-side array indexes stay sequential.
		function reindex() {
			$list.children(cfg.row).each(function (newIdx) {
				$(this).attr('data-index', newIdx);
				$(this).find('input, select, textarea').each(function () {
					const name = $(this).attr('name');
					if (!name) return;
					$(this).attr('name', name.replace(keyRe, '[' + cfg.key + '][' + newIdx + ']'));
				});
			});
		}

		$list.sortable({
			handle: cfg.handle,
			placeholder: cfg.row.slice(1) + ' alistclub-placeholder',
			forcePlaceholderSize: true,
			update: reindex,
		});

		$addBtn.on('click', function () {
			const idx = $list.children(cfg.row).length;
			$list.append($tpl.html().replace(/__INDEX__/g, idx));
		});

		$list.on('click', cfg.del, function () {
			if (!window.confirm(cfg.confirm)) return;
			$(this).closest(cfg.row).remove();
			reindex();
		});

		return $list;
	}

	/* ---------------------------------------------------------- *
	 * Homepage banners
	 * ---------------------------------------------------------- */
	const $bannerList = initRepeater({
		list:     '#alistclub-banner-list',
		template: '#alistclub-banner-template',
		add:      '#alistclub-add-banner',
		row:      '.alistclub-banner-row',
		handle:   '.alistclub-banner-handle',
		del:      '.alistclub-banner-delete',
		key:      'banners',
		confirm:  alistclubOptions.confirmRemove,
	});

	if ($bannerList) {
		let frame;

		// Image picker (delegated).
		$bannerList.on('click', '.alistclub-image-select', function (e) {
			e.preventDefault();
			const $field = $(this).closest('.alistclub-image-field');

			frame = wp.media({
				title:    alistclubOptions.mediaTitle,
				button:   { text: alistclubOptions.mediaButton },
				multiple: false,
				library:  { type: 'image' },
			});

			frame.on('select', function () {
				const att = frame.state().get('selection').first().toJSON();
				const url = (att.sizes && att.sizes.medium && att.sizes.medium.url) || att.url;
				$field.find('.alistclub-image-id').val(att.id);
				const $preview = $field.find('.alistclub-image-preview').addClass('has-image');
				$preview.empty().append($('<img>').attr({ src: url, alt: '' }));
				$field.find('.alistclub-image-remove').show();
			});

			frame.open();
		});

		// Image remove (delegated).
		$bannerList.on('click', '.alistclub-image-remove', function (e) {
			e.preventDefault();
			const $field = $(this).closest('.alistclub-image-field');
			$field.find('.alistclub-image-id').val('');
			$field.find('.alistclub-image-preview').removeClass('has-image').empty();
			$(this).hide();
		});
	}

	/* ---------------------------------------------------------- *
	 * Flash Notice action buttons
	 * ---------------------------------------------------------- */
	initRepeater({
		list:     '#alistclub-flash-button-list',
		template: '#alistclub-flash-button-template',
		add:      '#alistclub-add-flash-button',
		row:      '.alistclub-flash-button-row',
		handle:   '.alistclub-banner-handle',
		del:      '.alistclub-flash-button-delete',
		key:      'flash_buttons',
		confirm:  alistclubOptions.confirmRemoveButton,
	});
})(jQuery);
