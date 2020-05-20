<?php
/**
 * Rixma REST API Controller
 * Note: Once you make this request you need to send a /collect request as soon as possible otherwise its gonna result in failure
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace WP_PDFGEN\API;

class Complete_Registration extends Client
{
    public $path = 'complete-registration';
    public $methods = ['POST'];

    public function __construct() {
        parent::__construct();
    }

    public function post($request) {
        $user_email = ! empty($request->get_param('user_email')) ? sanitize_email($request->get_param('user_email')) : '';
        $user_phone = ! empty($request->get_param('user_phone')) ? sanitize_text_field($request->get_param('user_phone')) : '';

        $user = wp_get_current_user();

        if (! $user_email) {
            wp_send_json_error([
                'message' => __('Please Enter your Email address', 'wp-bankid')
            ]);
        }

        if (! $user_phone) {
            wp_send_json_error([
                'message' => __('Please Enter your Phone number', 'wp-bankid')
            ]);
        }

        if (email_exists($user_phone)) {
            wp_send_json_error([
                'message' => __('A user with this email address already exists', 'wp-bankid')
            ]);
        }

        if (phone_number_exists($user_phone)) {
            wp_send_json_error([
                'message' => __('A user with this phone number already exists', 'wp-bankid')
            ]);
        }

        do_action('wp_bankid_user_profile_before_process_data', $user);

        wp_update_user([
            'ID'         => $user->ID,
            'user_email' => $user_email,
        ]);

        update_user_meta($user->ID, 'user_phone', $user_phone);
        update_user_meta($user->ID, 'registration_incomplete', false);
        delete_user_meta($user->ID, 'registration_incomplete');
        do_action('wp_bankid_user_profile_after_process_data', $user);

        ob_start();
        bankid_template_part('shortcodes/welcome', ['user' => $user]);
        $html = ob_get_clean();

        wp_send_json_success([
            'message' => __('Your registration completed successfully', 'wp-bankid'),
            'html'    => $html
        ]);
    }

    public function permission_post() {
        return is_user_logged_in() && get_user_meta(wp_get_current_user()->ID, 'registration_incomplete', true);
    }
}
