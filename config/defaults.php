<?php
/**
 * Default settings, merged under the option key `trust_settings`.
 *
 * The plugin ships enabled, showing a row of secure-checkout badges with a
 * "Guaranteed safe checkout" heading near the add-to-cart button on the single
 * product page. The merchant tunes the heading, which badges show, the colour,
 * size, alignment and placement from the Trust admin screen.
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

    // Placement on the storefront.
    'show_on_product'  => true,
    'product_position' => 'after_add_to_cart', // 'after_add_to_cart' | 'after_summary' | 'before_add_to_cart'
    'show_on_cart'     => false,
    'show_on_checkout' => false,

    // Presentation.
    'alignment'  => 'left',   // 'left' | 'center' | 'right'
    'size'       => 'medium', // 'small' | 'medium' | 'large'
    'icon_color' => '#3c4858',
    'show_labels' => false,    // Show each badge's text label beneath its icon.

    // Optional custom image badges (attachment IDs uploaded by the merchant).
    'custom_badges' => [],
];
