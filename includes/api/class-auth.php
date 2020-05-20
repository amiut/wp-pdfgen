<?php
/**
 * Rixma REST API Controller
 * Note: Once you make this request you need to send a /collect request as soon as possible otherwise its gonna result in failure
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace WP_PDFGEN\API;

class Auth extends Client
{
    public $path = 'auth';
    public $methods = ['POST'];

    public function __construct() {
        parent::__construct();
    }

    public function post($request) {
        $personal_number = ! empty($request->get_param('personalNumber')) ? sanitize_text_field($request->get_param('personalNumber')) : '';
        $register = ! empty($request->get_param('register'));
        $requestURI = esc_url($request->get_param('requestURI'));

        if ($personal_number && ! preg_match(apply_filters('wp_bankid_valid_ssn_regex', '/^(19|20)?(\d{6}([-+]|\s)\d{4}|(?!19|20)\d{10})$/'), $personal_number)) {
            wp_send_json_error([
                'message'   => __('Invalid Personalnumber', 'wp-bankid'),
            ]);
        }

        // Register errors
        if ($register) {
            if ($personal_number && personal_number_exists($personal_number)) {
                wp_send_json_error([
                    'message'   => __('You have already signed up, please login to your account', 'wp-bankid'),
                ]);
            }

        } else {
            // Login Errors
            if ($personal_number && ! personal_number_exists($personal_number)) {
                wp_send_json_error([
                    'message'   => __('User not found', 'wp-bankid'),
                ]);
            }
        }

        $client = new Bankid_Client();
        $response = $client->auth($personal_number);
        $result = $this->send_response($response);

        if (is_array($result)) {
            session_regenerate_id();
            $_SESSION['dw_bankid_refid'] = [
                'ref_id' => $result['orderRef'],
                'type'   => $register ? 'register' : 'login'
            ];

            if (wp_bankid()->device->isMobile()) {
                if (wp_bankid()->device->isiOS()) {
                    $result['mobile_link'] = "https://app.bankid.com/?autostarttoken={$result['autoStartToken']}&redirect={$requestURI}";

                } else {
                    $result['mobile_link'] = "bankid:///?autostarttoken={$result['autoStartToken']}&redirect=null";
                }
            }

            $result['qr'] = apply_filters('wp_bankid_qrcode', 'https://api.qrserver.com/v1/create-qr-code/?size=512x512&data=bankid:///?autostarttoken=' . $result['autoStartToken'], $result['autoStartToken']);

            wp_send_json_success(array_filter($result, function($key) {
                return $key != 'orderRef';
            }, ARRAY_FILTER_USE_KEY));
        }
    }

    public function permission_post() {
        return true;
    }
}
