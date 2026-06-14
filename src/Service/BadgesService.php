<?php

declare(strict_types=1);

namespace Trust\Service;

use Trust\Badges\BadgeLibrary;
use Trust\Contract\HasHooks;

defined('ABSPATH') || exit;

/**
 * Renders the trust-badge row on the storefront.
 *
 * Pure presentation: it reads the merchant's settings, builds the list of
 * bundled inline-SVG badges to show and prints an accessible, CSS-only group
 * after the add-to-cart button on single product pages (and anywhere via the
 * `[trust_badges]` shortcode). It never makes external requests and renders
 * nothing when disabled or when no badges are selected (no broken output).
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
            add_action('woocommerce_after_add_to_cart_form', [$this, 'renderRow']);
        }
    }

    /**
     * Load the front-end stylesheet only where a badge row may render.
     */
    public function enqueueAssets(): void
    {
        $settings = $this->settings();

        $needed = ! empty($settings['show_on_product'])
            && function_exists('is_product')
            && is_product();

        // The shortcode can appear anywhere; register here so it is available,
        // and enqueue on demand from renderRow() when the row actually prints.
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
            'heading' => (string) ($settings['heading'] ?? ''),
            'items'   => $items,
        ];

        $this->renderTemplate('badges', $context);
    }

    /**
     * Build the ordered list of renderable badge items from the settings.
     *
     * Each item is: ['slug' => string, 'svg' => string, 'label' => string].
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
                'slug'  => $slug,
                'svg'   => BadgeLibrary::svg($slug),
                'label' => BadgeLibrary::label($slug),
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
