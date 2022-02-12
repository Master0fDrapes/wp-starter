<?php
/**
 *
 * @package templates/default
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$paramsManager = DUPX_Params_Manager::getInstance();
?>
<div class="hdr-sub3">Site Details</div>
<?php
$paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_URL_NEW);
$paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_PATH_NEW);
$paramsManager->getHtmlFormParam(DUPX_Params_Manager::PARAM_ARCHIVE_ACTION);