<?php
/**
 * PDFGEN PDF Document Class
 *
 * @package WP_PDFGEN
 * @since   1.0
 */

namespace WP_PDFGEN;

use \Mpdf\Mpdf;

defined('ABSPATH') || exit;

class PDF_Document
{
    /**
     * @var int PDF Document id
     */
    public $id = 0;

    /**
     * @var int PDF Template id
     */
    public $template = 0;

    /**
     * PDF File
     * @var Pdf_File
     */
    public $file;

    /**
     * List of key/value variables
     * used in rendering the template
     *
     * @var array
     */
    public $variables = [];

    /**
     * Title
     *
     * @var string
     */
    public $title = '';

    /**
     * CSS Stylesheets
     *
     * @var string
     */
    public $stylesheet = '';

    /**
     * Template html markup
     *
     * @var string
     */
    public $template_markup = '';

    /**
     * Use global styles
     *
     * @var boolean
     */
    public $use_global_styles = '';

    /**
     * Default font family
     * if false, global font family will be used
     *
     * @var string|bool
     */
    public $default_font_family = false;

    /**
     * Constructor
     *
     */
    public function __construct($id = null) {
        $pdf_id = get_post($id);

        if ($pdf_id && get_post_type($pdf_id->ID) == 'pdfgen-doc') {
            $this->id = (int) $pdf_id->ID;
            $this->setup();
            $this->file = new Pdf_File($this);
        }
    }

    /**
     * Set initial things
     */
    public function setup() {
        $this->template = absint(get_post_meta($this->id, 'template', true));
        $this->variables = array_filter((array) get_post_meta($this->id, 'pdf_variables', true));
        $this->title = get_the_title($this->id);
        $this->template_markup = get_post_meta($this->template, 'html_markup', true);
        $this->template_header = get_post_meta($this->template, 'header_template', true);
        $this->template_footer = get_post_meta($this->template, 'footer_template', true);
        $this->stylesheet = get_post_meta($this->template, 'css_styles', true);
        $this->use_global_styles = get_post_meta($this->template, 'use_default_styles', true) != 'no';
        $this->default_font_family = get_post_meta($this->template, 'default_font', true) ?: false;
    }

    /**
     * Get Raw html template
     */
    public function get_template_html() {
        return $this->template_markup;
    }

    /**
     * Get Raw Header template
     */
    public function get_template_header() {
        return $this->template_header;
    }

    /**
     * Get Raw Footer template
     */
    public function get_template_footer() {
        return $this->template_footer;
    }

    /**
     * Get User-defined Stylesheet
     */
    public function get_stylesheet() {
        return $this->stylesheet;
    }

    /**
     * Get User-defined Stylesheet
     */
    public function get_font_family() {
        return $this->default_font_family;
    }

    /**
     * Check if PDF Document exists
     */
    public function exists() {
        return $this->id > 0;
    }

    /**
     * Get array of key/value variables from post meta
     */
    public function get_meta_variables() {
        if (! $this->exists()) return [];
    }

    /**
     * Set Variables
     */
    public function set_variables($vars) {
        foreach ($vars as $key => $value) {
            $this->variables[$key] = $value;
        }

        update_post_meta($this->id, 'pdf_variables', $this->variables);
    }

    /**
     * Get Variables
     */
    public function get_variables() {
        return $this->variables;
    }

    /**
     * Get a single Variable
     */
    public function get_variable($key) {
        return isset($this->variables[$key]) ? $this->variables[$key] : false;
    }

    /**
     * Set a single Variable
     */
    public function set_variable($key, $value) {
        $this->variables[$key] = $value;
        update_post_meta($this->id, 'pdf_variables', $this->variables);
    }

    /**
     * Get PDF Template id
     *
     */
    public function get_pdf_template() {
        return $this->template;
    }

    /**
     * Extract Available Variables based on raw template html
     */
    public function get_available_variables() {
        $variables = [];
        $template = $this->get_template_html();
        $match = preg_match_all('/{{(\s*.\w+\s*)}}/ms', ($this->get_template_html()), $matches);
        $variables = $matches && isset($matches[1]) ? array_map(function($val) {
            return trim(str_replace(['/', '#'], '', $val));
        }, $matches[1]) : [];

        return array_unique($variables);
    }

    /**
     * Rendered Content based on html template and variables
     */
    public function get_rendered_content() {
        $template = $this->get_template_html();

        if (! $template || ! $this->get_pdf_template()) {
            return;
        }

        $m = new \Mustache_Engine;
        $content = $m->render($template, $this->get_variables());
        return $content;
    }

    /**
     * Save PDF file
     */
    public function save_pdf() {
        $this->file->save();
    }
}
