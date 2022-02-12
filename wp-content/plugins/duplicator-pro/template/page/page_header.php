<?php

/**
 * Duplicator page header
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

defined("ABSPATH") or die("");

/* Variables */
/* @var $templateMng \Duplicator\Core\Views\TplMng */
/* @var $templateData array */

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/assets/js/javascript.php');
?>
<div class="wrap">
    <?php
    $templateMng->render('page/page_main_title');

