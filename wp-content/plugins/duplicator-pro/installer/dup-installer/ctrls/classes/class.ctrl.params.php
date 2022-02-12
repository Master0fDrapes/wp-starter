<?php

/**
 * Controller params manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * singleton class
 */
final class DUPX_Ctrl_Params
{
    /**
     *
     * @var bool    // this variable becomes false if there was something wrong with the validation but the basic is true
     */
    private static $paramsValidated = true;

    /**
     * returns false if at least one param has not been validated
     * 
     * @return bool 
     */
    public static function isParamsValidated()
    {
        return self::$paramsValidated;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsBase()
    {
        DUPX_LOG::info('CTRL PARAMS BASE', DUPX_Log::LV_DETAILED);
        $paramsManager = DUPX_Params_Manager::getInstance();
        $paramsManager->setValueFromInput(DUPX_Params_Manager::PARAM_CTRL_ACTION, DUPX_Param_item_form::INPUT_REQUEST);
        $paramsManager->setValueFromInput(DUPX_Params_Manager::PARAM_STEP_ACTION, DUPX_Param_item_form::INPUT_REQUEST);
        return true;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsStep0()
    {
        DUPX_LOG::info('CTRL PARAMS S0', DUPX_Log::LV_DETAILED);
        DUPX_Log::info('REQUEST: ' . DUPX_Log::varToString($_REQUEST), DUPX_Log::LV_HARD_DEBUG);
        $paramsManager = DUPX_Params_Manager::getInstance();

        DUPX_ArchiveConfig::getInstance()->setNewPathsAndUrlParamsByMainNew();
        DUPX_Custom_Host_Manager::getInstance()->setManagedHostParams();

        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsStep1()
    {
        DUPX_LOG::info('CTRL PARAMS S1', DUPX_Log::LV_DETAILED);
        DUPX_Log::info('REQUEST: ' . DUPX_Log::varToString($_REQUEST), DUPX_Log::LV_HARD_DEBUG);
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $paramsManager  = DUPX_Params_Manager::getInstance();
        $paramsManager->setValueFromInput(DUPX_Params_Manager::PARAM_LOGGING, DUPX_Param_item_form::INPUT_POST);
        DUPX_Log::setLogLevel();

        $oldSubsiteId = $paramsManager->getValue(DUPX_Params_Manager::PARAM_SUBSITE_ID);

        $readParamsList = array(
            DUPX_Params_Manager::PARAM_INST_TYPE,
            DUPX_Params_Manager::PARAM_PATH_NEW,
            DUPX_Params_Manager::PARAM_URL_NEW,
            DUPX_Params_Manager::PARAM_PATH_WP_CORE_NEW,
            DUPX_Params_Manager::PARAM_SITE_URL,
            DUPX_Params_Manager::PARAM_PATH_CONTENT_NEW,
            DUPX_Params_Manager::PARAM_URL_CONTENT_NEW,
            DUPX_Params_Manager::PARAM_PATH_UPLOADS_NEW,
            DUPX_Params_Manager::PARAM_URL_UPLOADS_NEW,
            DUPX_Params_Manager::PARAM_PATH_PLUGINS_NEW,
            DUPX_Params_Manager::PARAM_URL_PLUGINS_NEW,
            DUPX_Params_Manager::PARAM_PATH_MUPLUGINS_NEW,
            DUPX_Params_Manager::PARAM_URL_MUPLUGINS_NEW,
            DUPX_Params_Manager::PARAM_SUBSITE_ID,
            DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_ID,
            DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_NEW_SLUG,
            DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE,
            DUPX_Params_Manager::PARAM_ARCHIVE_ACTION,
            DUPX_Params_Manager::PARAM_ARCHIVE_ENGINE,
            DUPX_Params_Manager::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES,
            DUPX_Params_Manager::PARAM_DB_ENGINE,
            DUPX_Params_Manager::PARAM_REPLACE_ENGINE,
            DUPX_Params_Manager::PARAM_SET_FILE_PERMS,
            DUPX_Params_Manager::PARAM_SET_DIR_PERMS,
            DUPX_Params_Manager::PARAM_FILE_PERMS_VALUE,
            DUPX_Params_Manager::PARAM_DIR_PERMS_VALUE,
            DUPX_Params_Manager::PARAM_SAFE_MODE,
            DUPX_Params_Manager::PARAM_WP_CONFIG,
            DUPX_Params_Manager::PARAM_HTACCESS_CONFIG,
            DUPX_Params_Manager::PARAM_OTHER_CONFIG,
            DUPX_Params_Manager::PARAM_FILE_TIME,
            DUPX_Params_Manager::PARAM_REMOVE_RENDUNDANT,
            DUPX_Params_Manager::PARAM_KEEP_TARGET_SITE_USERS,
            DUPX_Params_Manager::PARAM_CONTENT_OWNER,
            DUPX_Params_Manager::PARAM_BLOGNAME,
            DUPX_Params_Manager::PARAM_ACCEPT_TERM_COND
        );

        foreach ($readParamsList as $cParam) {
            if ($paramsManager->setValueFromInput($cParam, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }

        $paramsManager->setValue(DUPX_Params_Manager::PARAM_REPLACE_ENGINE, DUPX_Params_Descriptor_engines::getReplaceEngineModeFromParams());
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_CHUNK, DUPX_Params_Descriptor_engines::getDbChunkFromParams());

        self::setParamsDatabase();
        self::updateBlogname($oldSubsiteId);

        if (self::$paramsValidated) {

            self::setParamsOnAddSiteOnMultisite();

            DUPX_Log::info('UPDATE PARAMS FROM SUBSITE ID', DUPX_Log::LV_DEBUG);
            DUPX_Log::info('NETWORK INSTALL: ' . DUPX_Log::varToString(DUPX_MU::newSiteIsMultisite()), DUPX_Log::LV_DEBUG);

            // UPDATE ACTIVE PARAMS BY SUBSITE ID
            $subsiteId = $paramsManager->getValue(DUPX_Params_Manager::PARAM_SUBSITE_ID);
            DUPX_Log::info('SUBSITE ID: ' . DUPX_Log::varToString($subsiteId), DUPX_Log::LV_DEBUG);

            $activePlugins = DUPX_Plugins_Manager::getInstance()->getDefaultActivePluginsList($subsiteId);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_PLUGINS, $activePlugins);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_MU_REPLACE, $archive_config->getNewUrlsArrayIdVal());

            // IF SAFE MODE DISABLE ALL PLUGINS
            if ($paramsManager->getValue(DUPX_Params_Manager::PARAM_SAFE_MODE) > 0) {
                $forceDisable = DUPX_Plugins_Manager::getInstance()->getAllPluginsSlugs();

                // EXCLUDE DUPLICATOR PRO
                if (($key = array_search(DUPX_Plugins_Manager::SLUG_DUPLICATOR_PRO, $forceDisable)) !== false) {
                    unset($forceDisable[$key]);
                }

                $paramsManager->setValue(DUPX_Params_Manager::PARAM_FORCE_DIABLE_PLUGINS, $forceDisable);
            }
        }

        // reload state after new path and new url
        DUPX_InstallerState::getInstance()->checkState(false, false);
        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsAfterValidation()
    {
        DUPX_LOG::info("\nCTRL PARAMS AFTER VALIDATION");
        $paramsManager = DUPX_Params_Manager::getInstance();

        $paramsManager->setValue(DUPX_Params_Manager::PARAM_WP_ADDON_SITES_PATHS, DUPX_Validation_test_addon_sites::getAddonsListsFolders());
        DUPX_Params_Descriptor_database::updateCharsetAndCollateByDatabaseSettings();

        $configsChecks = DUPX_Validation_test_iswritable_configs::configsWritableChecks();

        if ($configsChecks['wpconfig'] === false) {
            DUPX_LOG::info("WP-CONFIG ISN\'T READABLE SO SET noting ON " . DUPX_Params_Manager::PARAM_WP_CONFIG . ' PARAM');
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_WP_CONFIG, 'nothing');
            $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_WP_CONFIG, DUPX_Param_item_form::STATUS_INFO_ONLY);
        }

        if ($configsChecks['htaccess'] === false) {
            DUPX_LOG::info("HTACCESS ISN\'T READABLE SO SET noting ON " . DUPX_Params_Manager::PARAM_HTACCESS_CONFIG . ' PARAM');
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_HTACCESS_CONFIG, 'nothing');
            $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_HTACCESS_CONFIG, DUPX_Param_item_form::STATUS_INFO_ONLY);
        }

        if ($configsChecks['other'] === false) {
            DUPX_LOG::info("OTHER CONFIGS ISN\'T READABLE SO SET noting ON " . DUPX_Params_Manager::PARAM_OTHER_CONFIG . ' PARAM');
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_OTHER_CONFIG, 'nothing');
            $paramsManager->setFormStatus(DUPX_Params_Manager::PARAM_OTHER_CONFIG, DUPX_Param_item_form::STATUS_INFO_ONLY);
        }

        $paramsManager->save();

        return self::$paramsValidated;
    }

    /**
     * update blog name if subsite id is changed 
     * 
     * @param int $oldSubsiteId
     * @return void
     */
    protected static function updateBlogname($oldSubsiteId)
    {
        $paramsManager  = DUPX_Params_Manager::getInstance();
        $archive_config = DUPX_ArchiveConfig::getInstance();

        if ($paramsManager->getInitStatus(DUPX_Params_Manager::PARAM_SUBSITE_ID) === DUPX_Param_item::STATUS_OVERWRITE) {
            return;
        }

        if ($oldSubsiteId == $paramsManager->getValue(DUPX_Params_Manager::PARAM_SUBSITE_ID)) {
            return;
        }

        $paramsManager->setValue(DUPX_Params_Manager::PARAM_BLOGNAME, $archive_config->getBlognameFromSelectedSubsiteId());
    }

    /**
     * 
     * @return bool
     */
    protected static function setParamsDatabase()
    {
        $paramsManager = DUPX_Params_Manager::getInstance();

        $paramsManager->setValueFromInput(DUPX_Params_Manager::PARAM_DB_VIEW_MODE, DUPX_Param_item_form::INPUT_POST);

        switch ($paramsManager->getValue(DUPX_Params_Manager::PARAM_DB_VIEW_MODE)) {
            case 'basic':
                $readParamsList = array(
                    DUPX_Params_Manager::PARAM_DB_ACTION,
                    DUPX_Params_Manager::PARAM_DB_HOST,
                    DUPX_Params_Manager::PARAM_DB_NAME,
                    DUPX_Params_Manager::PARAM_DB_USER,
                    DUPX_Params_Manager::PARAM_DB_PASS
                );
                foreach ($readParamsList as $cParam) {
                    if ($paramsManager->setValueFromInput($cParam, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
                        self::$paramsValidated = false;
                    }
                }
                break;
            case 'cpnl':
                $readParamsList = array(
                    DUPX_Params_Manager::PARAM_CPNL_HOST,
                    DUPX_Params_Manager::PARAM_CPNL_USER,
                    DUPX_Params_Manager::PARAM_CPNL_PASS,
                    DUPX_Params_Manager::PARAM_CPNL_DB_USER_CHK,
                    DUPX_Params_Manager::PARAM_CPNL_PREFIX,
                    DUPX_Params_Manager::PARAM_CPNL_DB_ACTION,
                    DUPX_Params_Manager::PARAM_CPNL_DB_HOST,
                    DUPX_Params_Manager::PARAM_CPNL_DB_NAME_SEL,
                    DUPX_Params_Manager::PARAM_CPNL_DB_NAME_TXT,
                    DUPX_Params_Manager::PARAM_CPNL_DB_USER_SEL,
                    DUPX_Params_Manager::PARAM_CPNL_DB_USER_TXT,
                    DUPX_Params_Manager::PARAM_CPNL_DB_PASS,
                    DUPX_Params_Manager::PARAM_CPNL_IGNORE_PREFIX
                );
                foreach ($readParamsList as $cParam) {
                    if ($paramsManager->setValueFromInput($cParam, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
                        self::$paramsValidated = false;
                    }
                }

                // NORMALIZE VALUES FOR DB TEST
                if ($paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_ACTION, $paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_DB_ACTION)) === false) {
                    self::$paramsValidated = false;
                }
                // DBHOST
                if ($paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_HOST, $paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_DB_HOST)) === false) {
                    self::$paramsValidated = false;
                }

                $cpnlPrefix   = $paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_PREFIX);
                $ignorePrefix = $paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_IGNORE_PREFIX);

                // DBNAME
                if ($paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_DB_ACTION) === 'create') {
                    // CREATE NEW DATABASE
                    $dbName = $paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_DB_NAME_TXT);
                } else {
                    // GET EXISTS DATABASE
                    $dbName = $paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_DB_NAME_SEL);
                }

                if ($ignorePrefix === false && strpos($dbName, $cpnlPrefix) !== 0) {
                    $dbName = $cpnlPrefix . $dbName;
                }
                if ($paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_NAME, $dbName) === false) {
                    self::$paramsValidated = false;
                }

                // DB USER
                if ($paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_DB_USER_CHK)) {
                    // CREATE NEW USER
                    $dbUser = $paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_DB_USER_TXT);
                } else {
                    // GET EXIST USER
                    $dbUser = $paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_DB_USER_SEL);
                }
                if ($ignorePrefix === false && strpos($dbUser, $cpnlPrefix) !== 0) {
                    $dbUser = $cpnlPrefix . $dbUser;
                }
                if ($paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_USER, $dbUser) === false) {
                    self::$paramsValidated = false;
                }

                //DBPASS
                if ($paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_PASS, $paramsManager->getValue(DUPX_Params_Manager::PARAM_CPNL_DB_PASS)) === false) {
                    self::$paramsValidated = false;
                }
                break;
        }

        $readParamsList = array(
            DUPX_Params_Manager::PARAM_DB_TABLE_PREFIX
        );

        foreach ($readParamsList as $cParam) {
            if ($paramsManager->setValueFromInput($cParam, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }

        if ($paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_TABLES, DUPX_DB_Tables::getInstance()->getDefaultParamValue()) === false) {
            self::$paramsValidated = false;
        }

        return self::$paramsValidated;
    }

    /**
     * 
     * @return void
     */
    public static function setParamsOnAddSiteOnMultisite()
    {
        if (!DUPX_InstallerState::isAddSiteOnMultisite()) {
            return;
        }

        $paramsManager      = DUPX_Params_Manager::getInstance();
        $overwriteData      = $paramsManager->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
        $overwriteSubsiteId = $paramsManager->getValue(DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_ID);

        if ($overwriteSubsiteId < 0) {
            throw new Exception('Invalid overwrite subsite id not set');
        }

        if ($overwriteSubsiteId > 0 && ($overwriteSubisite = DUPX_MU::getSubsiteOverwriteById($overwriteSubsiteId)) == false) {
            throw new Exception('Invalid overwrite subsite id ' . $overwriteSubsiteId);
        }

        if (empty($overwriteData['adminUsers'])) {
            throw new Exception('Empty admin users');
        }

        DUPX_Log::info('OVERWRITE SUBSITE INFO SET [' . $overwriteSubsiteId . ']');
        if ($overwriteSubsiteId > 0 && DUPX_Log::isLevel(DUPX_Log::LV_DETAILED)) {
            DUPX_Log::info(DUPX_Log::varToString($overwriteSubisite), DUPX_Log::LV_DETAILED);
        }

        $paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_ACTION, DUPX_DBInstall::DBACTION_REMOVE_ONLY_TABLES);
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES, DUP_PRO_Extraction::FILTER_ONLY_MEDIA_PLUG_THEMES);
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_ARCHIVE_ACTION, DUP_PRO_Extraction::ACTION_REMOVE_WP_FILES);
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_WP_CONFIG, 'nothing');
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_HTACCESS_CONFIG, 'nothing');
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_OTHER_CONFIG, 'nothing');
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_DB_TABLE_PREFIX, $overwriteData['table_prefix']);
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_EMPTY_SCHEDULE_STORAGE, false);

        if ($overwriteSubsiteId > 0) {
            // Overwrite existing subsite
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_URL_NEW, $overwriteSubisite['fullHomeUrl']);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_SITE_URL, $overwriteSubisite['fullSiteUrl']);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_URL_UPLOADS_NEW, $overwriteSubisite['fullUploadUrl']);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_PATH_UPLOADS_NEW, $overwriteSubisite['fullUploadPath']);
        } else {
            // temp values updated after new subsite is created
            $newSlug = $paramsManager->getValue(DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_NEW_SLUG);
            if (isset($overwriteData['subdomain']) && $overwriteData['subdomain']) {
                $parseUrl         = DupProSnapLibURLU::parseUrl($overwriteData['urls']['home']);
                $parseUrl['host'] = $newSlug . '.' . DupProSnapLibURLU::wwwRemove($parseUrl['host']);

                $newUrl = DupProSnapLibURLU::buildUrl($parseUrl);
            } else {
                $newUrl = $overwriteData['urls']['home'] . '/' . $newSlug;
            }
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_URL_NEW, $newUrl);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_SITE_URL, $overwriteData['urls']['abs']);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_URL_UPLOADS_NEW, $overwriteData['urls']['uploads']);
            $paramsManager->setValue(DUPX_Params_Manager::PARAM_PATH_UPLOADS_NEW, $overwriteData['paths']['uploads']);
        }

        $paramsManager->setValue(DUPX_Params_Manager::PARAM_PATH_WP_CORE_NEW, $overwriteData['paths']['abs']);

        $paramsManager->setValue(DUPX_Params_Manager::PARAM_URL_CONTENT_NEW, $overwriteData['urls']['wpcontent']);
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_PATH_CONTENT_NEW, $overwriteData['paths']['wpcontent']);

        $paramsManager->setValue(DUPX_Params_Manager::PARAM_URL_PLUGINS_NEW, $overwriteData['urls']['plugins']);
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_PATH_PLUGINS_NEW, $overwriteData['paths']['plugins']);

        $paramsManager->setValue(DUPX_Params_Manager::PARAM_URL_MUPLUGINS_NEW, $overwriteData['urls']['muplugins']);
        $paramsManager->setValue(DUPX_Params_Manager::PARAM_PATH_MUPLUGINS_NEW, $overwriteData['paths']['muplugins']);
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsStep2()
    {
        DUPX_LOG::info('CTRL PARAMS S2', DUPX_Log::LV_DETAILED);
        DUPX_Log::info('REQUEST: ' . DUPX_Log::varToString($_REQUEST), DUPX_Log::LV_HARD_DEBUG);
        $paramsManager = DUPX_Params_Manager::getInstance();

        $readParamsList = array(
            DUPX_Params_Manager::PARAM_DB_TABLES,
            DUPX_Params_Manager::PARAM_DB_CHARSET,
            DUPX_Params_Manager::PARAM_DB_COLLATE,
            DUPX_Params_Manager::PARAM_DB_SPACING,
            DUPX_Params_Manager::PARAM_DB_VIEW_CREATION,
            DUPX_Params_Manager::PARAM_DB_PROC_CREATION,
            DUPX_Params_Manager::PARAM_DB_FUNC_CREATION,
            DUPX_Params_Manager::PARAM_DB_SPLIT_CREATES,
            DUPX_Params_Manager::PARAM_DB_MYSQL_MODE,
            DUPX_Params_Manager::PARAM_DB_MYSQL_MODE_OPTS
        );

        foreach ($readParamsList as $cParam) {
            if ($paramsManager->setValueFromInput($cParam, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }

        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamsStep3()
    {
        DUPX_LOG::info('CTRL PARAMS S3', DUPX_Log::LV_DETAILED);
        DUPX_Log::info('REQUEST: ' . DUPX_Log::varToString($_REQUEST), DUPX_Log::LV_HARD_DEBUG);

        $paramsManager = DUPX_Params_Manager::getInstance();

        $readParamsList = array(
            DUPX_Params_Manager::PARAM_REPLACE_MODE,
            DUPX_Params_Manager::PARAM_MU_REPLACE,
            DUPX_Params_Manager::PARAM_EMPTY_SCHEDULE_STORAGE,
            DUPX_Params_Manager::PARAM_EMAIL_REPLACE,
            DUPX_Params_Manager::PARAM_FULL_SEARCH,
            DUPX_Params_Manager::PARAM_POSTGUID,
            DUPX_Params_Manager::PARAM_MAX_SERIALIZE_CHECK,
            DUPX_Params_Manager::PARAM_MULTISITE_CROSS_SEARCH,
            DUPX_Params_Manager::PARAM_PLUGINS,
            DUPX_Params_Manager::PARAM_CUSTOM_SEARCH,
            DUPX_Params_Manager::PARAM_CUSTOM_REPLACE,
            DUPX_Params_Manager::PARAM_WP_CONF_DISALLOW_FILE_EDIT,
            DUPX_Params_Manager::PARAM_WP_CONF_DISALLOW_FILE_MODS,
            DUPX_Params_Manager::PARAM_WP_CONF_AUTOSAVE_INTERVAL,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_POST_REVISIONS,
            DUPX_Params_Manager::PARAM_WP_CONF_FORCE_SSL_ADMIN,
            DUPX_Params_Manager::PARAM_WP_CONF_IMAGE_EDIT_OVERWRITE,
            DUPX_Params_Manager::PARAM_GEN_WP_AUTH_KEY,
            DUPX_Params_Manager::PARAM_WP_CONF_AUTOMATIC_UPDATER_DISABLED,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_CACHE,
            DUPX_Params_Manager::PARAM_WP_CONF_WPCACHEHOME,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_DEBUG,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_DEBUG_LOG,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_DISABLE_FATAL_ERROR_HANDLER,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_DEBUG_DISPLAY,
            DUPX_Params_Manager::PARAM_WP_CONF_SCRIPT_DEBUG,
            DUPX_Params_Manager::PARAM_WP_CONF_CONCATENATE_SCRIPTS,
            DUPX_Params_Manager::PARAM_WP_CONF_SAVEQUERIES,
            DUPX_Params_Manager::PARAM_WP_CONF_ALTERNATE_WP_CRON,
            DUPX_Params_Manager::PARAM_WP_CONF_DISABLE_WP_CRON,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_CRON_LOCK_TIMEOUT,
            DUPX_Params_Manager::PARAM_WP_CONF_EMPTY_TRASH_DAYS,
            DUPX_Params_Manager::PARAM_WP_CONF_COOKIE_DOMAIN,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_MEMORY_LIMIT,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT,
            DUPX_Params_Manager::PARAM_WP_CONF_WP_TEMP_DIR,
            DUPX_Params_Manager::PARAM_USERS_PWD_RESET,
            DUPX_Params_Manager::PARAM_WP_ADMIN_CREATE_NEW
        );

        foreach ($readParamsList as $cParam) {
            if ($paramsManager->setValueFromInput($cParam, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
                self::$paramsValidated = false;
            }
        }

        if ($paramsManager->getValue(DUPX_Params_Manager::PARAM_WP_ADMIN_CREATE_NEW)) {
            $readParamsList = array(
                DUPX_Params_Manager::PARAM_WP_ADMIN_NAME,
                DUPX_Params_Manager::PARAM_WP_ADMIN_PASSWORD,
                DUPX_Params_Manager::PARAM_WP_ADMIN_MAIL,
                DUPX_Params_Manager::PARAM_WP_ADMIN_NICKNAME,
                DUPX_Params_Manager::PARAM_WP_ADMIN_FIRST_NAME,
                DUPX_Params_Manager::PARAM_WP_ADMIN_LAST_NAME
            );

            foreach ($readParamsList as $cParam) {
                if ($paramsManager->setValueFromInput($cParam, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
                    self::$paramsValidated = false;
                }
            }

            if (DUPX_DB_Functions::getInstance()->checkIfUserNameExists($paramsManager->getValue(DUPX_Params_Manager::PARAM_WP_ADMIN_NAME))) {
                self::$paramsValidated = false;
                DUPX_NOTICE_MANAGER::getInstance()->addNextStepNotice(array(
                    'shortMsg'    => 'The user ' . $paramsManager->getValue(DUPX_Params_Manager::PARAM_WP_ADMIN_NAME) . ' can\'t be created, already exists',
                    'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                    'longMsg'     => 'Please insert another new user login name',
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML
                ));
            }
        }

        $paramsManager->save();
        return self::$paramsValidated;
    }

    /**
     * 
     * @return boolean
     */
    public static function setParamAutoClean()
    {
        $paramsManager = DUPX_Params_Manager::getInstance();
        if ($paramsManager->setValueFromInput(DUPX_Params_Manager::PARAM_AUTO_CLEAN_INSTALLER_FILES, DUPX_Param_item_form::INPUT_POST, false, true) === false) {
            self::$paramsValidated = false;
        }
        $paramsManager->save();
        return self::$paramsValidated;
    }
}
