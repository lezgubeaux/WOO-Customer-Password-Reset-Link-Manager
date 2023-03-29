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
 * @package           Woo_Customer_Password_Reset_Link_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       WOO Customer Password Reset Link Manager
 * Plugin URI:        https://woo-customer-password-reset-link-manager.com
 * Description:       When browsing a WOO Order - display a link to the customer's password reset. The link is live-updated on any event related to customer passwords.
 * Version:           1.0.0
 * Author:            Vladimir Eric
 * Author URI:        https://framework.tech
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-customer-password-reset-link-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOO_CUSTOMER_PASSWORD_RESET_LINK_MANAGER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-customer-password-reset-link-manager-activator.php
 */
function activate_woo_customer_password_reset_link_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-customer-password-reset-link-manager-activator.php';
	Woo_Customer_Password_Reset_Link_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-customer-password-reset-link-manager-deactivator.php
 */
function deactivate_woo_customer_password_reset_link_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-customer-password-reset-link-manager-deactivator.php';
	Woo_Customer_Password_Reset_Link_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_customer_password_reset_link_manager' );
register_deactivation_hook( __FILE__, 'deactivate_woo_customer_password_reset_link_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-customer-password-reset-link-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_customer_password_reset_link_manager() {

	$plugin = new Woo_Customer_Password_Reset_Link_Manager();
	$plugin->run();

}
run_woo_customer_password_reset_link_manager();
