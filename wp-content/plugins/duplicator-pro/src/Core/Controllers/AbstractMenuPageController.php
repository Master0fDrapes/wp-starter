<?php

/**
 * Abstract class that manages a menu page and sub-menus.
 * Rendering the page automatically generates the page wrapper and level 2 and 3 minuses.
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Core\Controllers;

use Duplicator\Core\Views\TplMng;

abstract class AbstractMenuPageController extends AbstractSinglePageController implements ControllerInterface
{
    protected $parentSlug = '';
    protected $menuItem   = true;
    protected $menuLabel  = '';
    protected $iconUrl    = '';
    protected $menuPos    = null;
    protected $subMenus   = array();

    /**
     * set template data function.
     */
    protected function setTemplateData()
    {
        parent::setTemplateData();

        $ctrMng = ControllersManager::getInstance();
        $tplMng = TplMng::getInstance();

        $currentMenuSlugs = $this->getCurrentMenuSlugs();

        $menuItemsL2 = $this->getSubMenuItems('');
        for ($i = 0; $i < count($menuItemsL2); $i++) {
            $menuItemsL2[$i]['link']   = $ctrMng->getMenuLink($this->pageSlug, $menuItemsL2[$i]['slug']);
            $menuItemsL2[$i]['active'] = ($menuItemsL2[$i]['slug'] === $currentMenuSlugs[1]);
        }
        $tplMng->setGlobalValue('menuItemsL2', $menuItemsL2);

        $menuItemsL3 = $this->getSubMenuItems($currentMenuSlugs[1]);
        for ($i = 0; $i < count($menuItemsL3); $i++) {
            $menuItemsL3[$i]['link']   = $ctrMng->getMenuLink($this->pageSlug, $currentMenuSlugs[1], $menuItemsL3[$i]['slug']);
            $menuItemsL3[$i]['active'] = ($menuItemsL3[$i]['slug'] === $currentMenuSlugs[2]);
        }
        $tplMng->setGlobalValue('menuItemsL3', $menuItemsL3);
    }

    /**
     * render page
     */
    public function render()
    {
        TplMng::setStripSpaces(true);
        $tplMng = TplMng::getInstance();
        $tplMng->render('page/page_header');
        $tplMng->render('parts/messages');
        $tplMng->render('parts/tabs_menu_l2');
        $tplMng->render('parts/tabs_menu_l3');
        do_action('duplicator_render_page_content_' . $this->pageSlug, $this->getCurrentMenuSlugs());
        $tplMng->render('page/page_footer');
    }

    public function getPosition()
    {
        return $this->menuPos;
    }

    /**
     * register admin page
     *
     * @return string
     */
    public function registerMenu()
    {
        if (strlen($this->menuLabel) == 0) {
            return;
        }

        if (!$this->isEnabled()) {
            return;
        }

        $pageTitle = apply_filters('duplicator_page_title_' . $this->pageSlug, $this->pageTitle);
        $menuLabel = apply_filters('duplicator_menu_label_' . $this->pageSlug, $this->menuLabel);

        if (strlen($this->parentSlug) > 0) {
            $this->menuHookSuffix = add_submenu_page($this->parentSlug, $pageTitle, $menuLabel, $this->capatibility, $this->pageSlug, array($this, 'run'), $this->menuPos);
        } else {
            $this->menuHookSuffix = add_menu_page($pageTitle, $menuLabel, $this->capatibility, $this->pageSlug, array($this, 'run'), $this->iconUrl, $this->menuPos);
        }
    }

    /**
     *
     * @return boolean
     */
    public function isMainPage()
    {
        return (strlen($this->parentSlug) === 0);
    }

    /**
     *
     * @param string $parent
     */
    protected function getSubMenuItems($parent = '')
    {
        $subMenus = apply_filters('duplicator_sub_menu_items_' . $this->pageSlug, array());

        $result = array_filter($subMenus, function ($item) use ($parent) {
            return $item['parent'] === $parent;
        });

        uksort($result, function ($a, $b) use ($result) {
            if ($result[$a]['position'] == $result[$b]['position']) {
                if ($a == $b) {
                    return 0;
                } elseif ($a > $b) {
                    return 1;
                } else {
                    return -1;
                }
            } elseif ($result[$a]['position'] > $result[$b]['position']) {
                return 1;
            } else {
                return -1;
            }
        });

        return array_values($result);
    }

    /**
     * return current slugs.
     *
     * @return string[]
     */
    public function getCurrentMenuSlugs()
    {
        $levels = ControllersManager::getMenuLevels();

        $result    = array();
        $result[0] = $levels[ControllersManager::QUERY_STRING_MENU_KEY_L1];
        if (($result[1] = $levels[ControllersManager::QUERY_STRING_MENU_KEY_L2]) === null) {
            $result[1] = $this->getDefaultSubMenuSlug('');
        } elseif (!$this->slugExists($result[1], '')) {
            $result[1] = $this->getDefaultSubMenuSlug('');
        }

        if (($result[2] = $levels[ControllersManager::QUERY_STRING_MENU_KEY_L3]) === null) {
            $result[2] = $this->getDefaultSubMenuSlug($result[1]);
        } elseif (!$this->slugExists($result[2], $result[1])) {
            $result[2] = $this->getDefaultSubMenuSlug($result[1]);
        }

        return $result;
    }

    /**
     *
     * @param string $parent
     * @return string[]
     */
    protected function getSubMenuSlugs($parent = '')
    {
        $result = array();
        foreach ($this->getSubMenuItems($parent) as $item) {
            $result[] = $item['slug'];
        }
        return $result;
    }

    /**
     *
     * @param string $slug
     * @param string $parent
     * @return boolean
     */
    protected function slugExists($slug, $parent = '')
    {
        if ($slug === false || strlen($slug) === 0) {
            return false;
        }

        return in_array($slug, $this->getSubMenuSlugs($parent));
    }

    /**
     *
     * @param string $parent
     * @return boolean|string
     */
    protected function getDefaultSubMenuSlug($parent = '')
    {
        $slug = apply_filters('duplicator_sub_level_default_tab_' . $this->pageSlug, false, $parent);

        if ($slug === false || strlen($slug) === 0 || !$this->slugExists($slug, $parent)) {
            $slugs = $this->getSubMenuSlugs($parent);
            return (count($slugs) === 0) ? false : $slugs[0];
        }

        return $slug;
    }

    /**
     * return sub lebel menus
     *
     * @return array[]
     */
    public function getSubLevelsTabs()
    {
        return apply_filters('duplicator_sub_level_tabs', array(), $this->pageSlug);
    }

    /**
     *
     * @param string $slug
     * @param string $label
     * @param string $parent
     * @param string $perms
     * @param int $position
     * @return array
     * @throws Exception
     */
    public static function generateSubMenuItem($slug, $label = '', $parent = '', $perms = true, $position = 10)
    {
        if (strlen($slug) === 0) {
            throw new Exception('sub menu slug can\'t be empty');
        }

        if (strlen($label) === 0) {
            $label = $slug;
        }

        return array(
            'slug'     => $slug,
            'label'    => $label,
            'parent'   => $parent,
            'perms'    => $perms,
            'position' => $position,
            'link'     => '',
            'active'   => false
        );
    }
}
