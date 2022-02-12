<?php
/**
 *
 * @package templates/default
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$paramsManager = DUPX_Params_Manager::getInstance();
?>
<div id="tabs-1">
    <?php
    // dupxTplRender('pages-parts/step1/info-tabs/managed-hosting');
    // dupxTplRender('pages-parts/step1/info-tabs/restore-backup');
    dupxTplRender('pages-parts/step1/info-tabs/overview-description');
    if (!DUPX_InstallerState::isRecoveryMode()) {
        ?>
        <div class="margin-top-1" ></div>
        <?php
        $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE);
        $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_INST_TYPE);
        $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_SUBSITE_ID);
    }

    if (DUPX_InstallerState::isAddSiteOnMultisiteAvaiable()) {
        ?>
        <div id="overwrite-subsite-on-multisite-wrapper" class="<?php echo DUPX_InstallerState::isAddSiteOnMultisite() ? '' : 'no-display'; ?>">
            <div class="hdr-sub3 margin-top-2">Subsites</div>
            <?php
            $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_ID);
            $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_CONTENT_OWNER);
            $paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_NEW_SLUG);
            ?>
        </div>
    <?php } ?>
</div>