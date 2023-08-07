<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://framework.tech
 * @since      1.0.0
 *
 * @package    Cprlm_Lite
 * @subpackage Cprlm_Lite/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cprlm_Lite
 * @subpackage Cprlm_Lite/admin
 * @author     Vladimir Eric <vladimir@framework.tech>
 */
class Cprlm_Lite_Admin
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
		 * defined in Cprlm_Lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cprlm_Lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woo-cprlm-lite-admin.css', array(), $this->version, 'all');
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
		 * defined in Cprlm_Lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cprlm_Lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woo-cprlm-lite-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * when reset_key regenerated, this means that either new pass was added, or new reset password link created.
	 * capture the reset_key, so you can generate the same reset link, and store it as PRL
	 */
	public function save_reset_key($user_login, $reset_key)
	{
		$user = get_user_by('login', $user_login);
		$id = $user->ID;

		$check_user = new Cprlm_Lite_Public($this->plugin_name, $this->version);
		$check = $check_user->user_is_customer($id);

		if ($check) {
			// save reset_key
			update_user_meta($id, 'permanent_reset_key', $reset_key);
			// restore WP's user_activation_key from user's permanent_activation_key 
			// (since it is deleted when reset link used)
			$act_key = get_user_meta($id, 'permanent_activation_key', true);
			$user->user_activation_key = $act_key;

			// clone PRL from existing WP's reset link
			$prl = new Woo_CPRLM_Lite_add_link;
			$prl->id = $id;
			$reset_link = $prl->gen_link($reset_key);
			update_user_meta($id, 'permanent_reset_link', $reset_link[0]);
			// push to CRM
			$prl->push_PRL_to_CRM($reset_link[0], $reset_link[1]);

			ve_debug_log("Reset link was used, WP generated new reset link, PRL was cloned from it, and pushed to CRM - for the user: " . $id);
		}
	}

	/**
	 * when WOO Order is saved or order status changes, this adds the user's PRL to billing-info of the Order
	 */
	public function PRL_to_order_and_crm($order_id)
	{

		$order = wc_get_order($order_id);
		$id = $order->get_customer_id();

		ve_debug_log("The order: " . $order_id . " of customer: " . $id . " is being saved...");

		// get existing or generate new link for the PRL field (on user's profile)
		$prl = new Woo_CPRLM_Lite_add_link;
		$pass_reset_link = $prl->prl_of_current_user($id);

		if ($pass_reset_link == 'error') {
			ve_debug_log("PRL could not be created. Possibly due to a bad user ID sent: " . $id, "error");

			return;
		}

		// add to the Order
		$order->update_meta_data('pass_reset', $pass_reset_link);
		// log
		ve_debug_log("Customer id: (" . $id . ") got PRL added to Order and CRM\r\n
		==============================================");
	}

	/**
	 * When New Order creation starts, save PRL to Order
	 */
	public function PRL_to_order($order)
	{
		// get current PRL
		$id = $order->get_customer_id();
		ve_debug_log("New order of customer: " . $id . " - starts.");

		// generate new link for the PRL field (on user's profile)
		$prl = new Woo_CPRLM_Lite_add_link;
		$pass_reset_link = $prl->prl_of_current_user($id, false, false);

		if ($pass_reset_link == 'error') {
			ve_debug_log("PRL could not be created. Possibly due to a bad user ID sent: " . $id, "error");

			return;
		}

		// add to the Order
		$order->update_meta_data('pass_reset', $pass_reset_link);
		// log
		ve_debug_log("Customer:" . $id . " got PRL " . $pass_reset_link . " added to the new Order fields\r\n
		==============================================");
	}

	/**
	 * add custom fields to user page: permanent_reset_link
	 *  */
	function fwt_profile_fields($user)
	{
		// get saved PRL from Profile
		$id = $user->ID;

		$prl = get_user_meta($id, 'permanent_reset_link', true);
		$prk = get_user_meta($id, 'permanent_reset_key', true);
		$ak = get_user_meta($id, 'permanent_activation_key', true);

?>
		<h3>Password Reset Link</h3>
<?php
		if (current_user_can('manage_options')) {
			echo $prl . "<br />
				p_reset_key: " . $prk . "<br />
				p_activation: " . $ak . "<br />
				activation: " . $user->user_activation_key;
		} else {
			echo '<em>(hidden)</em>';
		}
	}
}
