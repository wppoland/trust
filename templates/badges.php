<?php
/**
 * Trust badge row. CSS-only, no JavaScript, no layout shift.
 *
 * Bundled badges are inline SVGs that inherit the merchant's icon colour via
 * `currentColor`; custom badges are uploaded images. The SVG strings come from
 * the BadgeLibrary (hand-authored, no user input) so they are printed verbatim;
 * everything derived from settings or attachments is escaped.
 *
 * @package Trust
 *
 * @var string                       $heading     Heading text (may be empty).
 * @var list<array<string, string>>  $items       Renderable badge items.
 * @var string                       $alignment   left|center|right.
 * @var string                       $size        small|medium|large.
 * @var bool                         $show_labels Whether to print each label.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Variables are local to this template include scope, not true globals.

if (empty($items) || ! is_array($items)) {
    return;
}

$alignment   = in_array($alignment, ['left', 'center', 'right'], true) ? $alignment : 'left';
$size        = in_array($size, ['small', 'medium', 'large'], true) ? $size : 'medium';
$show_labels = ! empty($show_labels);

$groupClasses = sprintf(
    'trust-badges trust-badges--align-%s trust-badges--size-%s%s',
    sanitize_html_class($alignment),
    sanitize_html_class($size),
    $show_labels ? ' trust-badges--labelled' : '',
);
?>
<div class="<?php echo esc_attr($groupClasses); ?>">
    <?php if (is_string($heading) && $heading !== '') : ?>
        <p class="trust-badges__heading"><?php echo esc_html($heading); ?></p>
    <?php endif; ?>

    <ul class="trust-badges__list" role="list">
        <?php foreach ($items as $item) : ?>
            <?php
            $type  = $item['type'] ?? '';
            $label = (string) ($item['label'] ?? '');
            ?>
            <li class="trust-badge trust-badge--<?php echo esc_attr(sanitize_html_class($type)); ?>">
                <?php if ($type === 'image' && ! empty($item['url'])) : ?>
                    <img
                        class="trust-badge__image"
                        src="<?php echo esc_url((string) $item['url']); ?>"
                        alt="<?php echo esc_attr($label); ?>"
                        loading="lazy"
                        decoding="async"
                    />
                <?php elseif ($type === 'svg' && ! empty($item['svg'])) : ?>
                    <span class="trust-badge__icon"<?php echo $show_labels ? '' : ' role="img" aria-label="' . esc_attr($label) . '"'; ?>>
                        <?php
                        /*
                         * Trusted, hand-authored markup from BadgeLibrary (never
                         * user input). wp_kses() with an SVG-safe allowlist keeps
                         * Plugin Check happy while preserving the icons.
                         */
                        echo wp_kses(
                            (string) $item['svg'],
                            [
                                'svg'      => [
                                    'viewbox'     => true,
                                    'fill'        => true,
                                    'xmlns'       => true,
                                    'focusable'   => true,
                                    'aria-hidden' => true,
                                    'role'        => true,
                                    'width'       => true,
                                    'height'      => true,
                                ],
                                'path'     => [
                                    'd'                => true,
                                    'fill'             => true,
                                    'stroke'           => true,
                                    'stroke-width'     => true,
                                    'stroke-linecap'   => true,
                                    'stroke-linejoin'  => true,
                                ],
                                'rect'     => [
                                    'x'            => true,
                                    'y'            => true,
                                    'width'        => true,
                                    'height'       => true,
                                    'rx'           => true,
                                    'ry'           => true,
                                    'fill'         => true,
                                    'stroke'       => true,
                                    'stroke-width' => true,
                                    'stroke-linejoin' => true,
                                ],
                                'circle'   => [
                                    'cx'           => true,
                                    'cy'           => true,
                                    'r'            => true,
                                    'fill'         => true,
                                    'stroke'       => true,
                                    'stroke-width' => true,
                                ],
                                'g'        => [
                                    'fill'   => true,
                                    'stroke' => true,
                                ],
                            ]
                        );
                        ?>
                    </span>
                <?php endif; ?>

                <?php if ($show_labels && $label !== '') : ?>
                    <span class="trust-badge__label"><?php echo esc_html($label); ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
