<?php

/**
 * PDFGEN PDF File Class
 *
 * @uses    \Mpdf\Mpdf
 * @package WP_PDFGEN/Mpdf
 * @since   1.0
 */

namespace WP_PDFGEN;
use \Mpdf\Mpdf;
use \Mpdf\Config\ConfigVariables;
use \Mpdf\Config\FontVariables;

defined('ABSPATH') || exit;

class Pdf_File
{
    /**
     * @var WP_PDFGEN\PDF_Document PDF Document
     */
    public $document;

    /**
     * Config
     */
    public $config;

    /**
     * Mpdf Instance
     *
     * @var \Mpdf\Mpdf
     */
    public $mpdf;

    /**
     * HTML content to output
     *
     * @var stringss
     */
    public $content = '';

    /**
     * Class Constructor
     */
    public function __construct($document) {
        if ($document->id) {
            $this->set_document($document);
        }

        $this->default_configs();
        $this->mpdf = new Mpdf($this->config);
    }

    /**
     * Set PDF Document
     */
    public function set_document($document) {
        $this->document = $document;
    }

    /**
     * Default Configs
     */
    public function default_configs() {
        $this->config = apply_filters('wp_pdfgen_default_pdf_configs', [
            'mode' => 'utf-8',
            'format' => 'A4-P',
            'orientation' => 'P'
        ]);

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $this->config['fontDir'] = apply_filters('wp_pdfgen_fonts_directories', array_merge($fontDirs, [
            WP_PDFGEN_ABSPATH . 'includes/fonts',
        ]));

        $this->config['tempDir'] = WP_PDFGEN_ABSPATH . 'tmp';
        $this->config['showImageErrors '] = true;
        $this->config['img_dpi '] = 300;

        $this->config['fontdata'] = $fontData + apply_filters('wp_pdfgen_available_fonts', [
            'roboto' => [
                'R'  => 'Roboto-Regular.ttf',
                'I'  => 'Roboto-Italic.ttf',
                'B'  => 'Roboto-Bold.ttf',
                'BI' => 'Roboto-BoldItalic.ttf',
            ],
            'raleway' => [
                'R'  => 'Raleway-Regular.ttf',
                'I'  => 'Raleway-Italic.ttf',
                'B'  => 'Raleway-Bold.ttf',
                'BI' => 'Raleway-BoldItalic.ttf',
            ],
            'lato' => [
                'R'  => 'Lato-Regular.ttf',
                'I'  => 'Lato-Italic.ttf',
                'B'  => 'Lato-Bold.ttf',
                'BI' => 'Lato-BoldItalic.ttf',
            ],
            'ubuntu' => [
                'R'  => 'Ubuntu-R.ttf',
                'I'  => 'Ubuntu-I.ttf',
                'B'  => 'Ubuntu-B.ttf',
                'BI' => 'Ubuntu-BI.ttf',
            ],
        ]);

        if ($this->document->get_font_family()) {
            $this->config['default_font'] = $this->document->get_font_family();

        } else {
            $this->config['default_font'] = apply_filters('wp_pdfgen_default_font', 'raleway');

        }
    }

    public function set_styles() {

    }

    public function global_stylesheet() {
        $file = WP_PDFGEN_ABSPATH . 'assets/css/pdf-default-styles.css';
        $default_styles = '';
        if (file_exists($file) && is_readable($file)) {
            $default_styles = file_get_contents($file);
        }

        return apply_filters('wp_pdfgen_global_default_styles', $default_styles);
    }

    public function get_file_name() {
        return get_post_field('post_name', $this->document->get_pdf_template()) . '-' . $this->document->id . '.pdf';
    }

    /**
     * Save PDF File
     */
    public function save() {
        $this->mpdf->SetTitle($this->document->title);

        if ($this->document->use_global_styles) {
            $this->mpdf->WriteHTML($this->global_stylesheet(), 1);
        }

        if ($user_styles = $this->document->get_stylesheet()) {
            $this->mpdf->WriteHTML($user_styles, 1);
        }

        if ($header = $this->document->get_template_header()) {
            $this->mpdf->SetHTMLHeader($header);
        }

        if ($footer = $this->document->get_template_footer()) {
            $this->mpdf->SetHTMLFooter($footer);
        }

        $this->mpdf->autoLangToFont = true;
        $this->mpdf->WriteHTML($this->document->get_rendered_content());
        $this->mpdf->Output(wp_pdfgen()->get_uploads_path() . '/'. $this->get_file_name());
    }

    public function md5() {
        $file = wp_pdfgen()->get_uploads_path() . '/'. $this->get_file_name();
        if (file_exists($file)) {
            return hash_file('md5', $file);
        }
    }

    public function sha1() {
        $file = wp_pdfgen()->get_uploads_path() . '/'. $this->get_file_name();
        if (file_exists($file)) {
            return hash_file('sha1', $file);
        }
    }

    public function url() {
        if (file_exists(wp_pdfgen()->get_uploads_path() . '/'. $this->get_file_name())) {
            return str_replace(trailingslashit(ABSPATH), trailingslashit(home_url()), wp_pdfgen()->get_uploads_path() . '/'. $this->get_file_name());
        }
    }
}
