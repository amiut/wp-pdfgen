<?php
    // dumpit($pdf_doc->get_variables());
?>
<h3><?php _e('List of variables for this document', 'wp-pdfgen'); ?></h3>
<table class="pdf-document-variables">
    <?php foreach ($pdf_doc->get_available_variables() as $idx => $variable_name): ?>
        <tr>
            <th>
                <label for="pdf_variable_<?php echo $idx; ?>"><?php echo $variable_name; ?> :</label>
            </th>

            <td>
                <input value="<?php echo $pdf_doc->get_variable($variable_name); ?>" type="text" name="pdf_variables[<?php echo $variable_name; ?>]" id="pdf_variable_<?php echo $idx; ?>">
            </td>
        </tr>
    <?php endforeach; ?>
</table>
