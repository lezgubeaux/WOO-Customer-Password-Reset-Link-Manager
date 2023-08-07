<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://framework.tech
 * @since             1.0.0
 * @package           CPRLM_Lite
 *
 * @wordpress-plugin
 * Plugin Name:       WOO Customer Password Reset Link Manager Lite
 * Plugin URI:        https://woo-customer-password-reset-link-manager-lite.com
 * Description:       Capture and management reset password link generation and expiration events of Customer users in WooCommerce.
 * Version:           1.3.1
 * Author:            Vladimir Eric
 * Author URI:        https://framework.tech
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-cprlm-lite
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('CPRLM_LITE_VERSION', '1.3.1');

/**
 * WooCommerce must be active!
 */
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	return;
}

/**
 * Debugging - If ve_debug mu-plugin is not installed, add function to output debug
 */
if (!function_exists('ve_debug_log')) {
	function ve_debug_log($message, $title = '', $new = false)
	{
		$filename = WP_CONTENT_DIR . '/woo_cprlm_l_debug-' . $title . '.log';

		// empty the log if requested
		if ($new && file_exists($filename)) {
			wp_delete_file($filename);
		}

		error_log("\r\n" . date('m/d/Y h:i:s a', time()) . " v" . CPRLM_LITE_VERSION . "\r\n" .
			$message . "\r\n", 3, $filename);

		return;
	}
}

/**
 * Woo User roles constants (only ones that BLACKLISTED within the plugin)
 * These should NOT be processed
 */
define('CPRLM_LITE_WOO_ROLES', array(
	'administrator', 'editor', 'shop-manager'
));

/**
 * PRL expiration time constants
 */
define('CPRLM_LITE_EXP_TIME', array(
	'1' => DAY_IN_SECONDS,
	'90' => 3 * MONTH_IN_SECONDS,
));
define('CPRLM_LITE_EXP_TIME_DEF', '90');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-cprlm-lite-activator.php
 */
function activate_woo_cprlm_lite()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-woo-cprlm-lite-activator.php';
	Cprlm_Lite_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-cprlm-lite-deactivator.php
 */
function deactivate_woo_cprlm_lite()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-woo-cprlm-lite-deactivator.php';
	Cprlm_Lite_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_woo_cprlm_lite');
register_deactivation_hook(__FILE__, 'deactivate_woo_cprlm_lite');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-woo-cprlm-lite.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_cprlm_lite()
{

	$plugin = new Cprlm_Lite();
	$plugin->run();
}
run_woo_cprlm_lite();
