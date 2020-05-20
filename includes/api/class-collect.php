<?php
/**
 * Rixma REST API Controller
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace WP_PDFGEN\API;

class Collect extends Client
{
    public $path = 'collect';
    public $methods = ['POST'];

    public function __construct() {
        parent::__construct();
    }

    public function post($request) {
        if (! isset($_SESSION['dw_bankid_refid']) || empty($_SESSION['dw_bankid_refid']['ref_id'])) {
            wp_send_json_error([
                'message'   => __('Invalid Request', 'wp-bankid')
            ]);
        }

        $type = ! empty($_SESSION['dw_bankid_refid']['type']) ? $_SESSION['dw_bankid_refid']['type'] : 'login';
        $ref_id = $_SESSION['dw_bankid_refid']['ref_id'];

        $client = new Bankid_Client();
        $response = $client->collect($_SESSION['dw_bankid_refid']['ref_id']);
        $response_body = $this->send_response($response);

        if ($response_body['status'] == 'pending') {
            wp_send_json_success([
                'status'            => 'pending',
                'request'           => $type,
                'request_group'     => in_array($type, ['login', 'register']) ? 'authentication' : 'sign',
                'message'           => wp_banki_id_get_user_notice($response_body['hintCode'], 'pending')
            ]);

        } elseif ($response_body['status'] == 'failed') {
            wp_send_json_error([
                'status'            => 'failed',
                'request'           => $type,
                'request_group'     => in_array($type, ['login', 'register']) ? 'authentication' : 'sign',
                'message'           => wp_banki_id_get_user_notice($response_body['hintCode'], 'failed')
            ]);
        }

        if ($response_body['status'] == 'complete') {
            if (! isset($response_body['completionData']->user)) {
                wp_send_json_error([
                    'message'   => __('Technical Issue, contact support', 'wp-bankid'),
                    'where'     => 'completion_data_user_missing'
                ]);
            }

            // Handle Registration
            if ($type == 'register') {
                do_action('wp_bankid_register_process_data', $request, $response_body);
                if (personal_number_exists($response_body['completionData']->user->personalNumber)) {
                    wp_send_json_error([
                        'message'   => __('User Already signed up', 'wp-bankid'),
                    ]);
                }

                // Create new user
                $args = apply_filters('wp_bankid_create_user_args', [
                    'user_login'    => $response_body['completionData']->user->personalNumber,
                    'first_name'    => $response_body['completionData']->user->givenName,
                    'last_name'     => $response_body['completionData']->user->surname,
                    'display_name'  => $response_body['completionData']->user->name,
                    'role'          => 'staff',
                    'user_pass'     => null
                ]);

                $user_id = wp_insert_user($args);

                if (is_wp_error($user_id)) {
                    wp_send_json_error([
                        'message' => __('Internal error occured, please try again', 'wp-bankid')
                    ]);
                }

                update_user_meta($user_id, 'registered_with_bankid', true);
                update_user_meta($user_id, 'registration_incomplete', true);
                do_action('wp_bankid_user_created', $user_id);
                update_user_meta($user_id, 'personal_number', $response_body['completionData']->user->personalNumber);

                wp_clear_auth_cookie();
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);

                wp_send_json_success([
                    'status'            => 'complete',
                    'request'           => $type,
                    'request_group'     => in_array($type, ['login', 'register']) ? 'authentication' : 'sign',
                    'message'           => __('You have succesfully registered on rixma', 'wp-bankid'),
                ]);


            } elseif ($type == 'login') {
                // Handle Login
                if (! personal_number_exists($response_body['completionData']->user->personalNumber)) {
                    wp_send_json_error([
                        'message'   => __('User not found, You need to first sign up', 'wp-bankid'),
                    ]);

                }

                $user = get_user_by_personal_number($response_body['completionData']->user->personalNumber);
                wp_clear_auth_cookie();
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);

                wp_send_json_success([
                    'status'            => 'complete',
                    'request'           => $type,
                    'request_group'     => in_array($type, ['login', 'register']) ? 'authentication' : 'sign',
                    'message'           => __('You are now logged in', 'wp-bankid'),
                    'redirect'          => get_permalink(dw_option('my_account_page'))
                ]);
            }

        }

        wp_send_json_success($response_body);
    }

    public function permission_post() {
        return true;
    }
}
