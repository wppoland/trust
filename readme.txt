=== Trust - Trust Badges for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, trust badges, secure checkout, conversion, ecommerce
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a row of secure-checkout badges after the add-to-cart button to reassure shoppers about payment safety.

== Description ==

Trust shows a row of secure-checkout badges after the add-to-cart button, under a short heading like "Guaranteed safe checkout". The idea is simple: put a reminder that the store is safe right where the shopper decides whether to buy.

The plugin includes ten hand-drawn inline SVG badges: secure checkout, SSL encrypted, money-back guarantee, verified store, free shipping, card payment, digital wallet, 24/7 support, privacy protected and satisfaction. You pick which ones to show, write the heading (or leave it blank for icons only) and set the icon colour.

It is built to stay out of the way:

* **No external requests.** The badges are inline SVGs served from your own site, so nothing loads from third parties and there is nothing to track.
* **No JavaScript on the storefront.** The badge row is plain CSS, so it does not add a script or shift the layout as the page loads.
* **Accessible.** Each badge has an accessible name and the row is marked up as a list. The small hover animation is skipped for visitors who set `prefers-reduced-motion`.
* **Inherits your colour.** Set one colour and every badge follows it via `currentColor`.

Trust is not yet on the WordPress.org directory. The source lives on GitHub at https://github.com/wppoland/trust if you want to read the code or report a bug.

= Documentation and links =

* **Documentation** - https://plogins.com/trust/docs/
* **Plugin page** - https://plogins.com/trust/
* **Source code** - https://github.com/wppoland/trust
* **Bug reports and feature requests** - https://github.com/wppoland/trust/issues
* **Discussions and questions** - https://github.com/wppoland/trust/discussions


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

= Which badges are included? =

A curated set of inline SVG icons (secure checkout, shipping, returns and similar). Choose which to show in settings.

== Screenshots ==

1. The trust-badge row beneath the add-to-cart button on a single product page.
2. The Trust Badges settings screen.

== External Services ==

Trust does not connect to any external services. Every badge is a bundled inline SVG served from your own site, so the storefront output loads nothing from third parties and the plugin makes no network requests. Your choices (the heading, selected badges and icon colour) are kept on your own site in a single `trust_settings` option, alongside a `trust_db_version` marker; both are removed when you delete the plugin. The plugin sends no email and stores no visitor or customer data.

== Changelog ==

= 0.1.0 =
* Initial release: bundled inline SVG trust badges shown after the add-to-cart button on single product pages, with a configurable heading, badge selection, icon colour and a `[trust_badges]` shortcode.
