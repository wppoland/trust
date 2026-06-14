# Trust - Trust Badges for WooCommerce

Show trust and secure-checkout badges to boost buyer confidence and conversions.

Trust adds a row of reassuring trust / secure-checkout badges next to the
add-to-cart button (and optionally on cart and checkout), with a short heading
such as "Guaranteed safe checkout". It is **pure presentation**: bundled inline
SVGs plus optional custom image uploads, served entirely from your own site —
no external requests, no tracking.

## Features (FREE)

- A curated set of bundled **inline SVG** trust badges (secure checkout, SSL,
  money-back, verified, free shipping, card, wallet, support, privacy,
  satisfaction). They inherit your chosen colour via `currentColor`.
- Optional **custom image badges** uploaded from the media library.
- Configurable **heading** (or icons only).
- **Placement**: single product (before / after add-to-cart, or end of summary),
  cart, checkout, and the `[trust_badges]` shortcode.
- **Appearance**: alignment, icon size, colour, optional text labels.
- A **live preview** on the settings screen.
- CSS-only storefront output — **no JavaScript, no layout shift**.

## Architecture

- **Bootstrap** (`trust.php`): PHP/WooCommerce guards, HPOS + cart-blocks compat,
  `init` priority 0 boot, `do_action('trust/booted')` fired from `Plugin::boot()`
  (the hook a PRO companion extends). No translation calls at `plugins_loaded`.
- **Autoload** (`autoload.php`): Composer vendor autoloader + PSR-4 fallback.
  Self-contained — no shared kit dependency.
- **DI**: `src/Plugin.php` singleton + `src/Container.php` (with `has()`);
  services in `config/services.php`, boot order in `config/hooks.php`, defaults in
  `config/defaults.php`; idempotent `src/Migrator.php` seeds defaults.
- **Badges**: `src/Badges/BadgeLibrary.php` is the single source of truth for the
  bundled SVGs; `src/Service/BadgesService.php` renders the row; the template
  lives in `templates/badges.php`.
- **Admin**: `src/Admin/Settings.php` registers a WooCommerce submenu page.
- **Quality**: `phpcs.xml.dist` (WPCS), `phpstan.neon.dist` (level 6 + WC stubs),
  `.distignore`, `.wp-env.json`, bundled `languages/trust.pot`.

## Development

```bash
composer install
composer cs        # PHPCS
composer analyse   # PHPStan level 6
```

## PRO companion

`trust-pro` (separate private repo) boots via `add_action('trust/booted', …)`
and adds premium badge features.
