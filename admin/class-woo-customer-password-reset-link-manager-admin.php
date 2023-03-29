<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://framework.tech
 * @since      1.0.0
 *
 * @package    Woo_Customer_Password_Reset_Link_Manager
 * @subpackage Woo_Customer_Password_Reset_Link_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Customer_Password_Reset_Link_Manager
 * @subpackage Woo_Customer_Password_Reset_Link_Manager/admin
 * @author     Vladimir Eric <vladimir@framework.tech>
 */
class Woo_Customer_Password_Reset_Link_Manager_Admin
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Customer_Password_Reset_Link_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Customer_Password_Reset_Link_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woo-customer-password-reset-link-manager-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Customer_Password_Reset_Link_Manager_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Customer_Password_Reset_Link_Manager_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woo-customer-password-reset-link-manager-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Add admin menu items
	 *
	 * @since    1.0.0
	 */
	public function addAdminMenuItems()
	{

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in _Loader 
		 *
		 * The _Loader will then create hooks
		 */

		//add_menu_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', string $icon_url = '', int $position = null )
		add_menu_page(
			$this->plugin_name,
			__('Woo Customer Password Reset Link Manager Gateway', 'woo-customer-password-reset-link-manager'),
			'administrator',
			$this->plugin_name . '-info',
			array(
				$this,
				'displayPluginAdminDashboard',
			),
			'dashicons-admin-network',
			20
		);

		/* //add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', int $position = null )
		add_submenu_page(
			$this->plugin_name . '-info',
			'Settings',
			'Settings',
			'administrator',
			$this->plugin_name . '-settings',
			array(
				$this,
				'displayPluginAdminSettings',
			)
		); */
	}

	/**
	 * Add admin menu items
	 *
	 * @since    1.0.0
	 */
	public function displayPluginAdminDashboard()
	{

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in _Loader
		 * The _Loader will then create hooks 
		 */

		require_once 'partials/' . $this->plugin_name . '-admin-display.php';
	}

	// *
	//  * Add admin submenu items
	//  *
	//  * @since    1.0.0

	public function displayPluginAdminSettings()
	{

		// *
		//  * An instance of this class should be passed to the run() function
		//  * defined in _Loader
		//  *
		//  * The _Loader will then create hooks


		// set this var to be used in the settings-display view
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
		if (isset($_GET['error_message'])) {
			add_action('admin_notices', array($this, 'quaifePgSettingsMessages'));
			do_action('admin_notices', $_GET['error_message']);
		}
		require_once 'partials/' . $this->plugin_name . '-admin-settings-display.php';
	}

	/**
	 * Add settings form
	 *
	 * @since    1.0.0
	 */
	public function registerAndBuildFields()
	{
		/**
		 * add_settings_section, add_settings_field, register_setting
		 */
		add_settings_section(
			// ID used to identify this section and with which to register options
			'wcprlm_general_settings',
			// Title to be displayed on the administration page
			'',
			// Callback used to render the description of the section
			array($this, 'wcprlm_display_general_account'),
			// Page on which to add this section of options
			'wcprlm-settings',
		);

		// password link lifetime (hrs)
		unset($args);
		$args = array(
			'type'      => 'input',
			'subtype'   => 'number',
			'id'    => 'wcprlm_expire',
			'name'      => 'wcprlm_expire',
			'required' => 'true',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' => 'option'
		);
		add_settings_field(
			'wcprlm_expire',
			'Reset link expiration period (in hrs):',
			array($this, 'wcprlm_render_settings_field'),
			'wcprlm-settings',
			'wcprlm_general_settings',
			$args
		);
		register_setting(
			'wcprlm-settings',
			'wcprlm_api_url'
		);

		// some hidden field
		unset($args);
		$args = array(
			'type'      => 'input',
			'subtype'   => 'hidden',
			'id'    => 'wcprlm_hidden',
			'name'      => 'wcprlm_hidden',
			'required' => 'true',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' => 'option'
		);
		add_settings_field(
			'wcprlm_hidden',
			'',
			array($this, 'wcprlm_render_settings_field'),
			'wcprlm-settings',
			'wcprlm_general_settings',
			$args
		);
		register_setting(
			'wcprlm-settings',
			'wcprlm_hidden'
		);
	}

	/**
	 * Add settings form
	 *
	 * @since    1.0.0
	 */
	public function wcprlm_display_general_account()
	{
		echo '<p>' . __('Set up the plugin according to your needs.', 'woo-customer-password-reset-link-manager') . '</p>';
	}

	/**
	 * Add settings form
	 *
	 * @since    1.0.0
	 */
	public function wcprlm_render_settings_field($args)
	{
		if ($args['wp_data'] == 'option') {
			$wp_data_value = get_option($args['name']);
		} elseif ($args['wp_data'] == 'post_meta') {
			$wp_data_value = get_post_meta($args['post_id'], $args['name'], true);
		}

		switch ($args['type']) {

			case 'input':
				$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
				if ($args['subtype'] != 'checkbox') {
					$prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">' . $args['prepend_value'] . '</span>' : '';
					$prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
					$step = (isset($args['step'])) ? 'step="' . $args['step'] . '"' : '';
					$min = (isset($args['min'])) ? 'min="' . $args['min'] . '"' : '';
					$max = (isset($args['max'])) ? 'max="' . $args['max'] . '"' : '';
					if (isset($args['disabled'])) {
						// hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
						echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '_disabled" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="' . $args['id'] . '" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="40" value="' . esc_attr($value) . '" />' . $prependEnd;
					} else {
						$msg = "'" . __('This field is mandatory!', 'woo-customer-password-reset-link-manager') . "'";
						echo $prependStart . '
							<input 
								data="default_tag" 
								type="' . $args['subtype'] . '" 
								id="' . $args['id'] . '" ' .
							($args['required'] == 'true' ? 'required' : '') . ' 
								name="' . $args['name'] . '" 
								size="40" 
								value="' . esc_attr($value) .  '" 					
								oninvalid="this.setCustomValidity(' . $msg . ')" 
								oninput="setCustomValidity(\'\')" />' .
							$prependEnd;
					}
					/*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/
				} else {
					$checked = ($value) ? 'checked' : '';
					echo '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" "' . ($args['required'] == 'true' ? 'required' : '') . '" name="' . $args['name'] . '" size="40" value="1" ' . $checked . ' />';
				}
				break;
			default:
				# code...
				break;
		}
	}
}
