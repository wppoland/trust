/**
 * Trust — admin settings enhancements (progressive, dependency-free except for
 * the WordPress media modal used for custom image badges).
 *
 * 1. Inline help: each "?" button toggles an accessible tooltip (aria-expanded).
 * 2. Custom image badges: opens the WP media modal and appends a hidden input +
 *    thumbnail per chosen image; each can be removed.
 * 3. Live preview: mirrors the heading, the selected badge icons (cloned from
 *    the picker SVGs), custom thumbnails, alignment, size and colour in real
 *    time so the merchant sees the result before saving.
 *
 * Loaded with `defer`; everything still saves with JS disabled.
 */
( function () {
	'use strict';

	var root = document.querySelector( '.trust-admin' );

	if ( ! root ) {
		return;
	}

	var i18n =
		( window.wp && window.wp.i18n ) || {
			__: function ( s ) {
				return s;
			},
		};
	var __ = i18n.__;

	/* ---- Inline help tooltips --------------------------------------- */

	function closeAllTips( except ) {
		root.querySelectorAll( '.trust-help[aria-expanded="true"]' ).forEach(
			function ( btn ) {
				if ( btn === except ) {
					return;
				}
				btn.setAttribute( 'aria-expanded', 'false' );
				var tip = document.getElementById(
					btn.getAttribute( 'aria-describedby' )
				);
				if ( tip ) {
					tip.hidden = true;
				}
			}
		);
	}

	root.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest( '.trust-help' );

		if ( ! btn ) {
			return;
		}

		var tip = document.getElementById(
			btn.getAttribute( 'aria-describedby' )
		);

		if ( ! tip ) {
			return;
		}

		var open = btn.getAttribute( 'aria-expanded' ) === 'true';
		closeAllTips( btn );
		btn.setAttribute( 'aria-expanded', String( ! open ) );
		tip.hidden = open;
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'Escape' ) {
			closeAllTips( null );
		}
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( ! event.target.closest( '.trust-help, .trust-tip' ) ) {
			closeAllTips( null );
		}
	} );

	/* ---- Custom image badges ---------------------------------------- */

	var customWrap = root.querySelector( '[data-trust-custom]' );

	if ( customWrap && window.wp && window.wp.media ) {
		var list = customWrap.querySelector( '[data-trust-custom-list]' );
		var addBtn = customWrap.querySelector( '[data-trust-custom-add]' );
		var template = customWrap.querySelector(
			'[data-trust-custom-template]'
		);
		var frame;

		if ( addBtn && list && template ) {
			addBtn.addEventListener( 'click', function () {
				if ( ! frame ) {
					frame = window.wp.media( {
						title: __( 'Select badge image', 'trust' ),
						button: { text: __( 'Use this image', 'trust' ) },
						library: { type: 'image' },
						multiple: true,
					} );

					frame.on( 'select', function () {
						frame
							.state()
							.get( 'selection' )
							.toJSON()
							.forEach( addItem );
						schedule();
					} );
				}

				frame.open();
			} );

			list.addEventListener( 'click', function ( event ) {
				var remove = event.target.closest(
					'[data-trust-custom-remove]'
				);
				if ( remove ) {
					var item = remove.closest( '[data-trust-custom-item]' );
					if ( item ) {
						item.remove();
						schedule();
					}
				}
			} );
		}

		function addItem( attachment ) {
			var url =
				attachment.sizes && attachment.sizes.thumbnail
					? attachment.sizes.thumbnail.url
					: attachment.url;

			var node = template.content.firstElementChild.cloneNode( true );
			node.querySelector( 'img' ).src = url;
			node.querySelector( 'input' ).value = attachment.id;
			list.appendChild( node );
		}
	}

	/* ---- Live preview ----------------------------------------------- */

	var preview = root.querySelector( '[data-trust-preview]' );

	if ( ! preview ) {
		return;
	}

	var previewHeading = preview.querySelector(
		'[data-trust-preview-heading]'
	);
	var previewList = preview.querySelector( '[data-trust-preview-list]' );
	var previewEmpty = preview.querySelector( '[data-trust-preview-empty]' );

	function field( name ) {
		return root.querySelector( '[name="trust_settings[' + name + ']"]' );
	}

	function render() {
		// Heading.
		var heading = field( 'heading' );
		previewHeading.textContent = heading ? heading.value.trim() : '';

		// Alignment + size + colour.
		var align = field( 'alignment' );
		var size = field( 'size' );
		var color = field( 'icon_color' );

		var alignValue = align ? align.value : 'left';
		var justify =
			alignValue === 'center'
				? 'center'
				: alignValue === 'right'
				? 'flex-end'
				: 'flex-start';
		previewList.style.justifyContent = justify;
		previewHeading.style.textAlign = alignValue;

		var px = size && size.value === 'small' ? 26 : size && size.value === 'large' ? 46 : 34;
		var colorValue = color ? color.value : '#3c4858';

		previewList.innerHTML = '';
		var count = 0;

		// Bundled badges: clone the SVG from each checked picker option.
		root
			.querySelectorAll( '.trust-badge-option input:checked' )
			.forEach( function ( input ) {
				var iconWrap = input
					.closest( '.trust-badge-option' )
					.querySelector( '.trust-badge-option__icon svg' );
				if ( ! iconWrap ) {
					return;
				}
				var li = document.createElement( 'li' );
				var svg = iconWrap.cloneNode( true );
				svg.style.inlineSize = px + 'px';
				svg.style.blockSize = px + 'px';
				svg.style.color = colorValue;
				li.appendChild( svg );
				previewList.appendChild( li );
				count++;
			} );

		// Custom image badges.
		root
			.querySelectorAll( '[data-trust-custom-item] img' )
			.forEach( function ( img ) {
				if ( ! img.src ) {
					return;
				}
				var li = document.createElement( 'li' );
				var clone = document.createElement( 'img' );
				clone.src = img.src;
				clone.alt = '';
				clone.style.blockSize = px + 'px';
				li.appendChild( clone );
				previewList.appendChild( li );
				count++;
			} );

		if ( previewEmpty ) {
			previewEmpty.hidden = count > 0;
		}
	}

	var debounce;
	function schedule() {
		window.clearTimeout( debounce );
		debounce = window.setTimeout( render, 100 );
	}

	root.addEventListener( 'input', schedule );
	root.addEventListener( 'change', schedule );

	render();
} )();
