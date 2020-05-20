<?php
/**
 * API Call client class
 *
 * @package WP_PDFGEN
 * @since   1.0
 */

namespace WP_PDFGEN\API;

defined('ABSPATH') || exit;

abstract class Client
{
    public $base = 'bankid/v5';
    public $path = '';
    public $methods = ['GET'];
    public $override = false;
    public $has_one = false;
    public $one_path = "(?P<id>\d+)";
    public $one_methods;

    public function __construct() {
        $this->base = apply_filters('rixma_rest_api_base', $this->base);
        $this->one_methods = $this->one_methods ?: ['GET'];

        add_action( 'rest_api_init', [$this, 'register_routes']);
        add_action( 'rest_api_init', [$this, 'additional_routes']);
    }

    public static function instance() {
        return new self();
    }

    public function register_routes() {
        $args = [];
        foreach ($this->methods as $method) {
            $args[] = [
                'methods'               => $method,
                'callback'              => [$this, strtolower($method)],
                'permission_callback'   => [$this, 'permission_' . strtolower($method)]
            ];
        }

        register_rest_route($this->base, "/" . $this->path, $args, $this->override);

        if ($this->has_one) {
            $args = [];

            foreach ($this->one_methods as $method) {
                $args[] = [
                    'methods'   => $method,
                    'callback'              => [$this, strtolower($method) . "_one"],
                    // 'permission_callback'   => [$this, 'permission_' . strtolower($method)],
                    'args' => array(
                        'id' => array(
                          'validate_callback' => function($param, $request, $key) {
                            return is_numeric( $param );
                          }
                        ),
                    ),
                ];
            }

            register_rest_route($this->base, "/" . $this->path . "/" . $this->one_path, $args, $this->override);
        }
    }

    public function additional_routes() {}

    public function send_response($response) {
        if ($response instanceof \GuzzleHttp\Psr7\Response) {
            $res = (array) json_decode($response->getBody()->getContents());

            return $res;

        } else {
            wp_send_json_error([
                'type'      => 'http_error',
                'message'   => wp_banki_id_get_user_notice($response['errorCode']),
                'details'   => wp_banki_id_get_user_notice($response['details']),
                'error'     => $response
            ]);
        }
    }
}
