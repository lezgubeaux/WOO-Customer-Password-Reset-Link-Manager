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
 * @package    Cprlm_Lite
 * @subpackage Cprlm_Lite/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cprlm_Lite
 * @subpackage Cprlm_Lite/includes
 * @author     Vladimir Eric <vladimir@framework.tech>
 */
class Cprlm_Lite_i18n
{


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'woo-cprlm-lite',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
