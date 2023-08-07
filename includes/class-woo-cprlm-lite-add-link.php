<?php

/**
 * Create, store and send to CRM - a Password Reset Link
 *
 * @link       https://framework.tech
 * @since      1.0.0
 *
 * @package    Woo_CPRLM_Lite
 * @subpackage Woo_CPRLM_Lite/includes
 */

/**
 * Create, store and send to CRM - a Password Reset Link
 *
 * Catching native WP events related to user password - and generating and storing the PRL
 *
 * @package    Woo_CPRLM_Lite
 * @subpackage Woo_CPRLM_Lite/includes
 * @author     Vladimir Eric <vladimir@framework.tech>
 */

class Woo_CPRLM_Lite_add_link
{
    /**
     * Checks all Customers for permanent_password_link
     *
     * @since    0.1.0
     */

    public $id;
    public $resp; // response to pass to caller

    public function __construct()
    {
    }

    /**
     * get or set the current user PRL, store it as Profiel field and push it to CRM
     * the main function!!!
     * 
     * returns PRL, or 'error'
     * 
     * prl_of_current_user(){
     *      ? need_new_PRL => reset_key
     *      gen_link()
     *          cleanURL()
     *      
     *          
     */
    public function prl_of_current_user($id, $force = false, $crm = true)
    {
        // make id class-global
        $this->id = $id;
        $user = get_user_by('ID', $id);

        if (!$user) {
            // user not found in DB
            return array(0, "user not found id: " . $this->id);
        }

        // let's get saved PRL from Profile
        $pass_reset_link = get_user_meta($id, 'permanent_reset_link', true);
        $reset_key = $this->need_new_PRL($id);

        if (!$reset_key && !$force) {

            // skip generating PRL, use the saved one
            $reset_link[0] = $pass_reset_link;
            $email = $user->user_email;

            ve_debug_log("User already had PRL: " . $id . "\r\n# " . $pass_reset_link . " #");
        } else {
            // generate new or recreate from saved fields
            if (is_array($reset_key)) {
                // in case of any error, returned $reset_key is not string (as should be)
                ve_debug_log("Error trying to get reset_key for id: " . $id . "\r\'n" .
                    print_r($reset_key, true));

                return 'error';
            }

            // generate the NEW link
            $reset_link = $this->gen_link($reset_key); // PRL + email
            $pass_reset_link = $reset_link[0];
            $email = $reset_link[1];

            // store the generated PRL
            update_user_meta($id, 'permanent_reset_link', $pass_reset_link);
            // permanent_reset_key is captured by hook 'retrieve_password_key'
            update_user_meta($id, 'permanent_activation_key', $user->user_activation_key);
        }

        // check if error
        if (filter_var($pass_reset_link, FILTER_VALIDATE_URL)) {
            ve_debug_log("PRL was generated or resaved for user id: " . $id . "\r\n" .
                $pass_reset_link);
        } else {
            ve_debug_log("Attempt of updating PRL with improper url: " . $pass_reset_link, "error");
        }

        // push to CRM
        if ($crm) {
            $this->push_PRL_to_CRM($reset_link[0], $email);
        }

        // log: PRL managed (created and saved)
        ve_debug_log("The user " . $id . " PRL was processed \r\n" . $pass_reset_link . "\r\n
        ........................");

        return $pass_reset_link;
    }

    /**
     * check if PRL is valid
     * (if ANY event !changed user_activation_key or it expired)
     * 
     * returns: $reset_key or false
     */
    public function need_new_PRL($id)
    {

        $user = get_user_by('ID', $id);
        if (!$user) {
            // user not found in DB
            ve_debug_log("Cannot create PRL - bad user ID sent: " . $id, "error");

            $this->id = false;
            return false;
        }

        $act_key = $user->user_activation_key;
        $reset_key = get_user_meta($id, 'permanent_reset_key', true);

        $check = check_password_reset_key($reset_key, $user->user_login);
        if (!is_wp_error($check)) {
            // if saved reset_key is valid, generate PRL from it (same as sent to user)
            ve_debug_log("Existing reset_key is OK!" . $reset_key);
            return $reset_key;
        }
        // saved reset_key is invalid!!! Generate a new one!
        ve_debug_log("Existing reset_key returns: " . $check->get_error_message());

        $reset_key = $this->gen_new_reset_key($user);
        ve_debug_log("user had an expired _activation_key. Attempting to generate new reset_key");

        return $reset_key;
    }

    /**
     * generate a new PRL
     */
    private function gen_new_reset_key($user)
    {
        // activation key was empty, so simulate 'lost password' request
        $reset_key = get_password_reset_key($user);

        if (is_wp_error($reset_key)) {
            $error_code = array_key_first($reset_key->errors);
            $error_message = $reset_key->errors[$error_code][0];
            ve_debug_log("generating reset key gave an error!!! User id: " . $this->id, "error");

            return array(0, "problem with _activation_key of id: " . $this->id .  ", " . $error_message);
        }

        return $reset_key;
    }

    /** 
     * create a link (keys available)
     */
    public function gen_link($reset_key)
    {
        $user = get_user_by('ID', $this->id);
        $username = $user->get('user_login');
        $user_locale = get_user_locale($user);
        $email = $user->user_email;

        $reset_link = $this->cleanURL(home_url() . '/my-account/lost-password/?show-reset-form=true&key=' . $reset_key . '&login=' . $username . '&wp_lang=' . $user_locale);

        return array($reset_link, $email);
    }

    /**
     * clean a link
     */
    public function cleanURL($url)
    {
        $url = str_replace(' ', '%20', $url);
        return $url;
    }

    /**
     * push newly created PRM to Zoho CRM Orders of the particular user
     */
    public function push_PRL_to_CRM($reset_link, $email)
    {
        $args = array(
            'timeout'       => 45,/* 
            'header'        => array(
                'X-Make-Execution-Id' => 
            ), */
            'body'          => array(
                'email'         => $email,
                'reset_link'    => $reset_link,
                'website'   => site_url(),
            ),
        );

        // send request to a webhook created within Zoho CRM
        $url = 'https://hook.eu1.make.com/bw1rb8yf7a3qv3zn4xtjp2y3b8vb6qje';

        $res = wp_remote_post($url, $args);
        if (is_wp_error($res)) {
            ve_debug_log("Error pushing to CRM. Cutomer email: " . $args['body']['email']);
        } else {
            $this->resp = $args['body']['email'] . " " . $reset_link . "\r\n " .
                print_r($res['body'], true) . ' ' . print_r($res['response']['code'], true);

            if ($res['response']['code'] == '200') {
                // log success
                ve_debug_log("The user's PRL was pushed to CRM: " . $this->resp);
            } else {
                // log error
                ve_debug_log("There was an error when trying to push PRL to CRM: \r\n" .
                    $this->resp . "\r\n " . print_r($res['headers'], true));
                // to print returned header only: ??!?!?!
                /* ve_debug_log("There was an error when trying to push PRL to CRM! Header: \r\n" .
                $res['http_response']->response->headers->data['x-make-execution-id'][0]); */
                // to the error log, too
                ve_debug_log("There was an error when trying to push PRL to CRM: \r\n" .
                    $this->resp . "\r\n " . print_r($res['headers'], true), "error");
            }
        }
    }
}
