<?php
/**
 * PDFGEN main class
 *
 * @package WP_PDFGEN
 * @since   1.0
 */

namespace WP_PDFGEN;

defined('ABSPATH') || exit;

/**
 * WP_PDFGEN main class
 */
final class App
{
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
    public $version = '1.0';

    /**
     * Plugin instance.
     *
     * @since 1.0
     * @var null|WP_PDFGEN
     */
    public static $instance = null;

    /**
     * Plugin API.
     *
     * @since 1.0
     * @var WP_PDFGEN\API\API
     */
    public $api = '';

    /**
     * Documents uploads path
     *
     * @var string
     */
    public $uploads_path = '';

    /**
     * Return the plugin instance.
     *
     * @return Dornaweb_Pack
     */
    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Dornaweb_Pack constructor.
     */
    private function __construct() {
        $this->secure_sessions();
        add_action( 'init', [$this, 'i18n'] );

        $this->uploads_path = apply_filters('wp_pdf_gen_uploads_directory', ABSPATH . "wp-content/uploads/pdfs");

        $this->define_constants();
        $this->init();
        $this->includes();
    }

    /**
     * Return uploads directory path
     */
    public function get_uploads_path() {
        return $this->uploads_path;
    }

    /**
     * Make Translatable
     *
     */
    public function i18n() {
        load_plugin_textdomain( 'wp-pdf-gen', false, dirname( plugin_basename( WP_PDFGEN_FILE ) ) . "/languages" );
    }

    /**
     * Make PHP Sessions more secure
     */
    public function secure_sessions() {
        if (session_status() == PHP_SESSION_NONE) {
            ini_set( 'session.use_only_cookies', TRUE );
            ini_set( 'session.use_trans_sid', FALSE );
        }
    }

    /**
     * Include required files
     *
     */
    public function includes() {
        include WP_PDFGEN_ABSPATH . 'includes/functions.php';
    }

    /**
     * Define constant if not already set.
     *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
    }

    /**
     * Define constants
     */
    public function define_constants() {
		$this->define('WP_PDFGEN_ABSPATH', dirname(WP_PDFGEN_FILE) . '/');
		$this->define('WP_PDFGEN_PLUGIN_BASENAME', plugin_basename(WP_PDFGEN_FILE));
		$this->define('WP_PDFGEN_BOOKING_VERSION', $this->version);
		$this->define('WP_PDFGEN_PLUGIN_URL', $this->plugin_url());
		$this->define('WP_PDFGEN_API_TEST_MODE', true);
    }

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit(plugins_url('/', WP_PDFGEN_FILE));
    }

    /**
     * Do initial stuff
     */
    public function init() {
        // Install
        register_activation_hook(WP_PDFGEN_FILE, ['WP_PDFGEN\\Install', 'install']);

        // Post types
        Post_Types::init();

        Admin\Admin::init();

        // Add scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'public_dependencies']);
        add_action('admin_enqueue_scripts', [$this, 'admin_dependencies']);

        // Initiate Required classes
        $this->api = new API\REST_Controller;
    }

    /**
     * Register scripts and styles for public area
     */
    public function public_dependencies() {

    }

    /**
     * Register scripts and styles for admin area
     */
    public function admin_dependencies() {
        wp_enqueue_style('wp-pdfgen', WP_PDFGEN_PLUGIN_URL . '/assets/css/wp-pdfgen.css', [], null);

        wp_register_script('wp-pdfgen', WP_PDFGEN_PLUGIN_URL . '/assets/js/wp-pdfgen.js', ['jquery'], null, true);
        wp_localize_script( 'wp-pdfgen', 'WP_PDFGEN', [
            'api_url'   => API\REST_Controller::get_api_uri(),
            'nonce'     => wp_create_nonce('wp_rest'),
            'ajaxurl'   => admin_url('admin-ajax.php')
        ]);
        wp_enqueue_script('wp-pdfgen');
    }
}
