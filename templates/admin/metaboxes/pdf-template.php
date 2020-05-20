<div class="wp-pdfgen wp-pdfgen-templates-metabox">
    <div class="form-row">
        <label for="html_markup"><?php _e('HTML Markup', 'wp-pdfgen'); ?></label>
        <textarea name="html_markup" id="html_markup"><?php echo get_post_meta($post->ID, 'html_markup', true); ?></textarea>
    </div><!-- .row -->

    <div class="form-row">
        <label for="css_styles"><?php _e('CSS Styles', 'wp-pdfgen'); ?></label>
        <textarea name="css_styles" id="css_styles"><?php echo get_post_meta($post->ID, 'css_styles', true); ?></textarea>
    </div><!-- .row -->

    <div class="form-row">
        <label for="default_font"><?php _e('Default Font', 'wp-pdfgen'); ?></label>
        <select name="default_font" id="default_font">
            <option value=""><?php _e('Plugin default font', 'wp-pdfgen'); ?></option>
            <option value="roboto" <?php selected(get_post_meta($post->ID, 'default_font', true), 'roboto'); ?>><?php _e('Roboto', 'wp-pdfgen'); ?></option>
            <option value="raleway" <?php selected(get_post_meta($post->ID, 'default_font', true), 'raleway'); ?>><?php _e('Raleway', 'wp-pdfgen'); ?></option>
            <option value="lato" <?php selected(get_post_meta($post->ID, 'default_font', true), 'lato'); ?>><?php _e('Lato', 'wp-pdfgen'); ?></option>
            <option value="ubuntu" <?php selected(get_post_meta($post->ID, 'default_font', true), 'ubuntu'); ?>><?php _e('Ubuntu', 'wp-pdfgen'); ?></option>
        </select>
    </div><!-- .row -->

    <div class="form-row">
        <label><input type="checkbox" name="use_default_styles" value="yes" <?php checked(get_post_meta($post->ID, 'css_styles', true) != 'no'); ?>><?php _e('Use default CSS styles also', 'wp-pdfgen'); ?></label>
    </div><!-- .row -->

    <div class="form-row">
        <label for="header_template"><?php _e('Document header (optional)', 'wp-pdfgen'); ?></label>
        <textarea name="header_template" id="header_template"><?php echo get_post_meta($post->ID, 'header_template', true); ?></textarea>
        <p class="note">
            <?php _e('You can use <code>{nbpg}</code>, <code>{PAGENO}</code>, <code>{DATE j-m-Y}</code> variables', 'wp-pdfgen'); ?>
        </p>
    </div><!-- .row -->

    <div class="form-row">
        <label for="footer_template"><?php _e('Document footer (optional)', 'wp-pdfgen'); ?></label>
        <textarea name="footer_template" id="footer_template"><?php echo get_post_meta($post->ID, 'footer_template', true); ?></textarea>
        <p class="note">
            <?php _e('You can use <code>{nbpg}</code>, <code>{PAGENO}</code>, <code>{DATE j-m-Y}</code> variables', 'wp-pdfgen'); ?>
        </p>
    </div><!-- .row -->

    <?php wp_nonce_field("pdf_temp_{$post->ID}_nonce", 'pdf_templ_nonce' ); ?>

</div><!-- .wp-pdfgen -->
