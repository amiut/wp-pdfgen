<?php
/**
 * Useful functions
 *
 */

/**
 * Load a template part file
 *
 * @param string $path - relative path to the file (e.g includes/views/shortcode-login.php)
 * @param array  $args - Array of key=>value variables for passing to the included file
 */
if (! function_exists('wp_pdfgen_template_part')) {
    function wp_pdfgen_template_part($path = '', $args = []) {
        $plugin_path = trailingslashit(WP_PDFGEN_ABSPATH) . 'templates/' . $path . '.php';

        if (file_exists($plugin_path)) {
            extract($args);
            include $plugin_path;
        }
    }
}

/**
 * Get PDF Document
 *
 * @param  int  $id
 * @return WP_PDFGEN\PDF_Document
 */
if (! function_exists('pdfgen_get_document')) {
    function pdfgen_get_document($id) {
        $id = get_post($id);

        if (! $id || ! $id->ID) {
            return false;
        }

        return new \WP_PDFGEN\PDF_Document((int) $id->ID);
    }
}
