<?php
/**
 * Trust badge row. CSS-only, no JavaScript, no layout shift.
 *
 * Bundled badges are inline SVGs that inherit the merchant's icon colour via
 * `currentColor`. The SVG strings come from the BadgeLibrary (hand-authored, no
 * user input) so they are printed through an SVG-safe wp_kses() allowlist;
 * everything derived from settings is escaped.
 *
 * @package Trust
 *
 * @var string                      $heading Heading text (may be empty).
 * @var list<array<string, string>> $items   Renderable badge items.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Variables are local to this template include scope, not true globals.

if (empty($items) || ! is_array($items)) {
    return;
}
?>
<div class="trust-badges">
    <?php if (is_string($heading) && $heading !== '') : ?>
        <p class="trust-badges__heading"><?php echo esc_html($heading); ?></p>
    <?php endif; ?>

    <ul class="trust-badges__list" role="list">
        <?php foreach ($items as $item) : ?>
            <?php $label = (string) ($item['label'] ?? ''); ?>
            <li class="trust-badge trust-badge--svg">
                <?php if (! empty($item['svg'])) : ?>
                    <span class="trust-badge__icon" role="img" aria-label="<?php echo esc_attr($label); ?>">
                        <?php
                        /*
                         * Trusted, hand-authored markup from BadgeLibrary (never
                         * user input). wp_kses() with an SVG-safe allowlist keeps
                         * Plugin Check happy while preserving the icons.
                         */
                        echo wp_kses(
                            (string) $item['svg'],
                            [
                                'svg'    => [
                                    'viewbox'     => true,
                                    'fill'        => true,
                                    'xmlns'       => true,
                                    'focusable'   => true,
                                    'aria-hidden' => true,
                                    'role'        => true,
                                    'width'       => true,
                                    'height'      => true,
                                ],
                                'path'   => [
                                    'd'               => true,
                                    'fill'            => true,
                                    'stroke'          => true,
                                    'stroke-width'    => true,
                                    'stroke-linecap'  => true,
                                    'stroke-linejoin' => true,
                                ],
                                'rect'   => [
                                    'x'               => true,
                                    'y'               => true,
                                    'width'           => true,
                                    'height'          => true,
                                    'rx'              => true,
                                    'ry'              => true,
                                    'fill'            => true,
                                    'stroke'          => true,
                                    'stroke-width'    => true,
                                    'stroke-linejoin' => true,
                                ],
                                'circle' => [
                                    'cx'           => true,
                                    'cy'           => true,
                                    'r'            => true,
                                    'fill'         => true,
                                    'stroke'       => true,
                                    'stroke-width' => true,
                                ],
                                'g'      => [
                                    'fill'   => true,
                                    'stroke' => true,
                                ],
                            ]
                        );
                        ?>
                    </span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
