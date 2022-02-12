<?php
/**
 *
 * @package templates/default
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (!DUPX_InstallerState::isRecoveryMode()) {
    return;
}
$overwriteMode = (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL);
$created       = DUPX_ArchiveConfig::getInstance()->created;
$packageLife   = DUPX_ArchiveConfig::getInstance()->getPackageLife();
?>
<div class="overview-description recovery">
    <h2>Recovery mode <?php echo DUPX_InstallerState::installTypeToString(); ?></h2>
    <p>
        This installer is about to overwrite the current data in this site with data from the Recovery Point 
        created on <b><?php echo $created; ?></b> which is <b><?php echo $packageLife; ?> hour(s) old</b>.
        <?php if ($overwriteMode) { ?>
            <br>
            By running this installation all the site data will be lost and the current backup restored.
        </p>
        <p>
            <b>Continuing, it will no longer be possible to go back.</b>
        <?php } ?>
    </p>
</div>