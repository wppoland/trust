<?php
/**
 * Default settings, merged under the option key `trust_settings`.
 *
 * The plugin ships enabled, showing a row of secure-checkout badges with a
 * "Guaranteed safe checkout" heading after the add-to-cart button on the single
 * product page. The merchant tunes the heading, which badges show and the icon
 * colour from the Trust admin screen.
 *
 * No translation calls here: this file is required at boot to seed the option,
 * and the badge labels are translated where they are displayed, never stored.
 *
 * @package Trust
 *
 * @return array<string, mixed>
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

return [
    'enabled' => true,

    // Heading shown above the badge row. Empty hides the heading entirely.
    'heading' => 'Guaranteed safe checkout',

    // Which bundled badges to show, in display order (slugs from BadgeLibrary).
    'badges' => ['secure_checkout', 'ssl_encrypted', 'money_back', 'card_payment'],

    // Show the row after the add-to-cart button on single product pages.
    'show_on_product' => true,

    // Colour applied to the bundled SVG icons and heading.
    'icon_color' => '#3c4858',
];
