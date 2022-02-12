<?php
/**
 *
 * @package templates/default
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$paramsManager = DUPX_Params_Manager::getInstance();
?>
<div class="hdr-sub3">Engine Settings</div>
<?php
$paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_ARCHIVE_ENGINE);
$paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES);
$paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_DB_ENGINE);
