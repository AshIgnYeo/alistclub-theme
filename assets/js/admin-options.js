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

	const $list   = $('#alistclub-banner-list');
	const $tpl    = $('#alistclub-banner-template');
	const $addBtn = $('#alistclub-add-banner');

	if (!$list.length) return;

	/**
	 * Re-key all input names so server-side array indexes are sequential.
	 */
	function reindex() {
		$list.children('.alistclub-banner-row').each(function (newIdx) {
			$(this).attr('data-index', newIdx);
			$(this).find('input, select, textarea').each(function () {
				const name = $(this).attr('name');
				if (!name) return;
				$(this).attr('name', name.replace(/\[banners\]\[[^\]]+\]/, '[banners][' + newIdx + ']'));
			});
		});
	}

	/**
	 * Make rows sortable by their handle.
	 */
	$list.sortable({
		handle: '.alistclub-banner-handle',
		placeholder: 'alistclub-banner-row alistclub-placeholder',
		forcePlaceholderSize: true,
		update: reindex,
	});

	/**
	 * Add new banner.
	 */
	$addBtn.on('click', function () {
		const idx = $list.children('.alistclub-banner-row').length;
		const html = $tpl.html().replace(/__INDEX__/g, idx);
		$list.append(html);
	});

	/**
	 * Delete banner.
	 */
	$list.on('click', '.alistclub-banner-delete', function () {
		if (!window.confirm(alistclubOptions.confirmRemove)) return;
		$(this).closest('.alistclub-banner-row').remove();
		reindex();
	});

	/**
	 * Image picker (delegated).
	 */
	let frame;
	$list.on('click', '.alistclub-image-select', function (e) {
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

	/**
	 * Image remove (delegated).
	 */
	$list.on('click', '.alistclub-image-remove', function (e) {
		e.preventDefault();
		const $field = $(this).closest('.alistclub-image-field');
		$field.find('.alistclub-image-id').val('');
		$field.find('.alistclub-image-preview').removeClass('has-image').empty();
		$(this).hide();
	});

	/* ---------------------------------------------------------- *
	 * Flash Notice button repeater
	 * ---------------------------------------------------------- */
	const $flashList   = $('#alistclub-flash-button-list');
	const $flashTpl    = $('#alistclub-flash-button-template');
	const $flashAddBtn = $('#alistclub-add-flash-button');

	if ($flashList.length) {
		function reindexFlash() {
			$flashList.children('.alistclub-flash-button-row').each(function (newIdx) {
				$(this).attr('data-index', newIdx);
				$(this).find('input, select, textarea').each(function () {
					const name = $(this).attr('name');
					if (!name) return;
					$(this).attr('name', name.replace(/\[flash_buttons\]\[[^\]]+\]/, '[flash_buttons][' + newIdx + ']'));
				});
			});
		}

		$flashList.sortable({
			handle: '.alistclub-banner-handle',
			placeholder: 'alistclub-flash-button-row alistclub-placeholder',
			forcePlaceholderSize: true,
			update: reindexFlash,
		});

		$flashAddBtn.on('click', function () {
			const idx = $flashList.children('.alistclub-flash-button-row').length;
			const html = $flashTpl.html().replace(/__INDEX__/g, idx);
			$flashList.append(html);
		});

		$flashList.on('click', '.alistclub-flash-button-delete', function () {
			if (!window.confirm(alistclubOptions.confirmRemoveButton)) return;
			$(this).closest('.alistclub-flash-button-row').remove();
			reindexFlash();
		});
	}
})(jQuery);
