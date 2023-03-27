<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://framework.tech
 * @since      1.0.0
 *
 * @package    Woo_Customer_Password_Reset_Link_Manager
 * @subpackage Woo_Customer_Password_Reset_Link_Manager/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woo_Customer_Password_Reset_Link_Manager
 * @subpackage Woo_Customer_Password_Reset_Link_Manager/includes
 * @author     Vladimir Eric <vladimir@framework.tech>
 */
class Woo_Customer_Password_Reset_Link_Manager_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woo-customer-password-reset-link-manager',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
