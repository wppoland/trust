<?php

declare(strict_types=1);

namespace Trust\Service;

use Trust\Badges\BadgeLibrary;
use Trust\Contract\HasHooks;

defined('ABSPATH') || exit;

/**
 * Renders the trust-badge row on the storefront.
 *
 * Pure presentation: it reads the merchant's settings, builds the list of badges
 * to show (bundled inline SVGs plus any custom uploaded images) and prints an
 * accessible, CSS-only group near the add-to-cart button — and optionally on the
 * cart and checkout. It never makes external requests and renders nothing when
 * disabled, misconfigured, or when no badges are selected (no broken output).
 */
final class BadgesService implements HasHooks
{
    private const OPTION = 'trust_settings';

    /** A `[trust_badges]` shortcode renders the group anywhere. */
    private const SHORTCODE = 'trust_badges';

    public function registerHooks(): void
    {
        $settings = $this->settings();

        if (empty($settings['enabled'])) {
            return;
        }

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_shortcode(self::SHORTCODE, [$this, 'renderShortcode']);

        if (! empty($settings['show_on_product'])) {
            $this->hookProductPlacement((string) ($settings['product_position'] ?? 'after_add_to_cart'));
        }

        if (! empty($settings['show_on_cart'])) {
            add_action('woocommerce_after_cart_totals', [$this, 'renderRow']);
        }

        if (! empty($settings['show_on_checkout'])) {
            add_action('woocommerce_review_order_after_payment', [$this, 'renderRow']);
        }
    }

    /**
     * Attach the single-product placement to the chosen WooCommerce hook.
     */
    private function hookProductPlacement(string $position): void
    {
        switch ($position) {
            case 'before_add_to_cart':
                add_action('woocommerce_before_add_to_cart_form', [$this, 'renderRow']);
                break;
            case 'after_summary':
                add_action('woocommerce_single_product_summary', [$this, 'renderRow'], 45);
                break;
            case 'after_add_to_cart':
            default:
                add_action('woocommerce_after_add_to_cart_form', [$this, 'renderRow']);
                break;
        }
    }

    /**
     * Load the front-end stylesheet only where a badge row may render.
     */
    public function enqueueAssets(): void
    {
        $settings = $this->settings();

        $needed =
            (! empty($settings['show_on_product']) && function_exists('is_product') && is_product())
            || (! empty($settings['show_on_cart']) && function_exists('is_cart') && is_cart())
            || (! empty($settings['show_on_checkout']) && function_exists('is_checkout') && is_checkout());

        // The shortcode can appear anywhere; enqueue on demand from renderRow()
        // too, but registering here keeps the common storefront paths covered.
        if (! $needed) {
            wp_register_style('trust-badges', \TRUST_URL . 'assets/css/badges.css', [], \Trust\VERSION);
            return;
        }

        wp_enqueue_style('trust-badges', \TRUST_URL . 'assets/css/badges.css', [], \Trust\VERSION);
        $this->printInlineColor();
    }

    /**
     * Shortcode handler: `[trust_badges]`. Returns the badge row markup.
     *
     * @param array<string, mixed>|string $atts
     */
    public function renderShortcode(mixed $atts): string
    {
        unset($atts); // No attributes today; the group reads the saved settings.

        wp_enqueue_style('trust-badges', \TRUST_URL . 'assets/css/badges.css', [], \Trust\VERSION);

        ob_start();
        $this->renderRow();

        return (string) ob_get_clean();
    }

    /**
     * Print the badge row. Safe to call from any storefront hook; renders
     * nothing when there is nothing valid to show.
     */
    public function renderRow(): void
    {
        $settings = $this->settings();

        if (empty($settings['enabled'])) {
            return;
        }

        $items = $this->resolveItems($settings);

        if ($items === []) {
            return;
        }

        // Make sure the stylesheet is present even when the row is printed by a
        // shortcode on an otherwise un-enqueued page.
        if (! wp_style_is('trust-badges', 'enqueued')) {
            wp_enqueue_style('trust-badges', \TRUST_URL . 'assets/css/badges.css', [], \Trust\VERSION);
            $this->printInlineColor();
        }

        $context = [
            'heading'     => (string) ($settings['heading'] ?? ''),
            'items'       => $items,
            'alignment'   => $this->oneOf((string) ($settings['alignment'] ?? 'left'), ['left', 'center', 'right'], 'left'),
            'size'        => $this->oneOf((string) ($settings['size'] ?? 'medium'), ['small', 'medium', 'large'], 'medium'),
            'show_labels' => ! empty($settings['show_labels']),
        ];

        $this->renderTemplate('badges', $context);
    }

    /**
     * Build the ordered list of renderable badge items from the settings.
     *
     * Each item is one of:
     *  - ['type' => 'svg',   'slug' => string, 'svg' => string, 'label' => string]
     *  - ['type' => 'image', 'url' => string,  'label' => string]
     *
     * @param array<string, mixed> $settings
     * @return list<array<string, string>>
     */
    private function resolveItems(array $settings): array
    {
        $items = [];

        $selected = isset($settings['badges']) && is_array($settings['badges']) ? $settings['badges'] : [];

        foreach ($selected as $slug) {
            $slug = (string) $slug;

            if (! BadgeLibrary::has($slug)) {
                continue;
            }

            $items[] = [
                'type'  => 'svg',
                'slug'  => $slug,
                'svg'   => BadgeLibrary::svg($slug),
                'label' => BadgeLibrary::label($slug),
            ];
        }

        $custom = isset($settings['custom_badges']) && is_array($settings['custom_badges']) ? $settings['custom_badges'] : [];

        foreach ($custom as $attachmentId) {
            $attachmentId = (int) $attachmentId;

            if ($attachmentId <= 0) {
                continue;
            }

            $url = wp_get_attachment_image_url($attachmentId, 'medium');

            if (! is_string($url) || $url === '') {
                continue;
            }

            $alt = get_post_meta($attachmentId, '_wp_attachment_image_alt', true);

            $items[] = [
                'type'  => 'image',
                'url'   => $url,
                'label' => is_string($alt) ? $alt : '',
            ];
        }

        return $items;
    }

    /**
     * Attach the merchant's icon colour as a CSS custom property. Done inline so
     * a colour change never requires regenerating a stylesheet, and is escaped.
     */
    private function printInlineColor(): void
    {
        $color = $this->sanitizeColor((string) ($this->settings()['icon_color'] ?? ''));

        if ($color === '') {
            return;
        }

        wp_add_inline_style(
            'trust-badges',
            sprintf('.trust-badges{--trust-icon-color:%s;}', $color),
        );
    }

    private function sanitizeColor(string $value): string
    {
        $color = sanitize_hex_color($value);

        return is_string($color) ? $color : '';
    }

    /**
     * @param list<string> $allowed
     */
    private function oneOf(string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    /**
     * Stored settings merged over packaged defaults.
     *
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        $stored = get_option(self::OPTION, []);

        if (! is_array($stored)) {
            $stored = [];
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require \TRUST_DIR . 'config/defaults.php';

        return array_merge($defaults, $stored);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function renderTemplate(string $template, array $context): void
    {
        $file = \TRUST_DIR . 'templates/' . $template . '.php';

        if (! is_readable($file)) {
            return;
        }

        extract($context, EXTR_SKIP);
        require $file;
    }
}
