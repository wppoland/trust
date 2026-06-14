<?php
/**
 * Uninstall cleanup for Trust.
 *
 * Removes the plugin's own options when it is deleted from wp-admin. Only the
 * options Trust creates are deleted; WooCommerce data is never touched.
 *
 * @package Trust
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('trust_settings');
delete_option('trust_db_version');
