<?php
/**
 * Service wiring. Returns a closure that registers every service in the
 * container. Keep services thin and focused — Trust is pure presentation.
 *
 * @package Trust
 */

declare(strict_types=1);

use Trust\Admin\Settings;
use Trust\Container;
use Trust\Migrator;
use Trust\Service\BadgesService;

defined('ABSPATH') || exit;

return static function (Container $c): void {
    $c->singleton(Migrator::class, static fn (): Migrator => new Migrator());

    // Storefront badge renderer (single product, cart, checkout, shortcode).
    $c->singleton(BadgesService::class, static fn (): BadgesService => new BadgesService());

    // Admin settings (only needed in wp-admin context).
    if (is_admin()) {
        $c->singleton(Settings::class, static fn (): Settings => new Settings());
    }
};
