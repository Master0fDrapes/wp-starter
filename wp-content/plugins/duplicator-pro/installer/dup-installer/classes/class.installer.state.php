<?php

defined("DUPXABSPATH") or die("");

class DUPX_InstallerState
{
    const MODE_UNKNOWN     = -1;
    const MODE_STD_INSTALL = 0;
    const MODE_OVR_INSTALL = 1;

    /**
     * install types
     */
    const INSTALL_NOT_SET                  = -2;
    const INSTALL_SINGLE_SITE              = -1;
    const INSTALL_STANDALONE               = 0;
    const INSTALL_MULTISITE_SUBDOMAIN      = 2;
    const INSTALL_MULTISITE_SUBFOLDER      = 3;
    const INSTALL_SINGLE_SITE_ON_SUBDOMAIN = 4;
    const INSTALL_SINGLE_SITE_ON_SUBFOLDER = 5;
    const INSTALL_SUBSITE_ON_SUBDOMAIN     = 6;
    const INSTALL_SUBSITE_ON_SUBFOLDER     = 7;
    const SUBSITE_IMPORT_DUP_MIN_VERSION   = '4.0.3.1';
    const SUBSITE_IMPORT_WP_MIN_VERSION    = '4.6';

    /**
     *
     * @var int
     */
    protected $mode = self::MODE_UNKNOWN;

    /**
     *
     * @var string 
     */
    protected $ovr_wp_content_dir = '';

    /**
     *
     * @var self
     */
    private static $instance = null;

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
        
    }

    /**
     * return installer mode
     * 
     * @return int 
     */
    public function getMode()
    {
        return DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_INSTALLER_MODE);
    }

    /**
     * check current installer mode 
     * 
     * @param bool $onlyIfUnknown // check se state only if is unknow state
     * @param bool $saveParams // if true update params
     * @return boolean
     */
    public function checkState($onlyIfUnknown = true, $saveParams = true)
    {
        $paramsManager = DUPX_Params_Manager::getInstance();

        if ($onlyIfUnknown && $paramsManager->getValue(DUPX_Params_Manager::PARAM_INSTALLER_MODE) !== self::MODE_UNKNOWN) {
            return true;
        }
        $isOverwrite = false;
        $nManager    = DUPX_NOTICE_MANAGER::getInstance();
        try {
            if (self::isImportFromBackendMode() || self::isRecoveryMode()) {
                $overwriteData = $this->getOverwriteDataFromParams();
            } else {
                $overwriteData = $this->getOverwriteDataFromWpConfig();
            }

            if (!empty($overwriteData)) {
                if (!DUPX_DB::testConnection($overwriteData['dbhost'], $overwriteData['dbuser'], $overwriteData['dbpass'], $overwriteData['dbname'])) {
                    throw new Exception('wp-config.php exists but database data connection isn\'t valid. Continuing with standard install');
                }

                $isOverwrite = true;

                if (!self::isImportFromBackendMode() && !self::isRecoveryMode()) {
                    //Add additional overwrite data for standard installs
                    $overwriteData['adminUsers'] = $this->getAdminUsersOnOverwriteDatabase($overwriteData);
                    $overwriteData['dupVersion'] = $this->getDuplicatorVersionOverwrite($overwriteData);
                    $overwriteData['wpVersion']  = $this->getWordPressVersionOverwrite();
                }
            }
        } catch (Exception $e) {
            DUPX_Log::logException($e);
            $longMsg = "Exception message: " . $e->getMessage() . "\n\n";
            $nManager->addNextStepNotice(array(
                'shortMsg'    => 'wp-config.php exists but isn\'t valid. Continue on standard install.',
                'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg'     => $longMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE
            ));
            $nManager->saveNotices();
        } catch (Error $e) {
            DUPX_Log::logException($e);
            $longMsg = "Exception message: " . $e->getMessage() . "\n\n";
            $nManager->addNextStepNotice(array(
                'shortMsg'    => 'wp-config.php exists but isn\'t valid. Continue on standard install.',
                'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg'     => $longMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE
            ));
            $nManager->saveNotices();
        }


        if ($isOverwrite) {
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_INSTALLER_MODE, self::MODE_OVR_INSTALL);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA, $overwriteData);
        } else {
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_INSTALLER_MODE, self::MODE_STD_INSTALL);
        }

        if ($saveParams) {
            return $this->save();
        } else {
            return true;
        }
    }

    /**
     * 
     * @return boolean
     */
    public static function isMuImportEnabled()
    {
        $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
        return (is_array($overwriteData) && isset($overwriteData['muImportEnabled'])) ? $overwriteData['muImportEnabled'] : false;
    }

    /**
     * 
     * @param int $type
     * @return string
     * @throws Exception
     */
    public static function installTypeToString($type = null)
    {
        if (is_null($type)) {
            $type = DUPX_InstallerState::getInstType();
        }
        switch ($type) {
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN:
                return 'multisite subdomain';
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER:
                return 'multisite subfolder';
            case DUPX_InstallerState::INSTALL_STANDALONE:
                return 'standalone subsite';
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN:
                return 'subsite on subdomain multisite';
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER:
                return 'subsite on subfolder multisite';
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
                return 'single site';
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
                return 'single site on subdomain multisite';
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
                return 'single site on subfolder multisite';
            case DUPX_InstallerState::INSTALL_NOT_SET:
                return 'NOT SET';
            default:
                throw new Exception('Invalid installer mode');
        }
    }

    protected static function overwriteDataDefault()
    {
        return array(
            'dupVersion'      => '0',
            'wpVersion'       => '0',
            'dbhost'          => '',
            'dbname'          => '',
            'dbuser'          => '',
            'dbpass'          => '',
            'table_prefix'    => '',
            'restUrl'         => '',
            'restNonce'       => '',
            'muImportEnabled' => false,
            'isMultisite'     => false,
            'subdomain'       => false,
            'subsites'        => array(),
            'adminUsers'      => array(),
            'paths'           => array(),
            'urls'            => array()
        );
    }

    protected function getOverwriteDataFromParams()
    {
        $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
        if (empty($overwriteData)) {
            return false;
        }

        if (!isset($overwriteData['dbhost']) || !isset($overwriteData['dbname']) || !isset($overwriteData['dbuser']) || !isset($overwriteData['dbpass'])) {
            return false;
        }

        return array_merge(self::overwriteDataDefault(), $overwriteData);
    }

    protected function getOverwriteDataFromWpConfig()
    {
        if (($wpConfigPath = DUPX_ServerConfig::getWpConfigLocalStoredPath()) === false) {
            $wpConfigPath = DUPX_WPConfig::getWpConfigPath();
            if (!file_exists($wpConfigPath)) {
                $wpConfigPath = DUPX_WPConfig::getWpConfigDeafultPath();
            }
        }

        $overwriteData = false;

        DUPX_Log::info('CHECK STATE INSTALLER WP CONFIG PATH: ' . DUPX_Log::varToString($wpConfigPath), DUPX_Log::LV_DETAILED);

        if (!file_exists($wpConfigPath)) {
            return $overwriteData;
        }

        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        try {
            if (DUPX_WPConfig::getLocalConfigTransformer() === false) {
                throw new Exception('wp-config.php exist but isn\'t valid. continue on standard install');
            }

            $overwriteData = array_merge(self::overwriteDataDefault(), array(
                'dbhost'       => DUPX_WPConfig::getValueFromLocalWpConfig('DB_HOST'),
                'dbname'       => DUPX_WPConfig::getValueFromLocalWpConfig('DB_NAME'),
                'dbuser'       => DUPX_WPConfig::getValueFromLocalWpConfig('DB_USER'),
                'dbpass'       => DUPX_WPConfig::getValueFromLocalWpConfig('DB_PASSWORD'),
                'table_prefix' => DUPX_WPConfig::getValueFromLocalWpConfig('table_prefix', 'variable')
                )
            );

            if (DUPX_WPConfig::getValueFromLocalWpConfig('MULTISITE', 'constant', false)) {
                $overwriteData['isMultisite'] = true;
                $overwriteData['subdomain']   = DUPX_WPConfig::getValueFromLocalWpConfig('SUBDOMAIN_INSTALL', 'constant', false);
            }
        } catch (Exception $e) {
            $overwriteData = false;
            DUPX_Log::logException($e);
            $longMsg       = "Exception message: " . $e->getMessage() . "\n\n";
            $nManager->addNextStepNotice(array(
                'shortMsg'    => 'wp-config.php exists but isn\'t valid. Continue on standard install.',
                'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg'     => $longMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE
            ));
            $nManager->saveNotices();
        } catch (Error $e) {
            $overwriteData = false;
            DUPX_Log::logException($e);
            $longMsg       = "Exception message: " . $e->getMessage() . "\n\n";
            $nManager->addNextStepNotice(array(
                'shortMsg'    => 'wp-config.php exists but isn\'t valid. Continue on standard install.',
                'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg'     => $longMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE
            ));
            $nManager->saveNotices();
        }

        return $overwriteData;
    }

    /**
     * 
     * @return bool
     */
    public static function isRecoveryMode()
    {
        return DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_TEMPLATE) === DUPX_Template::TEMPLATE_RECOVERY;
    }

    /**
     * 
     * @return bool
     */
    public static function isRestoreBackup()
    {
        return DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE);
    }

    /**
     * 
     * @return bool
     */
    public static function isImportFromBackendMode()
    {
        $template = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_TEMPLATE);
        return $template === DUPX_Template::TEMPLATE_IMPORT_BASE || $template === DUPX_Template::TEMPLATE_IMPORT_ADVANCED;
    }

    /**
     * 
     * @return bool
     */
    public static function isAddSiteOnMultisite()
    {
        return self::isInstType(
                array(
                    self::INSTALL_SINGLE_SITE_ON_SUBDOMAIN,
                    self::INSTALL_SINGLE_SITE_ON_SUBFOLDER,
                    self::INSTALL_SUBSITE_ON_SUBDOMAIN,
                    self::INSTALL_SUBSITE_ON_SUBFOLDER
                )
        );
    }

    /**
     * 
     * @param int|array $type
     * @return bool
     */
    public static function instTypeAvaiable($type)
    {
        $acceptList      = DUPX_Params_Descriptor_configs::getInstallTypesAcceptValues();
        $typesToCheck    = is_array($type) ? $type : array($type);
        $typesAvaliables = array_intersect($acceptList, $typesToCheck);
        return (count($typesAvaliables) > 0);
    }

    /**
     * 
     * @return bool
     */
    public static function isAddSiteOnMultisiteAvaiable()
    {
        return self::instTypeAvaiable(
                array(
                    self::INSTALL_SINGLE_SITE_ON_SUBDOMAIN,
                    self::INSTALL_SINGLE_SITE_ON_SUBFOLDER,
                    self::INSTALL_SUBSITE_ON_SUBDOMAIN,
                    self::INSTALL_SUBSITE_ON_SUBFOLDER
                )
        );
    }

    /**
     * this function in case of an error returns an empty array but never generates exceptions
     * 
     * @param string $overwriteData
     * @return array
     */
    protected function getAdminUsersOnOverwriteDatabase($overwriteData)
    {
        $adminUsers = array();
        try {
            $dbFuncs = DUPX_DB_Functions::getInstance();

            if (!$dbFuncs->dbConnection($overwriteData)) {
                DUPX_Log::info('GET USERS ON CURRENT DATABASE FAILED. Can\'t connect');
                return $adminUsers;
            }

            $usersTables = array(
                $dbFuncs->getUserTableName($overwriteData['table_prefix']),
                $dbFuncs->getUserMetaTableName($overwriteData['table_prefix'])
            );

            if (!$dbFuncs->tablesExist($usersTables)) {
                DUPX_Log::info('GET USERS ON CURRENT DATABASE FAILED. Users tables doesn\'t exist, continue with orverwrite installation but with option keep users disabled' . "\n");
                $dbFuncs->closeDbConnection();
                return $adminUsers;
            }

            if (($adminUsers = $dbFuncs->getAdminUsers($overwriteData['table_prefix'])) === false) {
                DUPX_Log::info('GET USERS ON CURRENT DATABASE FAILED. OVERWRITE DB USERS NOT FOUND');
                $dbFuncs->closeDbConnection();
                return $adminUsers;
            }

            $dbFuncs->closeDbConnection();
        } catch (Exception $e) {
            DUPX_Log::logException($e, DUPX_Log::LV_DEFAULT, 'GET ADMIN USER EXECPTION BUT CONTINUE');
        } catch (Error $e) {
            DUPX_Log::logException($e, DUPX_Log::LV_DEFAULT, 'GET ADMIN USER EXECPTION BUT CONTINUE');
        }

        return $adminUsers;
    }

    /**
     * Returns the WP version from the ./wp-includes/version.php file if it exists, otherwise '0'
     *
     * @return string WP version
     */
    protected function getWordPressVersionOverwrite()
    {
        $wp_version = '0';
        try {
            $versionFilePath = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_PATH_WP_CORE_NEW) . "/wp-includes/version.php";
            if (!file_exists($versionFilePath) || !is_readable($versionFilePath)) {
                DUPX_Log::info("WordPress Version file does not exist or is not readable at path: {$versionFilePath}");
                return $wp_version;
            }

            include($versionFilePath);
            return $wp_version;
        } catch (Exception $e) {
            DUPX_Log::logException($e, DUPX_Log::LV_DEFAULT, 'EXCEPTION GETTING WORDPRESS VERSION, BUT CONTINUE');
        } catch (Error $e) {
            DUPX_Log::logException($e, DUPX_Log::LV_DEFAULT, 'ERROR GETTING WORDPRESS VERSION, BUT CONTINUE');
        }

        return $wp_version;
    }

    /**
     * Returns the Duplicator Pro version if it exists, otherwise '0'
     *
     * @param $overwriteData
     * @return string
     */
    protected function getDuplicatorVersionOverwrite($overwriteData)
    {
        $duplicatorProVersion = '0';
        try {
            $dbFuncs = DUPX_DB_Functions::getInstance();

            if (!$dbFuncs->dbConnection($overwriteData)) {
                DUPX_Log::info('GET DUPLICATOR VERSION ON CURRENT DATABASE FAILED. Can\'t connect');
                return $duplicatorProVersion;
            }

            $optionsTable = DUPX_DB_Functions::getOptionsTableName($overwriteData['table_prefix']);

            if (!$dbFuncs->tablesExist($optionsTable)) {
                DUPX_Log::info("GET DUPLICATOR VERSION ON CURRENT DATABASE FAILED. Options tables doesn't exist.\n");
                $dbFuncs->closeDbConnection();
                return $duplicatorProVersion;
            }

            if (($duplicatorProVersion = $dbFuncs->getDuplicatorVersion($overwriteData['table_prefix'])) === false) {
                DUPX_Log::info('GET DUPLICATOR VERSION ON CURRENT DATABASE FAILED. OVERWRITE VERSION NOT FOUND');
                $dbFuncs->closeDbConnection();
                return '0';
            }

            $dbFuncs->closeDbConnection();
        } catch (Exception $e) {
            DUPX_Log::logException($e, DUPX_Log::LV_DEFAULT, 'GET DUPLICATOR VERSION EXECPTION BUT CONTINUE');
        } catch (Error $e) {
            DUPX_Log::logException($e, DUPX_Log::LV_DEFAULT, 'GET DUPLICATOR VERSION ERROR BUT CONTINUE');
        }

        return $duplicatorProVersion;
    }

    /**
     * 
     * if (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL) {
      echo "<span class='dupx-overwrite'>Mode: Overwrite Install {$db_only_txt}</span>";
      } else {
      echo "Mode: Standard Install {$db_only_txt}";
      }
     */
    public function getHtmlModeHeader()
    {
        $php_enforced_txt = ($GLOBALS['DUPX_ENFORCE_PHP_INI']) ? '<i style="color:red"><br/>*PHP ini enforced*</i>' : '';
        $db_only_txt      = ($GLOBALS['DUPX_AC']->exportOnlyDB) ? ' - Database Only' : '';
        $db_only_txt      = $db_only_txt . $php_enforced_txt;

        switch ($this->getMode()) {
            case self::MODE_OVR_INSTALL:
                $label = 'Overwrite Install';
                $class = 'dupx-overwrite mode_overwrite';
                break;
            case self::MODE_STD_INSTALL:
                $label = 'Standard Install';
                $class = 'dupx-overwrite mode_standard';
                break;
            case self::MODE_UNKNOWN:
            default:
                $label = 'Unknown';
                $class = 'mode_unknown';
                break;
        }

        if (strlen($db_only_txt)) {
            return '<span class="' . $class . '">[' . $label . ' ' . $db_only_txt . ']</span>';
        } else {
            return "<span class=\"{$class}\">[{$label}]</span>";
        }
    }

    /**
     * reset current mode
     * 
     * @param boolean $saveParams
     * @return boolean
     */
    public function resetState($saveParams = true)
    {
        $paramsManager = DUPX_Params_Manager::getInstance();
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_INSTALLER_MODE, self::MODE_UNKNOWN);
        if ($saveParams) {
            return $this->save();
        } else {
            return true;
        }
    }

    /**
     * save current installer state
     * 
     * @return bool
     * @throws Exception if fail
     */
    public function save()
    {
        return DUPX_Params_Manager::getInstance()->save();
    }

    /**
     * this function returns true if both the URL and path old and new path are identical
     * 
     * @return bool
     */
    public function isInstallerCreatedInThisLocation()
    {
        $paramsManager = DUPX_Params_Manager::getInstance();
        $originalPaths = DUPX_ArchiveConfig::getInstance()->getRealValue('originalPaths');

        $path_old_original = $originalPaths->home;
        $path_new          = $paramsManager->getValue(DUPX_Params_Manager::PARAM_PATH_NEW);
        $path_old          = $paramsManager->getValue(DUPX_Params_Manager::PARAM_PATH_OLD);
        $url_new           = $paramsManager->getValue(DUPX_Params_Manager::PARAM_URL_NEW);
        $url_old           = $paramsManager->getValue(DUPX_Params_Manager::PARAM_URL_OLD);
        return (($path_new === $path_old || $path_new === $path_old_original) && $url_new === $url_old);
    }

    /**
     * get migration data to store in wp-options
     * 
     * @return array
     */
    public static function getMigrationData()
    {
        $sec           = DUPX_Security::getInstance();
        $paramsManager = DUPX_Params_Manager::getInstance();

        return array(
            'installType'         => $paramsManager->getValue(DUPX_Params_Manager::PARAM_INST_TYPE),
            'restoreBackupMode'   => self::isRestoreBackup(),
            'recoveryMode'        => self::isRecoveryMode(),
            'archivePath'         => $sec->getArchivePath(),
            'packageHash'         => DUPX_Package::getPackageHash(),
            'installerPath'       => $sec->getBootFilePath(),
            'installerBootLog'    => $sec->getBootLogFile(),
            'installerLog'        => $GLOBALS['LOG_FILE_PATH'],
            'dupInstallerPath'    => DUPX_INIT,
            'origFileFolderPath'  => DUPX_Orig_File_Manager::getInstance()->getMainFolder(),
            'safeMode'            => $paramsManager->getValue(DUPX_Params_Manager::PARAM_SAFE_MODE),
            'cleanInstallerFiles' => $paramsManager->getValue(DUPX_Params_Manager::PARAM_AUTO_CLEAN_INSTALLER_FILES)
        );
    }

    /**
     * 
     * @return string
     */
    public static function getAdminLogin()
    {
        $paramsManager = DUPX_Params_Manager::getInstance();
        if (self::isAddSiteOnMultisite()) {
            $overwriteData = $paramsManager->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
            $adminUrl      = $overwriteData['urls']['abs'];
        } else {
            $adminUrl = $paramsManager->getValue(DUPX_Params_Manager::PARAM_SITE_URL);
        }
        return $adminUrl . '/wp-login.php';
    }

    /**
     * 
     * @return int
     */
    public static function getInstType()
    {
        return DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_INST_TYPE);
    }

    /**
     * 
     * @param int|array $type
     * @return bool
     */
    public static function isInstType($type)
    {
        if (is_array($type)) {
            return in_array(self::getInstType(), $type);
        } else {
            return self::getInstType() === $type;
        }
    }
}
