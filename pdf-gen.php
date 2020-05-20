<?php
/**
 * Plugin Name: Wordpress PDF Generator
 * Description: Programmatically Generate PDF Documents, Supports custom variables, templates, REST API
 * Plugin URI:  https://wwww.dornaweb.com
 * Version:     1.0
 * Author:      Dornaweb
 * Author URI:  https://wwww.dornaweb.com
 * License:     GPL
 * Text Domain: wp-pdf-gen
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

if (! defined('WP_PDFGEN_FILE')) {
	define('WP_PDFGEN_FILE', __FILE__);
}

/**
 * Load core packages and the autoloader.
 * The SPL Autoloader needs PHP 5.6.0+ and this plugin won't work on older versions
 */
if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
	require __DIR__ . '/includes/class-autoloader.php';
}

/**
 * Returns the main instance of PDF Gen.
 *
 * @since  1.0
 * @return WP_PDFGEN\App
 */
function wp_pdfgen() {
	return WP_PDFGEN\App::instance();
}

// Global for backwards compatibility.
$GLOBALS['wp_pdfgen'] = wp_pdfgen();
