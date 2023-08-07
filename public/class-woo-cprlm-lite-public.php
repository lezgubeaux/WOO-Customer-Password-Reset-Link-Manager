<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://framework.tech
 * @since      1.0.0
 *
 * @package    Cprlm_Lite
 * @subpackage Cprlm_Lite/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cprlm_Lite
 * @subpackage Cprlm_Lite/public
 * @author     Vladimir Eric <vladimir@framework.tech>
 */
class Cprlm_Lite_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cprlm_Lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cprlm_Lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woo-cprlm-lite-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cprlm_Lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cprlm_Lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woo-cprlm-lite-public.js', array('jquery'), $this->version, false);
	}

	/**
	 * when WP reset link expiration is checked, set custom expiry period and call 'add_link' (PRL)
	 */

	public function cprlm_light_expiration($ve_user = null, $username = null, $ve_password = null)
	{
		// called without known username or id (like: when opening reset pass page)
		if ($username === null || !isset($username) || $username === '') {
			// set custom exp time of WP reset link
			return CPRLM_LITE_EXP_TIME[CPRLM_LITE_EXP_TIME_DEF];
		}

		// if login form returns error, skip everything
		if (is_wp_error($ve_user)) {
			ve_debug_log("Warning: a user login was attempted by false user!");
			// set custom exp time of WP reset link
			return CPRLM_LITE_EXP_TIME[CPRLM_LITE_EXP_TIME_DEF];
		}

		// do for Customers only
		if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
			$user = get_user_by('email', $username);
		} else {
			$user = get_user_by('slug', $username);
		}

		if (!$user) {

			// user could not be retrieved.
			ve_debug_log("Customer with email/username: " . $username . " could not be found in the DB!", "error");
			// set custom exp time of WP reset link
			return CPRLM_LITE_EXP_TIME[CPRLM_LITE_EXP_TIME_DEF];
		}

		$id = $user->ID;
		$is_customer = $this->user_is_customer($id);
		if (!$is_customer) {
			// log a non-customer user
			ve_debug_log("error: the user is not a Customer (id: " . print_r($id, true) . ") ", "error");

			return;
		} else {
			// create PRL: new, or from saved key - for the current Customer
			$prl = new Woo_CPRLM_Lite_add_link;
			$pass_reset_link = $prl->prl_of_current_user($id, true);

			if ($pass_reset_link == 'error') {
				ve_debug_log("PRL could not be created. Possibly due to a bad user ID sent: " . $id, "error");

				return;
			}

			ve_debug_log("Expiration set + PRL was generated or resaved for user id: " . $id . "\r\n" .
				$pass_reset_link . "\r\n=============================");
		}

		// set custom exp time of WP reset link
		return CPRLM_LITE_EXP_TIME[CPRLM_LITE_EXP_TIME_DEF];
	}

	/**
	 * check if a user is 'customer'
	 */
	public function user_is_customer($user_id)
	{
		if (!is_int($user_id)) {
			// an id must be integer - log the error!
			ve_debug_log("Warning: cannot add PRL when improper ID was passed! ID: " . print_r($user_id, true), "error");

			return false;
		} else {
			$user_meta = get_userdata($user_id);
			if (!$user_meta) {
				// the id is not a registered id
				ve_debug_log("Warning: cannot add PRL when not-registered ID was passed! ID: " . print_r($user_id, true), "error");

				return false;
			}
		}

		$user_roles = $user_meta->roles;

		// get out - if the user has any of the blacklisted roles (admin, editor...)
		if (count(array_intersect($user_roles, CPRLM_LITE_WOO_ROLES)) > 0) {
			return false;
		}
		return true;
	}

	/**
	 * if PRL link used == pass reset attempted, 
	 * reset PRL
	 */
	public function force_PRL_reset($user)
	{
		$id = $user->ID;
		ve_debug_log(" yyyyyyyyyyyyyyyyy reset yyyyyyyyyyyyyyyyy" . $id, "link_used");

		$prl = new Woo_CPRLM_Lite_add_link;
		$pass_reset_link = $prl->prl_of_current_user($id, true); // forces PRL regeneration!

		ve_debug_log("User " . $id . " attempted password reset. New PRL should be generated!\r\n" . $pass_reset_link, "link_used");
	}
	public function force_PRL_reset_by_login($user_login)
	{
		$user = get_user_by('login', $user_login);
		ve_debug_log(" xxxxxxxxxxxxxxxxx check yyyyyyyyyyyyyyyyy" . $user_login, "link_used");
		$this->force_PRL_reset($user);
	}

	public function test_hook()
	{
		ve_debug_log("hook called: check_passwords", "test");
	}
}
