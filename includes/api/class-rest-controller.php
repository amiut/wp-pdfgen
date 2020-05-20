<?php
/**
 * Register REST API Routes
 *
 * @package WP_PDFGEN
 * @since   1.0
 */

namespace WP_PDFGEN\API;

defined('ABSPATH') || exit;

class REST_Controller
{
    public $controllers = [];
    public static $base = 'bankid/v5';

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->rest_init();

        $this->controllers['Auth'] = new Auth;
        // $this->controllers['Sign'] = new Sign;
        $this->controllers['Collect'] = new Collect;
        $this->controllers['Cancel'] = new Cancel;
        $this->controllers['Complete_Registration'] = new Complete_Registration;
    }

    public static function get_api_uri($path = '/') {
        return get_rest_url(null, self::$base . $path);
    }

    public function rest_init() {
        add_action('rest_api_init', [$this, 'register_rest_routes'], 10);
    }

    public function register_rest_routes() {
        foreach ($this->controllers as $className => $api_obj) {
            $api_obj->register_routes();
        }
    }
}
