<?php
/**
 * Admin Controller
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace WP_PDFGEN\Admin;

class Admin
{
    public static function init()
    {
        add_action('show_user_profile', [__CLASS__, 'additional_fields']);
        add_action('edit_user_profile', [__CLASS__, 'additional_fields']);
        add_action('personal_options_update', [__CLASS__, 'additional_fields_save']);
        add_action('edit_user_profile_update', [__CLASS__, 'additional_fields_save']);
    }

    public static function additional_fields($user) { ?>
        <h3><?php _e("WP-Bankid", "wp-bankid"); ?></h3>

        <table class="form-table">
            <tr>
                <th><label for="personal_number"><?php _e("Personal Number", "wp-bankid"); ?></label></th>
                <td>
                    <input type="text" name="personal_number" id="personal_number" value="<?php echo esc_attr(get_the_author_meta('personal_number', $user->ID)); ?>" class="regular-text" /><br />
                    <span class="description"><?php _e("Please enter your Personalnumber"); ?></span>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function additional_fields_save($user_id) {
        if (! user_can($user_id, 'edit_user')) {
            return;
        }

        update_user_meta($user_id, 'personal_number', $_POST['personal_number']);
    }
}
