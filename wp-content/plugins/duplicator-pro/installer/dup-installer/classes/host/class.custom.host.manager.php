<?php

/**
 * custom hosting manager
 * singleton class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\DB
 * @link http://www.php-fig.org/psr/psr-2/
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

require_once (DUPX_INIT . '/classes/host/interface.host.php');
require_once (DUPX_INIT . '/classes/host/class.godaddy.host.php');
require_once (DUPX_INIT . '/classes/host/class.wpengine.host.php');
require_once (DUPX_INIT . '/classes/host/class.wordpresscom.host.php');
require_once (DUPX_INIT . '/classes/host/class.liquidweb.host.php');
require_once (DUPX_INIT . '/classes/host/class.pantheon.host.php');
require_once (DUPX_INIT . '/classes/host/class.flywheel.host.php');

class DUPX_Custom_Host_Manager
{
    const HOST_GODADDY      = 'godaddy';
    const HOST_WPENGINE     = 'wpengine';
    const HOST_WORDPRESSCOM = 'wordpresscom';
    const HOST_LIQUIDWEB    = 'liquidweb';
    const HOST_PANTHEON     = 'pantheon';
    const HOST_FLYWHEEL     = 'flywheel';

    /**
     *
     * @var self
     */
    protected static $instance = null;

    /**
     * this var prevent multiple params inizialization. 
     * it's useful on development to prevent an infinite loop in class constructor
     * 
     * @var bool
     */
    private $initialized = false;

    /**
     * custom hostings list 
     * 
     * @var DUPX_Host_interface[]
     */
    private $customHostings = array();

    /**
     * active custom hosting in current server
     * 
     * @var string[]
     */
    private $activeHostings = array();

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * init custom histings
     */
    private function __construct()
    {
        $this->customHostings[DUPX_WPEngine_Host::getIdentifier()]     = new DUPX_WPEngine_Host();
        $this->customHostings[DUPX_GoDaddy_Host::getIdentifier()]      = new DUPX_GoDaddy_Host();
        $this->customHostings[DUPX_WordpressCom_Host::getIdentifier()] = new DUPX_WordpressCom_Host();
        $this->customHostings[DUPX_Liquidweb_Host::getIdentifier()]    = new DUPX_Liquidweb_Host();
        $this->customHostings[DUPX_Pantheon_Host::getIdentifier()]     = new DUPX_Pantheon_Host();
        $this->customHostings[DUPX_Flywheel_Host::getIdentifier()]     = new DUPX_Flywheel_Host();
    }

    /**
     * execute the active custom hostings inizialization only one time.
     * 
     * @return boolean
     * @throws Exception
     */
    public function init()
    {
        if ($this->initialized) {
            return true;
        }
        foreach ($this->customHostings as $cHost) {
            if (!($cHost instanceof DUPX_Host_interface)) {
                throw new Exception('Host must implemnete DUPX_Host_interface');
            }
            if ($cHost->isHosting()) {
                $this->activeHostings[] = $cHost->getIdentifier();
                $cHost->init();
            }
        }
        $this->initialized = true;
        return true;
    }

    /**
     * return the lisst of current custom active hostings
     * 
     * @return DUPX_Host_interface[]
     */
    public function getActiveHostings()
    {
        $result = array();
        foreach ($this->customHostings as $cHost) {
            if ($cHost->isHosting()) {
                $result[] = $cHost->getIdentifier();
            }
        }
        return $result;
    }

    /**
     * return true if current identifier hostoing is active
     * 
     * @param string $identifier
     * @return bool
     */
    public function isHosting($identifier)
    {
        return isset($this->customHostings[$identifier]) && $this->customHostings[$identifier]->isHosting();
    }

    /**
     * 
     * @return boolean|string return false if isn't managed manage hosting of manager hosting 
     */
    public function isManaged()
    {
        if ($this->isHosting(self::HOST_WPENGINE)) {
            return self::HOST_WPENGINE;
        } else if ($this->isHosting(self::HOST_LIQUIDWEB)) {
            return self::HOST_LIQUIDWEB;
        } else if ($this->isHosting(self::HOST_GODADDY)) {
            return self::HOST_GODADDY;
        } else if ($this->isHosting(self::HOST_WORDPRESSCOM)) {
            return self::HOST_WORDPRESSCOM;
        } else if ($this->isHosting(self::HOST_PANTHEON)) {
            return self::HOST_PANTHEON;
        } else if ($this->isHosting(self::HOST_FLYWHEEL)) {
            return self::HOST_FLYWHEEL;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $identifier
     * @return boolean|DUPX_Host_interface
     */
    public function getHosting($identifier)
    {
        if ($this->isHosting($identifier)) {
            return $this->customHostings[$identifier];
        } else {
            return false;
        }
    }

    /**
     * @todo temp function fot prevent the warnings on managed hosting. 
     * This function must be removed in favor of right extraction mode will'be implemented
     * 
     * @param string $extract_filename
     * @return boolean
     */
    public function skipWarningExtractionForManaged($extract_filename)
    {
        if (!$this->isManaged()) {
            return false;
        } else if (DupProSnapLibUtilWp::isWpCore($extract_filename, DupProSnapLibUtilWp::PATH_RELATIVE)) {
            return true;
        } else if (DUPX_ArchiveConfig::getInstance()->isChildOfArchivePath($extract_filename, array('abs', 'plugins', 'muplugins', 'themes'))) {
            return true;
        } else if (in_array($extract_filename, DUPX_Plugins_Manager::getInstance()->getDropInsPaths())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @return bool
     * @throws Exception
     */
    public function setManagedHostParams()
    {
        if (($managedSlug = $this->isManaged()) === false) {
            return;
        }

        $paramsManager = DUPX_Params_Manager::getInstance();

        $paramsManager->setValue(DUPX_Params_Manager::PARAM_WP_CONFIG, 'nothing');
        $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_WP_CONFIG, DUPX_Param_item_form::STATUS_INFO_ONLY);
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_HTACCESS_CONFIG, 'nothing');
        $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_HTACCESS_CONFIG, DUPX_Param_item_form::STATUS_INFO_ONLY);
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_OTHER_CONFIG, 'nothing');
        $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_OTHER_CONFIG, DUPX_Param_item_form::STATUS_INFO_ONLY);

        $paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_ACTION, 'empty');

        if (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL) {
            $overwriteData = $paramsManager->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_VALIDATION_ACTION_ON_START, DUPX_Validation_manager::ACTION_ON_START_AUTO);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_DISPLAY_OVERWIRE_WARNING, false);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_CPNL_CAN_SELECTED, false);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_HOST, $overwriteData['dbhost']);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_NAME, $overwriteData['dbname']);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_USER, $overwriteData['dbuser']);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_PASS, $overwriteData['dbpass']);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_TABLE_PREFIX, $overwriteData['table_prefix']);
            $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_DB_ACTION, DUPX_Param_item_form::STATUS_INFO_ONLY);
            $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_DB_HOST, DUPX_Param_item_form::STATUS_INFO_ONLY);
            $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_DB_NAME, DUPX_Param_item_form::STATUS_INFO_ONLY);
            $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_DB_USER, DUPX_Param_item_form::STATUS_INFO_ONLY);
            $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_DB_PASS, DUPX_Param_item_form::STATUS_INFO_ONLY);
            $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_DB_TABLE_PREFIX, DUPX_Param_item_form::STATUS_INFO_ONLY);
        }

        $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_URL_NEW, DUPX_Param_item_form::STATUS_INFO_ONLY);
        $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_SITE_URL, DUPX_Param_item_form::STATUS_INFO_ONLY);
        $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_PATH_NEW, DUPX_Param_item_form::STATUS_INFO_ONLY);

        $this->getHosting($managedSlug)->setCustomParams();
    }
}
