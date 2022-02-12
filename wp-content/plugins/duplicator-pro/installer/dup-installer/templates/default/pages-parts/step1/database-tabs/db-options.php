<?php
/**
 *
 * @package templates/default
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$paramsManager = DUPX_Params_Manager::getInstance();
?>
<div class="hdr-sub3">General</div> 
<?php
$paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_BLOGNAME);
$paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_KEEP_TARGET_SITE_USERS);
?>
<div class="hdr-sub3 margin-top-2">Database Settings</div>
<div class="help-target">
    <?php // DUPX_View_Funcs::helpIconLink('step2');  ?>
</div>
<?php
if (DUPX_Custom_Host_Manager::getInstance()->isManaged()) {
    $paramsManager->setFormNote(DUPX_Params_Manager::PARAM_DB_TABLE_PREFIX, 'The table prefix must be set according to the managed hosting where you install the site.');
}
$paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_TABLE_PREFIX);
