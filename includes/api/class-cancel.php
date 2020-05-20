<?php
/**
 * Rixma REST API Controller
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace WP_PDFGEN\API;

class Cancel extends Client
{
    public $path = 'cancel';
    public $methods = ['POST'];

    public function __construct() {
        parent::__construct();
    }

    public function post($request) {
        if (! isset($_SESSION['dw_bankid_refid'])) {
            wp_send_json_error([
                'message'   => __('Invalid Request', 'wp-bankid')
            ]);
        }

        $client = new Bankid_Client();
        $response = $client->cancel($_SESSION['dw_bankid_refid']['ref_id']);

        wp_send_json_success($this->send_response($response));
    }

    public function permission_post() {
        return true;
    }
}
