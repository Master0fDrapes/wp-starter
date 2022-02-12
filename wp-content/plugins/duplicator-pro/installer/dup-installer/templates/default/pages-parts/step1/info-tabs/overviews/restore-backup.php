<?php
/**
 *
 * @package templates/default
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (!DUPX_InstallerState::getInstance()->isInstallerCreatedInThisLocation()) {
    return;
}

$overwriteMode = (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL);
$display       = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE);
?>
<div class="overview-description restore-backup <?php echo $display ? '' : 'no-display'; ?>">
    <h2>Restore backup <?php echo DUPX_InstallerState::installTypeToString(); ?></h2>
    <p>
        The restore backup mode restores the original site by not performing any processing on the database or tables. 
        This ensures that the exact copy of the original site is restored.
        <?php if ($overwriteMode) { ?>
            <br>
            By running this installation all the site data will be lost and the current backup restored.
        </p>
        <p>
            <b>Continuing, it will no longer be possible to go back.</b>
        <?php } ?>
    </p>
</div>