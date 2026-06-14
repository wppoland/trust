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
 * heading, the selected bundled badges + custom image badges, placement and
 * presentation. All output is escaped; all input is sanitised on save against
 * the BadgeLibrary allowlist.
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
     * Load the settings-page assets only on the Trust screen.
     */
    public function enqueueAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== 'woocommerce_page_' . self::PAGE) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style(
            'trust-admin',
            \TRUST_URL . 'assets/css/admin.css',
            [],
            \Trust\VERSION,
        );

        wp_enqueue_script(
            'trust-admin',
            \TRUST_URL . 'assets/js/admin.js',
            [],
            \Trust\VERSION,
            ['in_footer' => true, 'strategy' => 'defer'],
        );

        wp_set_script_translations('trust-admin', 'trust');
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
        $custom   = is_array($settings['custom_badges'] ?? null) ? array_map('intval', $settings['custom_badges']) : [];
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
                    <p><?php esc_html_e('Trust shows a row of secure-checkout and payment badges with a short heading next to the add-to-cart button. Pick the badges, write the heading and choose where it appears — hover a “?” for a quick explanation.', 'trust'); ?></p>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE); ?>

                <div class="trust-admin__layout">
                    <div class="trust-admin__main">

                        <div class="trust-card">
                            <h2><?php esc_html_e('General', 'trust'); ?></h2>
                            <table class="form-table" role="presentation">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <?php esc_html_e('Enable trust badges', 'trust'); ?>
                                            <?php $this->helpTip('enabled', __('Master switch. When off, no badges and no stylesheet load anywhere and the storefront is completely unaffected.', 'trust')); ?>
                                        </th>
                                        <td>
                                            <label for="trust_enabled">
                                                <input type="checkbox" id="trust_enabled" name="<?php echo esc_attr(self::OPTION); ?>[enabled]" value="1" aria-describedby="trust-tip-enabled" <?php checked((bool) ($settings['enabled'] ?? false), true); ?> />
                                                <?php esc_html_e('Show the trust-badge row on the storefront.', 'trust'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="trust_heading"><?php esc_html_e('Heading', 'trust'); ?></label>
                                            <?php $this->helpTip('heading', __('Short reassurance shown above the badges, e.g. “Guaranteed safe checkout”. Leave empty to show only the icons.', 'trust')); ?>
                                        </th>
                                        <td>
                                            <input type="text" id="trust_heading" name="<?php echo esc_attr(self::OPTION); ?>[heading]" value="<?php echo esc_attr((string) ($settings['heading'] ?? '')); ?>" class="regular-text" aria-describedby="trust-tip-heading" />
                                            <p class="description"><?php esc_html_e('Leave empty to hide the heading and show only the badge icons.', 'trust'); ?></p>
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

                        <div class="trust-card">
                            <h2><?php esc_html_e('Custom image badges', 'trust'); ?></h2>
                            <p class="trust-card__intro"><?php esc_html_e('Optionally add your own badge images (payment provider logos you are licensed to use, accreditations, etc.). They appear after the bundled badges.', 'trust'); ?></p>

                            <div class="trust-custom" data-trust-custom>
                                <ul class="trust-custom__list" data-trust-custom-list>
                                    <?php foreach ($custom as $attachmentId) : ?>
                                        <?php
                                        $url = wp_get_attachment_image_url($attachmentId, 'thumbnail');
                                        if (! is_string($url) || $url === '') {
                                            continue;
                                        }
                                        ?>
                                        <li class="trust-custom__item" data-trust-custom-item>
                                            <img src="<?php echo esc_url($url); ?>" alt="" />
                                            <input type="hidden" name="<?php echo esc_attr(self::OPTION); ?>[custom_badges][]" value="<?php echo esc_attr((string) $attachmentId); ?>" />
                                            <button type="button" class="trust-custom__remove" data-trust-custom-remove aria-label="<?php esc_attr_e('Remove this badge', 'trust'); ?>">&times;</button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <button type="button" class="button trust-custom__add" data-trust-custom-add>
                                    <?php esc_html_e('Add image badge', 'trust'); ?>
                                </button>

                                <template data-trust-custom-template>
                                    <li class="trust-custom__item" data-trust-custom-item>
                                        <img src="" alt="" />
                                        <input type="hidden" name="<?php echo esc_attr(self::OPTION); ?>[custom_badges][]" value="" />
                                        <button type="button" class="trust-custom__remove" data-trust-custom-remove aria-label="<?php esc_attr_e('Remove this badge', 'trust'); ?>">&times;</button>
                                    </li>
                                </template>
                            </div>
                        </div>

                        <div class="trust-card">
                            <h2><?php esc_html_e('Placement', 'trust'); ?></h2>
                            <table class="form-table" role="presentation">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <?php esc_html_e('Single product page', 'trust'); ?>
                                            <?php $this->helpTip('show_on_product', __('Show the badge row on single product pages. This is where it does the most work — right where shoppers decide to buy.', 'trust')); ?>
                                        </th>
                                        <td>
                                            <label for="trust_show_on_product">
                                                <input type="checkbox" id="trust_show_on_product" name="<?php echo esc_attr(self::OPTION); ?>[show_on_product]" value="1" aria-describedby="trust-tip-show_on_product" <?php checked((bool) ($settings['show_on_product'] ?? false), true); ?> />
                                                <?php esc_html_e('Show on single product pages.', 'trust'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="trust_product_position"><?php esc_html_e('Product position', 'trust'); ?></label>
                                            <?php $this->helpTip('product_position', __('Where the row sits relative to the add-to-cart form. “After add to cart” is the most common and effective placement.', 'trust')); ?>
                                        </th>
                                        <td>
                                            <?php $pos = (string) ($settings['product_position'] ?? 'after_add_to_cart'); ?>
                                            <select id="trust_product_position" name="<?php echo esc_attr(self::OPTION); ?>[product_position]" aria-describedby="trust-tip-product_position">
                                                <option value="after_add_to_cart" <?php selected($pos, 'after_add_to_cart'); ?>><?php esc_html_e('After add-to-cart button', 'trust'); ?></option>
                                                <option value="before_add_to_cart" <?php selected($pos, 'before_add_to_cart'); ?>><?php esc_html_e('Before add-to-cart button', 'trust'); ?></option>
                                                <option value="after_summary" <?php selected($pos, 'after_summary'); ?>><?php esc_html_e('End of product summary', 'trust'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <?php esc_html_e('Cart page', 'trust'); ?>
                                            <?php $this->helpTip('show_on_cart', __('Also show the badges beneath the cart totals, reassuring shoppers as they head to checkout.', 'trust')); ?>
                                        </th>
                                        <td>
                                            <label for="trust_show_on_cart">
                                                <input type="checkbox" id="trust_show_on_cart" name="<?php echo esc_attr(self::OPTION); ?>[show_on_cart]" value="1" aria-describedby="trust-tip-show_on_cart" <?php checked((bool) ($settings['show_on_cart'] ?? false), true); ?> />
                                                <?php esc_html_e('Show on the cart page.', 'trust'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <?php esc_html_e('Checkout page', 'trust'); ?>
                                            <?php $this->helpTip('show_on_checkout', __('Show the badges in the checkout order review, right by the payment step.', 'trust')); ?>
                                        </th>
                                        <td>
                                            <label for="trust_show_on_checkout">
                                                <input type="checkbox" id="trust_show_on_checkout" name="<?php echo esc_attr(self::OPTION); ?>[show_on_checkout]" value="1" aria-describedby="trust-tip-show_on_checkout" <?php checked((bool) ($settings['show_on_checkout'] ?? false), true); ?> />
                                                <?php esc_html_e('Show on the checkout page.', 'trust'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="trust-card">
                            <h2><?php esc_html_e('Appearance', 'trust'); ?></h2>
                            <table class="form-table" role="presentation">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="trust_alignment"><?php esc_html_e('Alignment', 'trust'); ?></label>
                                        </th>
                                        <td>
                                            <?php $align = (string) ($settings['alignment'] ?? 'left'); ?>
                                            <select id="trust_alignment" name="<?php echo esc_attr(self::OPTION); ?>[alignment]">
                                                <option value="left" <?php selected($align, 'left'); ?>><?php esc_html_e('Left', 'trust'); ?></option>
                                                <option value="center" <?php selected($align, 'center'); ?>><?php esc_html_e('Center', 'trust'); ?></option>
                                                <option value="right" <?php selected($align, 'right'); ?>><?php esc_html_e('Right', 'trust'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="trust_size"><?php esc_html_e('Icon size', 'trust'); ?></label>
                                        </th>
                                        <td>
                                            <?php $size = (string) ($settings['size'] ?? 'medium'); ?>
                                            <select id="trust_size" name="<?php echo esc_attr(self::OPTION); ?>[size]">
                                                <option value="small" <?php selected($size, 'small'); ?>><?php esc_html_e('Small', 'trust'); ?></option>
                                                <option value="medium" <?php selected($size, 'medium'); ?>><?php esc_html_e('Medium', 'trust'); ?></option>
                                                <option value="large" <?php selected($size, 'large'); ?>><?php esc_html_e('Large', 'trust'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="trust_icon_color"><?php esc_html_e('Icon colour', 'trust'); ?></label>
                                            <?php $this->helpTip('icon_color', __('Colour for the bundled SVG icons and heading. Custom image badges keep their own colours.', 'trust')); ?>
                                        </th>
                                        <td>
                                            <input type="color" id="trust_icon_color" name="<?php echo esc_attr(self::OPTION); ?>[icon_color]" value="<?php echo esc_attr($this->colorValue((string) ($settings['icon_color'] ?? '#3c4858'))); ?>" aria-describedby="trust-tip-icon_color" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <?php esc_html_e('Show labels', 'trust'); ?>
                                            <?php $this->helpTip('show_labels', __('Print each badge’s short text label beneath its icon. Off by default for a cleaner row.', 'trust')); ?>
                                        </th>
                                        <td>
                                            <label for="trust_show_labels">
                                                <input type="checkbox" id="trust_show_labels" name="<?php echo esc_attr(self::OPTION); ?>[show_labels]" value="1" aria-describedby="trust-tip-show_labels" <?php checked((bool) ($settings['show_labels'] ?? false), true); ?> />
                                                <?php esc_html_e('Show a text label under each badge.', 'trust'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <?php submit_button(); ?>
                    </div>

                    <aside class="trust-admin__aside">
                        <div class="trust-preview" data-trust-preview>
                            <h2 class="trust-preview__title"><?php esc_html_e('Live preview', 'trust'); ?></h2>
                            <div class="trust-preview__stage">
                                <p class="trust-preview__heading" data-trust-preview-heading></p>
                                <ul class="trust-preview__list" data-trust-preview-list></ul>
                                <p class="trust-preview__empty" data-trust-preview-empty hidden><?php esc_html_e('Select at least one badge to preview it here.', 'trust'); ?></p>
                            </div>
                            <p class="trust-preview__hint"><?php esc_html_e('Updates as you change the settings. Save to apply on your storefront.', 'trust'); ?></p>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Render an accessible "?" help affordance with a tooltip.
     */
    private function helpTip(string $key, string $text): void
    {
        $tipId = 'trust-tip-' . $key;
        ?>
        <button
            type="button"
            class="trust-help"
            data-trust-tip="<?php echo esc_attr($tipId); ?>"
            aria-label="<?php esc_attr_e('More information', 'trust'); ?>"
            aria-describedby="<?php echo esc_attr($tipId); ?>"
            aria-expanded="false"
            title="<?php echo esc_attr($text); ?>"
        >?</button>
        <span class="trust-tip" id="<?php echo esc_attr($tipId); ?>" role="tooltip" hidden><?php echo esc_html($text); ?></span>
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

        // Custom badges: positive attachment IDs that resolve to images.
        $custom = [];
        if (isset($raw['custom_badges']) && is_array($raw['custom_badges'])) {
            foreach ($raw['custom_badges'] as $id) {
                $id = absint($id);
                if ($id > 0 && wp_attachment_is_image($id) && ! in_array($id, $custom, true)) {
                    $custom[] = $id;
                }
            }
        }

        $heading = isset($raw['heading']) ? sanitize_text_field((string) $raw['heading']) : '';

        $position = isset($raw['product_position']) ? sanitize_key((string) $raw['product_position']) : 'after_add_to_cart';
        if (! in_array($position, ['after_add_to_cart', 'before_add_to_cart', 'after_summary'], true)) {
            $position = 'after_add_to_cart';
        }

        $alignment = isset($raw['alignment']) ? sanitize_key((string) $raw['alignment']) : 'left';
        if (! in_array($alignment, ['left', 'center', 'right'], true)) {
            $alignment = 'left';
        }

        $size = isset($raw['size']) ? sanitize_key((string) $raw['size']) : 'medium';
        if (! in_array($size, ['small', 'medium', 'large'], true)) {
            $size = 'medium';
        }

        $color = isset($raw['icon_color']) ? sanitize_hex_color((string) $raw['icon_color']) : null;
        if (! is_string($color) || $color === '') {
            $color = (string) ($defaults['icon_color'] ?? '#3c4858');
        }

        $sanitized = array_merge($defaults, [
            'enabled'          => ! empty($raw['enabled']),
            'heading'          => $heading,
            'badges'           => $badges,
            'custom_badges'    => $custom,
            'show_on_product'  => ! empty($raw['show_on_product']),
            'product_position' => $position,
            'show_on_cart'     => ! empty($raw['show_on_cart']),
            'show_on_checkout' => ! empty($raw['show_on_checkout']),
            'alignment'        => $alignment,
            'size'             => $size,
            'icon_color'       => $color,
            'show_labels'      => ! empty($raw['show_labels']),
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
