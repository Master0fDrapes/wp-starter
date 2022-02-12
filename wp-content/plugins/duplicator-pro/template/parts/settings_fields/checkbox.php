<?php

/**
 * Duplicator messages sections
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

defined("ABSPATH") or die("");

/* Variables */
/* @var $templateMng \Duplicator\Core\Views\TplMng */
/* @var $templateData array */
?>
<tr>
    <th scope="row">
        <?php echo esc_html($templateData['fieldLabel']); ?>
    </th>
    <td>
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php echo esc_html($templateData['fieldLabel']); ?></span>
            </legend>
            <label>
                <input 
                    id="<?php echo esc_attr('dup-id-' . $templateData['fieldName']); ?>" 
                    name="<?php echo esc_attr($templateData['fieldName']); ?>" 
                    type="checkbox" 
                    value="1" 
                    <?php checked($templateData['fieldChecked']); ?>
                    >
                    <?php echo esc_html($templateData['fieldCheckboxLabel']); ?>
            </label>
            <?php if (!empty($templateData['fieldDescription'])) { ?>
                <p class="description">
                    <?php echo $templateData['fieldDescription']; ?>
                </p>
            <?php } ?>
        </fieldset>
    </td>
</tr>