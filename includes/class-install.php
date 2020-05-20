<?php
/**
 * PDFGEN Install class
 *
 * @package WP_PDFGEN
 * @since   1.0
 */

namespace WP_PDFGEN;

defined('ABSPATH') || exit;

class Install
{
    public static function install() {
        if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'wp_pdfgen_installing' ) ) {
			return;
        }

        set_transient( 'wp_pdfgen_installing', 'yes', MINUTE_IN_SECONDS * 10 );
        self::assign_capabilities();

        delete_transient( 'wp_pdfgen_installing' );
        flush_rewrite_rules();
        do_action('wp_pdfgen_installed');

        self::maybe_create_directories();
    }

    /**
     * Asign Capabailities
     */
    public static function assign_capabilities() {
        $capabilities = self::get_capabalities();

        self::assign_cap_group_for_role(
            apply_filters('wp_pdfgen_roles_allowed_for_templates', ['administrator']),
            $capabilities['pdfgen_template']
        );

        self::assign_cap_group_for_role(
            apply_filters('wp_pdfgen_roles_allowed_for_documents', ['administrator']),
            $capabilities['pdfgen_doc']
        );
    }

    /**
     * Assign Group of capabilities to specific role
     */
    public static function assign_cap_group_for_role($roles, $cap_group) {
        global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // @codingStandardsIgnoreLine
        }

        foreach ($roles as $role) {
            foreach ( $cap_group as $cap ) {
                $wp_roles->add_cap( $role, $cap );
            }
        }
    }

    /**
     * Get list of capabalities
     */
    public static function get_capabalities() {
        $capabilities = [];

        $types = ['pdfgen_template', 'pdfgen_doc'];

        foreach ( $types as $type ) {
			$capabilities[ $type ] = array(
				// Post type.
				"edit_{$type}",
				"read_{$type}",
				"delete_{$type}",
				"edit_{$type}s",
				"edit_others_{$type}s",
				"publish_{$type}s",
				"read_private_{$type}s",
				"delete_{$type}s",
				"delete_private_{$type}s",
				"delete_published_{$type}s",
				"delete_others_{$type}s",
				"edit_private_{$type}s",
				"edit_published_{$type}s",

				// Terms.
				"manage_{$type}_terms",
				"edit_{$type}_terms",
				"delete_{$type}_terms",
				"assign_{$type}_terms",
			);
		}

		return $capabilities;
    }

    public static function maybe_create_directories() {
        if (! file_exists(wp_pdfgen()->get_uploads_path())) {
            mkdir(wp_pdfgen()->get_uploads_path(), 0755);
        }
    }
}
