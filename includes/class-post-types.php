<?php
/**
 * PDFGEN Post Types Class class
 * Post types, Taxonomies, meta boxes, post columns are registered here
 *
 * @package WP_PDFGEN
 * @since   1.0
 */

namespace WP_PDFGEN;

defined('ABSPATH') || exit;

/**
 * Post Types class
 */
class Post_Types{

    public static function init() {
        add_action('init', [__CLASS__, 'register_post_types']);
        add_action('init', [__CLASS__, 'register_taxonomies']);

        // Register Metaboxes
        add_action('add_meta_boxes', [__CLASS__, 'metaboxes']);

        add_action('save_post', [__CLASS__, 'save_pdf_template'], 10, 2);
        add_action('save_post', [__CLASS__, 'save_pdf_document'], 10, 2);

        add_action('edit_form_after_title', [__CLASS__, 'move_metaboxes']);
    }

    public static function register_post_types() {
        // PDF Generated Documents
        register_post_type('pdfgen-doc',
            apply_filters('wp_pdfgen_register_post_type_document_params', [
                'labels'              => array(
                    'name'                  => __( 'PDF Documents', 'wp-pdfgen' ),
                    'singular_name'         => __( 'PDF Document', 'wp-pdfgen' ),
                    'all_items'             => __( 'All Documents', 'wp-pdfgen' ),
                    'menu_name'             => _x( 'PDF Gen', 'Admin menu name', 'wp-pdfgen' ),
                    'add_new'               => __( 'Add New', 'wp-pdfgen' ),
                    'add_new_item'          => __( 'Add new PDF Document', 'wp-pdfgen' ),
                    'edit'                  => __( 'Edit', 'wp-pdfgen' ),
                    'edit_item'             => __( 'Edit Document', 'wp-pdfgen' ),
                    'new_item'              => __( 'New PDF Document', 'wp-pdfgen' ),
                    'view_item'             => __( 'View PDF Document', 'wp-pdfgen' ),
                    'view_items'            => __( 'View PDF Documents', 'wp-pdfgen' ),
                    'search_items'          => __( 'Search PDF Documents', 'wp-pdfgen' ),
                    'not_found'             => __( 'No PDF Documents found', 'wp-pdfgen' ),
                    'not_found_in_trash'    => __( 'No PDF Documents found in trash', 'wp-pdfgen' ),
                    'parent'                => __( 'Parent Document', 'wp-pdfgen' ),
                    'featured_image'        => __( 'Document image', 'wp-pdfgen' ),
                    'set_featured_image'    => __( 'Set Document image', 'wp-pdfgen' ),
                    'remove_featured_image' => __( 'Remove Document image', 'wp-pdfgen' ),
                    'use_featured_image'    => __( 'Use as Document image', 'wp-pdfgen' ),
                    'insert_into_item'      => __( 'Insert into Document', 'wp-pdfgen' ),
                    'uploaded_to_this_item' => __( 'Uploaded to this Document', 'wp-pdfgen' ),
                    'filter_items_list'     => __( 'Filter PDF Documents', 'wp-pdfgen' ),
                    'items_list_navigation' => __( 'PDF Documents navigation', 'wp-pdfgen' ),
                    'items_list'            => __( 'PDF Documents list', 'wp-pdfgen' ),
                ),
                'menu_icon'           => 'dashicons-media-document',
                'description'         => __( 'This is where you can add new pdf Documents', 'wp-pdfgen' ),
                'public'              => false,
                'show_ui'             => true,
                'capability_type'     => 'pdfgen_doc',
                'map_meta_cap'        => true,
                'publicly_queryable'  => false,
                'exclude_from_search' => true,
                'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
                'query_var'           => false,
                'supports'            => ['title'],
                'has_archive'         => false,
                'show_in_nav_menus'   => false,
                'show_in_rest'        => false,
            ]
        ));

        // PDF Templates
        register_post_type('pdfgen-template',
            apply_filters('wp_pdfgen_register_post_type_templates_params', [
                'labels'              => array(
                    'name'                  => __( 'PDF Templates', 'wp-pdfgen' ),
                    'singular_name'         => __( 'Template', 'wp-pdfgen' ),
                    'all_items'             => __( 'PDF Templates', 'wp-pdfgen' ),
                    'menu_name'             => _x( 'PDF Templates', 'Admin menu name', 'wp-pdfgen' ),
                    'add_new'               => __( 'Create new PDF template', 'wp-pdfgen' ),
                    'add_new_item'          => __( 'Add new PDF Template', 'wp-pdfgen' ),
                    'edit'                  => __( 'Edit', 'wp-pdfgen' ),
                    'edit_item'             => __( 'Edit PDF Template', 'wp-pdfgen' ),
                    'new_item'              => __( 'New PDF Template', 'wp-pdfgen' ),
                    'view_item'             => __( 'View PDF Template', 'wp-pdfgen' ),
                    'view_items'            => __( 'View PDF Templates', 'wp-pdfgen' ),
                    'search_items'          => __( 'Search PDF Templates', 'wp-pdfgen' ),
                    'not_found'             => __( 'No PDF Templates found', 'wp-pdfgen' ),
                    'not_found_in_trash'    => __( 'No PDF Templates found in trash', 'wp-pdfgen' ),
                    'parent'                => __( 'Parent Template', 'wp-pdfgen' ),
                    'featured_image'        => __( 'Template image', 'wp-pdfgen' ),
                    'set_featured_image'    => __( 'Set Template image', 'wp-pdfgen' ),
                    'remove_featured_image' => __( 'Remove Template image', 'wp-pdfgen' ),
                    'use_featured_image'    => __( 'Use as Template image', 'wp-pdfgen' ),
                    'insert_into_item'      => __( 'Insert into Template', 'wp-pdfgen' ),
                    'uploaded_to_this_item' => __( 'Uploaded to this Template', 'wp-pdfgen' ),
                    'filter_items_list'     => __( 'Filter Templates', 'wp-pdfgen' ),
                    'items_list_navigation' => __( 'Templates navigation', 'wp-pdfgen' ),
                    'items_list'            => __( 'Templates list', 'wp-pdfgen' ),
                ),
                'description'         => __( 'This is where you can add new pdf templates', 'wp-pdfgen' ),
                'public'              => false,
                'show_ui'             => true,
                'show_in_menu'        => 'edit.php?post_type=pdfgen-doc',
                'capability_type'     => 'pdfgen_template',
                'map_meta_cap'        => true,
                'publicly_queryable'  => false,
                'exclude_from_search' => true,
                'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
                'query_var'           => false,
                'supports'            => ['title'],
                'has_archive'         => false,
                'show_in_nav_menus'   => false,
                'show_in_rest'        => false,
            ]
        ));
    }

    /**
     * Register Metaboxes
     */
    public static function metaboxes() {
        add_meta_box('wp_pdfgen_templates_mb', __('Template Options', 'wp-pdfgen'), function($post, $metabox) {
            wp_pdfgen_template_part('admin/metaboxes/pdf-template', get_defined_vars());
        }, 'pdfgen-template', 'advanced', 'high');

        add_meta_box('wp_pdfgen_documentss_mb', __('PDF Options', 'wp-pdfgen'), function($post, $metabox) {
            wp_pdfgen_template_part('admin/metaboxes/pdf-document', get_defined_vars());
        }, 'pdfgen-doc', 'advanced', 'high');
    }

    public static function move_metaboxes() {
        global $post, $wp_meta_boxes;
        do_meta_boxes(get_current_screen(), 'advanced', $post);
        unset($wp_meta_boxes[get_post_type($post)]['advanced']);
    }

    public static function save_pdf_template($post_id, $post) {
        if (! isset( $_POST['pdf_templ_nonce'] ) || ! wp_verify_nonce( $_POST['pdf_templ_nonce'], "pdf_temp_{$post_id}_nonce" ) ) return;

        /* Get the post type object. */
        $post_type = get_post_type_object( $post->post_type );

        if ($post->post_type != 'pdfgen-template' || ! current_user_can( $post_type->cap->edit_post, $post_id ) ) return;

        update_post_meta($post_id, 'html_markup', $_POST['html_markup']);
        update_post_meta($post_id, 'header_template', $_POST['header_template']);
        update_post_meta($post_id, 'footer_template', $_POST['footer_template']);
        update_post_meta($post_id, 'css_styles', $_POST['css_styles']);
        update_post_meta($post_id, 'default_font', $_POST['default_font']);
        update_post_meta($post_id, 'use_default_styles', isset($_POST['use_default_styles']) ? 'yes' : 'no');
    }


    public static function save_pdf_document($post_id, $post) {
        if (! isset( $_POST['pdf_doc_nonce'] ) || ! wp_verify_nonce( $_POST['pdf_doc_nonce'], "pdf_doc_{$post_id}_nonce" ) ) return;

        /* Get the post type object. */
        $post_type = get_post_type_object( $post->post_type );

        if ($post->post_type != 'pdfgen-doc' || ! current_user_can( $post_type->cap->edit_post, $post_id ) ) return;

        if (isset($_POST['pdf_variables'])) {
            $pdf_variables = array_filter($_POST['pdf_variables'], function($key) {
                return ! empty($key);
            }, ARRAY_FILTER_USE_KEY);

            update_post_meta($post_id, 'pdf_variables', $pdf_variables);
        }

        update_post_meta($post_id, 'template', absint($_POST['template']));

        $pdf_doc = pdfgen_get_document($post_id);
        $pdf_doc->save_pdf();
    }

    public static function register_taxonomies() {

    }

}
