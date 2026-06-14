<?php
/**
 * Constant stubs for PHPStan only.
 *
 * The plugin defines these at runtime in trust.php via define()/const, which
 * static analysis does not execute. Declaring them here lets PHPStan resolve the
 * symbols without affecting the shipped plugin.
 *
 * @package Trust
 */

declare(strict_types=1);

namespace Trust {
    const VERSION     = '0.1.0';
    const PLUGIN_FILE = __FILE__;
}

namespace {
    define('TRUST_DIR', __DIR__ . '/');
    define('TRUST_URL', 'https://example.test/wp-content/plugins/trust/');
}
