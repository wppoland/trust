=== Trust - Trust Badges for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, trust badges, secure checkout, payment icons, conversion
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show trust and secure-checkout badges to boost buyer confidence and conversions.

== Description ==

Trust adds a row of reassuring trust and secure-checkout badges next to the add-to-cart button, with a short heading such as "Guaranteed safe checkout". Shoppers see, right at the moment they decide to buy, that your store is safe — which lifts conversions.

The plugin ships a set of clean, hand-drawn inline SVG badges (secure checkout, SSL encrypted, money-back guarantee, verified store, free shipping, card payment, digital wallet, support, privacy and satisfaction). You can also upload your own image badges for accreditations or payment logos you are licensed to use.

= Pure presentation, zero bloat =

* **No external requests.** All bundled badges are inline SVGs served from your own site. Nothing is loaded from third parties, so there is no tracking and no privacy concern.
* **No layout shift (CLS).** The badge row is CSS-only with no JavaScript on the storefront.
* **Accessible.** Badges expose accessible names, the row uses a semantic list, and motion respects `prefers-reduced-motion`.
* **Themeable.** Choose the icon colour, size and alignment; badges inherit your colour via `currentColor`.

= Where badges appear =

* Single product page — before or after the add-to-cart button, or at the end of the product summary.
* Cart page (optional).
* Checkout page (optional).
* Anywhere via the `[trust_badges]` shortcode.

= Settings =

A settings page under the WooCommerce menu lets you:

* Enable or disable the badges.
* Write the heading (or leave it empty for icons only).
* Pick which bundled badges to show and reorder them.
* Add custom image badges from the media library.
* Choose placement (product / cart / checkout and the product position).
* Tune the alignment, icon size, colour, and whether to show text labels.

A live preview on the settings screen shows your choices before you save.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/trust`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be installed and active.
3. Visit **WooCommerce → Trust Badges** to choose your badges, heading and placement.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. Trust requires an active WooCommerce installation.

= Does it load anything from third-party servers? =

No. All bundled badges are inline SVGs served from your own site. Custom badges you upload are served from your media library.

= Can I use my own badge images? =

Yes. Add image badges from the WordPress media library on the settings screen. They appear after the bundled badges.

= Will it slow my store down or shift the layout? =

No. The storefront output is CSS-only with no JavaScript, and the row is reserved inline so it does not cause layout shift.

= Can I place badges somewhere custom? =

Yes. Use the `[trust_badges]` shortcode to render the row anywhere shortcodes are supported.

== Screenshots ==

1. The trust-badge row beneath the add-to-cart button on a single product page.
2. The Trust Badges settings screen with the live preview.

== Changelog ==

= 0.1.0 =
* Initial release: bundled inline SVG trust badges plus custom image badges, with a configurable heading, placement (product / cart / checkout), alignment, size and colour, a `[trust_badges]` shortcode, and a live-preview settings screen.
