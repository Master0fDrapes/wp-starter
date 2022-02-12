<?php

/**
 * Version Pro Base functionalities
 *
 * Name: Duplicator PRO base
 * Version: 1
 * Author: Snap Creek
 * Author URI: http://snapcreek.com
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Addons\ProBase;

defined('ABSPATH') || exit;

require_once __DIR__ . '/Vendor/edd/Duplicator_EDD_SL_Plugin_Updater.php';

use Duplicator\Controllers\SchedulePageController;
use Duplicator\Addons\ProBase\License\License;
use Duplicator\Addons\ProBase\License\Notices;

class ProBase extends \Duplicator\Core\Addons\AbstractAddonCore
{

    public function init()
    {
        add_action('duplicator_addons_loaded', array($this, 'addonsLoaded'));
        add_action('duplicator_unistall', array($this, 'unistall'));

        add_filter('duplicator_main_menu_label', function () {
            return 'Duplicator Pro';
        });

        add_filter('duplicator_menu_pages', array($this, 'addScheduleMenuField'));

        Notices::init();
        LicensingController::init();
    }

    public function unistall()
    {
        if (strlen(License::getLicenseKey()) > 0) {
            switch (License::changeLicenseActivation(false)) {
                case License::ACTIVATION_RESPONSE_OK:
                    break;
                case License::ACTIVATION_RESPONSE_POST_ERROR:
                    \DUP_PRO_Low_U::errLog("Error deactivate license: ACTIVATION_RESPONSE_POST_ERROR");
                    break;
                case License::ACTIVATION_RESPONSE_INVALID:
                default:
                    \DUP_PRO_Low_U::errLog("Error deactivate license: ACTIVATION_RESPONSE_INVALID");
                    break;
            }
        }
    }

    public function addScheduleMenuField($basicMenuPages)
    {
        $basicMenuPages[] = SchedulePageController::getInstance();
        return $basicMenuPages;
    }

    public function addonsLoaded()
    {
        License::check();
    }

    public function canEnable()
    {
        return true;
    }

    public static function getAddonPath()
    {
        return __DIR__;
    }

    public static function getAddonFile()
    {
        return __FILE__;
    }
}
