<?php

declare(strict_types=1);

namespace Trust\Badges;

defined('ABSPATH') || exit;

/**
 * Catalogue of bundled trust / payment badges.
 *
 * Each badge is a hand-authored, self-contained inline SVG (no external
 * requests, no tracking, no third-party logos that would breach trademark
 * rules). They are generic, recognisable secure-checkout iconography — a lock,
 * a shield, a money-back guarantee, generic card/wallet shapes, etc. The SVGs
 * use `currentColor` so they inherit the merchant's chosen badge colour.
 *
 * The library is the single source of truth: the admin picker, the front-end
 * renderer and the sanitiser all read the same keys from here.
 */
final class BadgeLibrary
{
    /**
     * Cached badge definitions, keyed by slug.
     *
     * @var array<string, array{label: string, svg: string}>|null
     */
    private static ?array $badges = null;

    /**
     * All bundled badges, keyed by slug.
     *
     * @return array<string, array{label: string, svg: string}>
     */
    public static function all(): array
    {
        if (self::$badges !== null) {
            return self::$badges;
        }

        self::$badges = [
            'secure_checkout' => [
                'label' => __('Secure checkout (lock)', 'trust'),
                'svg'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true"><path d="M6 10V8a6 6 0 1 1 12 0v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><rect x="4" y="10" width="16" height="11" rx="2.2" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="15" r="1.4" fill="currentColor"/><path d="M12 16.4V18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            ],
            'ssl_encrypted' => [
                'label' => __('SSL encrypted', 'trust'),
                'svg'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true"><path d="M12 3 5 6v5c0 4.2 2.9 8.1 7 9 4.1-.9 7-4.8 7-9V6l-7-3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m9 12 2.2 2.2L15 10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            ],
            'money_back' => [
                'label' => __('Money-back guarantee', 'trust'),
                'svg'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M14.5 9.2a3 3 0 0 0-2.5-1.2c-1.6 0-2.6.9-2.6 2 0 2.7 5.4 1.4 5.4 4.1 0 1.2-1.1 2.1-2.8 2.1a3.2 3.2 0 0 1-2.7-1.3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M12 6.2v1.6M12 16.2v1.6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            ],
            'verified' => [
                'label' => __('Verified / trusted store', 'trust'),
                'svg'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true"><path d="m12 2.6 2.2 1.6 2.7-.2.9 2.6 2.2 1.6-.8 2.6.8 2.6-2.2 1.6-.9 2.6-2.7-.2L12 21.4l-2.2-1.6-2.7.2-.9-2.6-2.2-1.6.8-2.6-.8-2.6 2.2-1.6.9-2.6 2.7.2L12 2.6Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="m9.2 12 2 2 3.6-3.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            ],
            'free_shipping' => [
                'label' => __('Free shipping (truck)', 'trust'),
                'svg'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true"><path d="M2.5 6.5h10v9h-10z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M12.5 9.5h4l3 3v3h-7z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="7" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.6"/><circle cx="16.5" cy="17.5" r="1.8" stroke="currentColor" stroke-width="1.6"/></svg>',
            ],
            'card_payment' => [
                'label' => __('Card payment', 'trust'),
                'svg'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true"><rect x="2.5" y="5.5" width="19" height="13" rx="2.2" stroke="currentColor" stroke-width="1.6"/><path d="M2.5 9.5h19" stroke="currentColor" stroke-width="1.6"/><path d="M6 14.5h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            ],
            'wallet' => [
                'label' => __('Digital wallet', 'trust'),
                'svg'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true"><path d="M4 7.5h13a2 2 0 0 1 2 2V17a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7.5Z" stroke="currentColor" stroke-width="1.6"/><path d="M4 7.5V6a1.5 1.5 0 0 1 1.5-1.5H15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="16" cy="13.2" r="1.3" fill="currentColor"/></svg>',
            ],
            'support' => [
                'label' => __('24/7 support (headset)', 'trust'),
                'svg'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true"><path d="M5 13v-1a7 7 0 0 1 14 0v1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><rect x="3.5" y="12.5" width="3.5" height="6" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="17" y="12.5" width="3.5" height="6" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M19 18.5v.5a3 3 0 0 1-3 3h-2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            ],
            'privacy' => [
                'label' => __('Privacy protected', 'trust'),
                'svg'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true"><path d="M12 3 5 6v5c0 4.2 2.9 8.1 7 9 4.1-.9 7-4.8 7-9V6l-7-3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="11" r="2" stroke="currentColor" stroke-width="1.6"/><path d="M8.8 16c.5-1.6 1.7-2.5 3.2-2.5s2.7.9 3.2 2.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
            ],
            'satisfaction' => [
                'label' => __('Satisfaction (thumbs up)', 'trust'),
                'svg'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true"><path d="M7 10.5 10.5 4c1.1 0 2 .9 2 2v3.5H17a2 2 0 0 1 2 2.3l-1 5A2 2 0 0 1 16 20H7" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><rect x="3.5" y="10.5" width="3.5" height="9.5" rx="1.2" stroke="currentColor" stroke-width="1.6"/></svg>',
            ],
        ];

        /**
         * Filter the bundled badge library.
         *
         * Add-ons can append generic inline SVG badges. The array is keyed by
         * slug and each item must contain a label and trusted SVG markup.
         *
         * @param array<string, array{label: string, svg: string}> $badges Badge definitions.
         */
        return (array) apply_filters('trust/badge_library', self::$badges);
    }

    public static function has(string $slug): bool
    {
        return array_key_exists($slug, self::all());
    }

    /**
     * The inline SVG markup for a badge, or an empty string for unknown slugs.
     */
    public static function svg(string $slug): string
    {
        $badges = self::all();

        return $badges[$slug]['svg'] ?? '';
    }

    /**
     * The human label for a badge, or the raw slug for unknown ones.
     */
    public static function label(string $slug): string
    {
        $badges = self::all();

        return $badges[$slug]['label'] ?? $slug;
    }
}
