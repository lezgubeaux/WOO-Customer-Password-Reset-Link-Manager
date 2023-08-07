<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://framework.tech
 * @since      1.0.0
 *
 * @package    Cprlm_Lite
 * @subpackage Cprlm_Lite/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Cprlm_Lite
 * @subpackage Cprlm_Lite/includes
 * @author     Vladimir Eric <vladimir@framework.tech>
 */
class Cprlm_Lite
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cprlm_Lite_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('CPRLM_LITE_VERSION')) {
			$this->version = CPRLM_LITE_VERSION;
		} else {
			$this->version = '1.3.1';
		}
		$this->plugin_name = 'woo-cprlm-lite';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cprlm_Lite_Loader. Orchestrates the hooks of the plugin.
	 * - Cprlm_Lite_i18n. Defines internationalization functionality.
	 * - Cprlm_Lite_Admin. Defines all hooks for the admin area.
	 * - Cprlm_Lite_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-cprlm-lite-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-cprlm-lite-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-woo-cprlm-lite-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-woo-cprlm-lite-public.php';

		/**
		 * Create, store and send to CRM - a Password Reset Link
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-cprlm-lite-add-link.php';

		$this->loader = new Cprlm_Lite_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Cprlm_Lite_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Cprlm_Lite_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Cprlm_Lite_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		/**
		 * Action hooks for PRL creation 
		 */
		// on Order creation, create and save PRL to Profile and Order
		// do not push to CRM!
		$this->loader->add_action('woocommerce_checkout_create_order',  $plugin_admin, 'PRL_to_order', 10, 1);

		/**
		 * when reset_key regenerated, grab it to Profile
		 */
		$this->loader->add_action('retrieve_password_key',  $plugin_admin, 'save_reset_key', 10, 2);

		// After an Order is created and saved,  
		// push PRL field from Profile to CRM
		// $this->loader->add_action('woocommerce_checkout_update_order_meta',  $plugin_admin, 'PRL_to_order_and_crm', 10, 2);
		// @@@ removed, as PRL added to order on checkout, and will be pulled by CRM itself

		// Update PRL field on any Order status change
		$this->loader->add_action('woocommerce_order_status_changed',  $plugin_admin, 'PRL_to_order_and_crm', 10, 2);

		// add PRL field to users' metadada
		$this->loader->add_action('show_user_profile', $plugin_admin, 'fwt_profile_fields');
		$this->loader->add_action('edit_user_profile', $plugin_admin, 'fwt_profile_fields');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Cprlm_Lite_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		// when a user attmepts to login, PRL is created
		$this->loader->add_filter('authenticate', $plugin_public, 'cprlm_light_expiration', 10, 3);

		// when using the reset link, generate new PRL
		// woo reset pass form sent new pass to be processed
		$this->loader->add_action('woocommerce_customer_reset_password', $plugin_public, 'force_PRL_reset');
		/* // wp reset pass form sent new pass to be processed
		$this->loader->add_action('password_reset', $plugin_public, 'force_PRL_reset'); */

		// on checking if the WP's reset password link is expired, 
		// extend the link expiration time (to constant defined in the plugin)
		$this->loader->add_filter('password_reset_expiration', $plugin_public, 'cprlm_light_expiration');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Cprlm_Lite_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
