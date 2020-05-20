<?php
    $pdf_doc = pdfgen_get_document($post->ID);
    // dumpit($pdf_doc->get_rendered_content());
?>
<div class="wp-pdfgen wp-pdfgen-documents-metabox">
    <h3><?php _e('Choose Template', 'wp-pdfgen'); ?></h3>
    <select name="template">
        <option value=""><?php _e('Choose Template', 'wp-pdfgen'); ?></option>

        <?php
            $templates = new WP_Query([
                'showposts' => -1,
                'post_type' => 'pdfgen-template'
            ]);

            foreach ($templates->posts as $template) {
                printf(
                    '<option value="%d" %s>%s</option>',
                    $template->ID, selected(absint($template->ID), $pdf_doc->get_pdf_template(), false),
                    esc_html($template->post_title)
                );
            }

            wp_reset_postdata();
        ?>
    </select>

    <?php wp_nonce_field("pdf_doc_{$post->ID}_nonce", 'pdf_doc_nonce' ); ?>

    <?php
        if ($pdf_doc->get_pdf_template()) {
            wp_pdfgen_template_part('admin/metaboxes/pdf-document-tpl', [
                'pdf_doc' => $pdf_doc,
                'post'    => $post
            ]);
        }
    ?>

</div><!-- .wp-pdfgen -->


