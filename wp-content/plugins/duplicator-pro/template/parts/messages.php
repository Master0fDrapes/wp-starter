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
<div class="dup-messages-section" >
    <?php
    if (isset($templateData['errorMessage']) && strlen($templateData['errorMessage']) > 0) {
        DUP_PRO_UI_Notice::displayGeneralAdminNotice(
            $templateData['errorMessage'],
            DUP_PRO_UI_Notice::GEN_ERROR_NOTICE,
            true
        );
    }

    if (isset($templateData['successMessage']) && strlen($templateData['successMessage']) > 0) {
        DUP_PRO_UI_Notice::displayGeneralAdminNotice(
            $templateData['successMessage'],
            DUP_PRO_UI_Notice::GEN_SUCCESS_NOTICE,
            true
        );
    }
    ?>
</div>
<?php
if (false) {
    // for debug
    ?>
    <pre style="font-size: 12px; max-height: 300px; overflow: auto; border: 1px solid black; padding: 10px;"><?php
        var_dump($templateData);
    ?></pre>
    <?php
}