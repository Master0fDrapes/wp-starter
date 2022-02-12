<?php

/**
 * Template view manager
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Core\Views;

final class TplMng
{
    /**
     *
     * @var [self]
     */
    private static $instance    = null;
    private $mainFolder         = '';
    private static $stripSpaces = false;
    private $globalData         = array();

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->mainFolder = DUPLICATOR____PATH . '/template/';
    }

    /**
     *
     * @param bool $strip
     */
    public static function setStripSpaces($strip)
    {
        self::$stripSpaces = (bool) $strip;
    }

    /**
     *
     * @param string $key
     * @param mixed $val
     */
    public function setGlobalValue($key, $val)
    {
        $this->globalData[$key] = $val;
    }

    /**
     *
     * @param array $data
     */
    public function updateGlobalData($data = array())
    {
        $this->globalData = array_merge($this->globalData, (array) $data);
    }

    /**
     *
     * @return array
     */
    public function getGlobalData()
    {
        return $this->globalData;
    }

    /**
     *
     * @param string $key
     */
    public function removeGlobalValue($key)
    {
        if (isset($this->globalData[$key])) {
            unset($this->globalData[$key]);
        }
    }

    /**
     *
     * @param string $slugTpl   // template file is a relative path from root template folder
     * @param array $args    // array key / val where key is the var name in template
     * @param bool $echo    // if false return template in string
     *
     * @return string
     */
    public function render($slugTpl, $args = array(), $echo = true)
    {
        ob_start();
        if (($renderFile = $this->getFileTemplate($slugTpl)) !== false) {
            $templateData = apply_filters(self::getDataHook($slugTpl), array_merge($this->globalData, $args));
            $templateMng  = $this;
            require($renderFile);
        } else {
            echo '<p>FILE TPL NOT FOUND: ' . $slugTpl . '</p>';
        }
        $renderResult = apply_filters(self::getRenderHook($slugTpl), ob_get_clean());

        if (self::$stripSpaces) {
            $renderResult = preg_replace('~>[\n\s]+<~', '><', $renderResult);
        }
        if ($echo) {
            echo $renderResult;
            return '';
        } else {
            return $renderResult;
        }
    }

    public static function tplFileToHookSlug($slugTpl)
    {
        return str_replace(array('\\', '/', '.'), '_', $slugTpl);
    }

    public static function getDataHook($slugTpl)
    {
        return 'duplicator_template_data_' . self::tplFileToHookSlug($slugTpl);
    }

    public static function getRenderHook($slugTpl)
    {
        return 'duplicator_template_render_' . self::tplFileToHookSlug($slugTpl);
    }

    /**
     * acctept html of php extensions. if the file have unknown extension automatic add the php extension
     *
     * @param string $slugTpl
     * @return boolean|string return false if don\'t find the template file
     */
    protected function getFileTemplate($slugTpl)
    {
        $fullPath = $this->mainFolder . $slugTpl . '.php';

        if (file_exists($fullPath)) {
            return $fullPath;
        } else {
            return false;
        }
    }
}
