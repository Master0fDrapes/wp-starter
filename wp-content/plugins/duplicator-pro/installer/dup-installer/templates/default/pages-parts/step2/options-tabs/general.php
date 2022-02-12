<?php
/**
 *
 * @package templates/default
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$paramsManager = DUPX_Params_Manager::getInstance();
?>
<div class="help-target">
    <?php DUPX_View_Funcs::helpIconLink('step2'); ?>
</div> 
<div  class="dupx-opts">
    <?php
    if ($paramsManager->getValue(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE)) {
        dupxTplRender('parts/restore-backup-mode-notice');
    }
    ?>
    <div class="hdr-sub3">General database options</div>  
    <?php
    $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_CHARSET);
    $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_COLLATE);
    $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_SPACING);
    ?>
    <div class="param-wrapper" >
        <?php
        $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_MYSQL_MODE);
        $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_MYSQL_MODE_OPTS);
        ?>
    </div>
    <?php
    $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_VIEW_CREATION);
    $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_PROC_CREATION);
    $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_FUNC_CREATION);
    $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_SPLIT_CREATES);
    ?>
</div>
