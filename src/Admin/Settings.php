<?php

declare(strict_types=1);

namespace Trust\Admin;

defined('ABSPATH') || exit;

use Trust\Badges\BadgeLibrary;
use Trust\Contract\HasHooks;

/**
 * Admin settings page registered under the WooCommerce menu.
 *
 * Stores everything in the `trust_settings` option (array): the master toggle,
 * heading, the selected bundled badges, the single-product toggle and the icon
 * colour. All output is escaped; all input is sanitised on save against the
 * BadgeLibrary allowlist.
 */
final class Settings implements HasHooks
{
    private const OPTION = 'trust_settings';
    private const PAGE   = 'trust-settings';

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_filter('plugin_action_links_' . plugin_basename(\Trust\PLUGIN_FILE), [$this, 'addSettingsLink']);
    }

    /**
     * Add a "Settings" link on the Plugins screen row.
     *
     * @param array<int|string, string> $links
     * @return array<int|string, string>
     */
    public function addSettingsLink(array $links): array
    {
        $url = admin_url('admin.php?page=' . self::PAGE);

        $settingsLink = sprintf(
            '<a href="%s">%s</a>',
            esc_url($url),
            esc_html__('Settings', 'trust'),
        );

        array_unshift($links, $settingsLink);

        return $links;
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Trust Badges', 'trust'),
            __('Trust Badges', 'trust'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'renderPage'],
        );
    }

    /**
     * Load the settings-page stylesheet only on the Trust screen.
     */
    public function enqueueAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== 'woocommerce_page_' . self::PAGE) {
            return;
        }

        wp_enqueue_style(
            'trust-admin',
            \TRUST_URL . 'assets/css/admin.css',
            [],
            \Trust\VERSION,
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            self::PAGE,
            self::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
            ],
        );

        // The menu uses manage_woocommerce; align the options.php save capability
        // so shop managers (not just manage_options admins) can save.
        add_filter(
            'option_page_capability_' . self::PAGE,
            static fn (): string => 'manage_woocommerce',
        );
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $settings = $this->settings();
        $selected = is_array($settings['badges'] ?? null) ? array_map('strval', $settings['badges']) : [];
        ?>
        <div class="wrap trust-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="trust-admin__intro">
                <span class="trust-admin__intro-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
                        <path d="M12 3 5 6v5c0 4.2 2.9 8.1 7 9 4.1-.9 7-4.8 7-9V6l-7-3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        <path d="m9 12 2.2 2.2L15 10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="trust-admin__intro-text">
                    <h2><?php esc_html_e('Reassure shoppers at the moment they decide to buy', 'trust'); ?></h2>
                    <p><?php esc_html_e('Trust shows a row of secure-checkout badges with a short heading after the add-to-cart button. Pick the badges, write the heading and choose the colour.', 'trust'); ?></p>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE); ?>

                <div class="trust-card">
                    <h2><?php esc_html_e('General', 'trust'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable trust badges', 'trust'); ?></th>
                                <td>
                                    <label for="trust_enabled">
                                        <input type="checkbox" id="trust_enabled" name="<?php echo esc_attr(self::OPTION); ?>[enabled]" value="1" <?php checked((bool) ($settings['enabled'] ?? false), true); ?> />
                                        <?php esc_html_e('Show the trust-badge row on the storefront.', 'trust'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('When off, no badges and no stylesheet load anywhere — the storefront is completely unaffected.', 'trust'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Show on product pages', 'trust'); ?></th>
                                <td>
                                    <label for="trust_show_on_product">
                                        <input type="checkbox" id="trust_show_on_product" name="<?php echo esc_attr(self::OPTION); ?>[show_on_product]" value="1" <?php checked((bool) ($settings['show_on_product'] ?? false), true); ?> />
                                        <?php esc_html_e('Show the badge row after the add-to-cart button on single product pages.', 'trust'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('You can also place the row anywhere with the [trust_badges] shortcode.', 'trust'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="trust_heading"><?php esc_html_e('Heading', 'trust'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="trust_heading" name="<?php echo esc_attr(self::OPTION); ?>[heading]" value="<?php echo esc_attr((string) ($settings['heading'] ?? '')); ?>" class="regular-text" />
                                    <p class="description"><?php esc_html_e('Short reassurance shown above the badges, e.g. “Guaranteed safe checkout”. Leave empty to show only the icons.', 'trust'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="trust_icon_color"><?php esc_html_e('Icon colour', 'trust'); ?></label>
                                </th>
                                <td>
                                    <input type="color" id="trust_icon_color" name="<?php echo esc_attr(self::OPTION); ?>[icon_color]" value="<?php echo esc_attr($this->colorValue((string) ($settings['icon_color'] ?? '#3c4858'))); ?>" />
                                    <p class="description"><?php esc_html_e('Colour for the bundled SVG icons and heading.', 'trust'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="trust-card">
                    <h2><?php esc_html_e('Badges', 'trust'); ?></h2>
                    <p class="trust-card__intro"><?php esc_html_e('Choose which bundled badges to show. They are safe inline graphics — no external requests and no third-party logos.', 'trust'); ?></p>

                    <fieldset class="trust-badge-picker">
                        <legend class="screen-reader-text"><?php esc_html_e('Bundled badges', 'trust'); ?></legend>
                        <?php foreach (BadgeLibrary::all() as $slug => $badge) : ?>
                            <label class="trust-badge-option" for="<?php echo esc_attr('trust_badge_' . $slug); ?>">
                                <input
                                    type="checkbox"
                                    id="<?php echo esc_attr('trust_badge_' . $slug); ?>"
                                    name="<?php echo esc_attr(self::OPTION); ?>[badges][]"
                                    value="<?php echo esc_attr($slug); ?>"
                                    <?php checked(in_array($slug, $selected, true), true); ?>
                                />
                                <span class="trust-badge-option__icon" aria-hidden="true"><?php $this->printSvg($badge['svg']); ?></span>
                                <span class="trust-badge-option__label"><?php echo esc_html($badge['label']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Echo trusted BadgeLibrary SVG markup, filtered through an SVG-safe
     * allowlist for Plugin Check.
     */
    private function printSvg(string $svg): void
    {
        echo wp_kses(
            $svg,
            [
                'svg'    => ['viewbox' => true, 'fill' => true, 'xmlns' => true, 'focusable' => true, 'aria-hidden' => true, 'role' => true, 'width' => true, 'height' => true],
                'path'   => ['d' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true],
                'rect'   => ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linejoin' => true],
                'circle' => ['cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true],
                'g'      => ['fill' => true, 'stroke' => true],
            ],
        );
    }

    private function colorValue(string $value): string
    {
        $color = sanitize_hex_color($value);

        return is_string($color) && $color !== '' ? $color : '#3c4858';
    }

    /**
     * Sanitise submitted settings before save, preserving defaults for fields
     * not present on the form.
     *
     * @param mixed $raw
     * @return array<string, mixed>
     */
    public function sanitize(mixed $raw): array
    {
        if (! is_array($raw)) {
            $raw = [];
        }

        $defaults = $this->settings();

        // Badges: keep only known slugs, de-duplicated, in submitted order.
        $badges = [];
        if (isset($raw['badges']) && is_array($raw['badges'])) {
            foreach ($raw['badges'] as $slug) {
                $slug = sanitize_key((string) $slug);
                if (BadgeLibrary::has($slug) && ! in_array($slug, $badges, true)) {
                    $badges[] = $slug;
                }
            }
        }

        $heading = isset($raw['heading']) ? sanitize_text_field((string) $raw['heading']) : '';

        $color = isset($raw['icon_color']) ? sanitize_hex_color((string) $raw['icon_color']) : null;
        if (! is_string($color) || $color === '') {
            $color = (string) ($defaults['icon_color'] ?? '#3c4858');
        }

        $sanitized = array_merge($defaults, [
            'enabled'         => ! empty($raw['enabled']),
            'heading'         => $heading,
            'badges'          => $badges,
            'show_on_product' => ! empty($raw['show_on_product']),
            'icon_color'      => $color,
        ]);

        return (array) apply_filters('trust_sanitize_settings', $sanitized, $raw);
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
}
