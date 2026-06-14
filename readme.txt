=== Trust - Trust Badges for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, trust badges, secure checkout, conversion, ecommerce
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show trust and secure-checkout badges to boost buyer confidence and conversions.

== Description ==

Trust adds a row of reassuring secure-checkout badges after the add-to-cart button, with a short heading such as "Guaranteed safe checkout". Shoppers see, right at the moment they decide to buy, that your store is safe — which lifts conversions.

The plugin ships a set of clean, hand-drawn inline SVG badges (secure checkout, SSL encrypted, money-back guarantee, verified store, free shipping, card payment, digital wallet, support, privacy and satisfaction). Pick the ones you want, write a heading and choose a colour.

= Pure presentation, zero bloat =

* **No external requests.** All bundled badges are inline SVGs served from your own site. Nothing is loaded from third parties, so there is no tracking and no privacy concern.
* **No layout shift (CLS).** The badge row is CSS-only with no JavaScript on the storefront.
* **Accessible.** Each badge exposes an accessible name and the row uses a semantic list. Motion respects `prefers-reduced-motion`.
* **Themeable.** Choose the icon colour; badges inherit it via `currentColor`.

= Where badges appear =

* Single product page — after the add-to-cart button.
* Anywhere via the `[trust_badges]` shortcode.

= Settings =

A settings page under the WooCommerce menu lets you:

* Enable or disable the badges.
* Write the heading (or leave it empty for icons only).
* Pick which bundled badges to show.
* Choose the icon colour.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/trust`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be installed and active.
3. Visit **WooCommerce → Trust Badges** to choose your badges, heading and colour.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. Trust requires an active WooCommerce installation.

= Does it load anything from third-party servers? =

No. All bundled badges are inline SVGs served from your own site.

= Will it slow my store down or shift the layout? =

No. The storefront output is CSS-only with no JavaScript, so it does not cause layout shift.

= Can I place badges somewhere custom? =

Yes. Use the `[trust_badges]` shortcode to render the row anywhere shortcodes are supported.

== Screenshots ==

1. The trust-badge row beneath the add-to-cart button on a single product page.
2. The Trust Badges settings screen.

== Changelog ==

= 0.1.0 =
* Initial release: bundled inline SVG trust badges shown after the add-to-cart button on single product pages, with a configurable heading, badge selection, icon colour and a `[trust_badges]` shortcode.
