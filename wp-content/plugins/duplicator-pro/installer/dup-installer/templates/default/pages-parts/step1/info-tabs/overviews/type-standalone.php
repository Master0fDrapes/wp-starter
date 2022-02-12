<?php
/**
 *
 * @package templates/default
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_STANDALONE)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_STANDALONE;
} else {
    return;
}

$overwriteMode = (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL);
$display       = DUPX_InstallerState::getInstance()->isInstType(DUPX_InstallerState::INSTALL_STANDALONE);
$display       = (!DUPX_InstallerState::isRestoreBackup() && $display);
?>
<div class="overview-description <?php echo $instTypeClass . ($display ? '' : ' no-display'); ?>">
    <h2>Standalone install</h2>
    <p>
        This installation converts the selected subsite into a standalone website.
        <?php if ($overwriteMode) { ?>
            <br>
            By running this installation all the site data will be lost and the current package will be installed.
        </p>
        <p>
            <b>Continuing, it will no longer be possible to go back.</b>
        <?php } ?>
    </p>
</div>