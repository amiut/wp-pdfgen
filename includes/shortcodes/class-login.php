<?php
/**
 * Login form shortcode Class
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace WP_PDFGEN\Shortcodes;

class Login
{
    /**
     * $shortcode_tag
     * holds the name of the shortcode tag
     * @var string
     */
    public static $shortcode_tag = 'bankid-login';

    /**
     * class init will set the needed filter and action hooks
     *
     * @param array $args
     */
    public static function init($args = [])
    {
        //add shortcode
        add_shortcode(self::$shortcode_tag, [__CLASS__, 'shortcode_handler']);
    }

    /**
     * shortcode_handler
     * @param  array  $atts shortcode attributes
     * @param  string $content shortcode content
     * @return string
     */
    public static function shortcode_handler($atts, $content = null)
    {
        // Attributes
        $atts = shortcode_atts(
            [],
            $atts
        );

        ob_start();
        bankid_template_part('shortcodes/login', $atts);
        return ob_get_clean();
    }
}
