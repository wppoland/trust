<?php
/**
 * Boot order: services listed here are resolved from the container and have
 * their registerHooks() called during Plugin::boot(). Each must implement
 * Trust\Contract\HasHooks.
 *
 * @package Trust
 *
 * @return array<class-string>
 */

declare(strict_types=1);

use Trust\Admin\Settings;
use Trust\Service\BadgesService;

defined('ABSPATH') || exit;

return [
    BadgesService::class,
    ...(is_admin() ? [Settings::class] : []),
];
