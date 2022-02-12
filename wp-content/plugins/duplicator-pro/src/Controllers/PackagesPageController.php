<?php

/**
 * Packages page page controller
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Controllers;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;

class PackagesPageController extends AbstractMenuPageController
{

    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::PACKAGES_SUBMENU_SLUG;
        $this->pageTitle    = __('Packages', 'duplicator_pro');
        $this->menuLabel    = __('Packages', 'duplicator_pro');
        $this->capatibility = self::getDefaultCapadibily();
        $this->menuPos      = 10;

        add_filter('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
        add_filter('duplicator_page_template_data_' . $this->pageSlug, array($this, 'updatePackagePageTitle'));
    }

    public function updatePackagePageTitle($templateData)
    {

        $_REQUEST['action'] = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'main';
        switch ($_REQUEST['action']) {
            case 'detail':
                $title = $this->getPackageDetailTitle();
                break;
            default:
                $title = $this->getPackageListTitle();
                break;
        }
        $templateData['pageTitle'] = $title;
        return $templateData;
    }

    protected function getPackageDetailTitle()
    {
        $package_id = isset($_REQUEST["id"]) ? sanitize_text_field($_REQUEST["id"]) : 0;
        $package    = \DUP_PRO_Package::get_by_id($package_id);
        if (!is_object($package)) {
            return __('Package Details » Not Found');
        } else {
            return sprintf(__('Package Details » %1$s', 'duplicator-pro'), $package->Name);
        }
    }

    protected function getPackageListTitle()
    {
        $inner_page = isset($_REQUEST['inner_page']) ? sanitize_text_field($_REQUEST['inner_page']) : 'list';
        switch ($inner_page) {
            case 'list':
                $postfix = __('All', 'duplicator-pro');
                break;
            case 'new1':
                $postfix = __('New', 'duplicator-pro');
                break;
            case 'new2':
                $postfix = __('New', 'duplicator-pro');
                break;
        }
        return __('Packages', 'duplicator_pro') . " » " . $postfix;
    }

    public function renderContent($currentLevelSlugs)
    {
        require(DUPLICATOR____PATH . '/views/packages/controller.php');
    }
}
