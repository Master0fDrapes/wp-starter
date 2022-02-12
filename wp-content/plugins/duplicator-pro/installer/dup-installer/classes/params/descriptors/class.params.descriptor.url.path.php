<?php

/**
 * Installer params manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @final class DUPX_Params_Descriptor_urls_paths
  {
  package SC\DUPX\U
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class DUPX_Params_Descriptor_urls_paths implements DUPX_Interface_Params_Descriptor
{
    const INVALID_PATH_EMPTY = 'can\'t be empty';
    const INVALID_URL_EMPTY  = 'can\'t be empty';

    public static function init(&$params)
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $paths          = $archive_config->getRealValue('archivePaths');

        $oldMainPath = $paths->home;
        $newMainPath = DUPX_ROOT;

        $oldHomeUrl = rtrim($archive_config->getRealValue('homeUrl'), '/');
        $newHomeUrl = rtrim(DUPX_ROOT_URL, '/');

        $oldSiteUrl      = rtrim($archive_config->getRealValue('siteUrl'), '/');
        $oldContentUrl   = rtrim($archive_config->getRealValue('contentUrl'), '/');
        $oldUploadUrl    = rtrim($archive_config->getRealValue('uploadBaseUrl'), '/');
        $oldPluginsUrl   = rtrim($archive_config->getRealValue('pluginsUrl'), '/');
        $oldMuPluginsUrl = rtrim($archive_config->getRealValue('mupluginsUrl'), '/');

        $oldWpAbsPath       = $paths->abs;
        $oldContentPath     = $paths->wpcontent;
        $oldUploadsBasePath = $paths->uploads;
        $oldPluginsPath     = $paths->plugins;
        $oldMuPluginsPath   = $paths->muplugins;

        $defValEdit = "This default value is automatically generated.\n"
            . "Change it only if you're sure you know what you're doing!";

        $params[DUPX_Params_Manager::PARAM_URL_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_URL_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'default' => $oldHomeUrl
            )
        );

        $params[DUPX_Params_Manager::PARAM_WP_ADDON_SITES_PATHS] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_WP_ADDON_SITES_PATHS,
            DUPX_Param_item_form::TYPE_ARRAY_STRING,
            array(
            'default' => array()
            )
        );

        $params[DUPX_Params_Manager::PARAM_URL_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_URL_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => $newHomeUrl,
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizeUrl'),
            'validateCallback' => array('DUPX_Params_Descriptors', 'validateUrlWithScheme'),
            'invalidMessage'   => self::INVALID_URL_EMPTY
            ),
            array(// FORM ATTRIBUTES
            'label'  => 'New Site URL:',
            'status' => function (DUPX_Param_item_form $param) {
                if (
                    DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE) ||
                    DUPX_InstallerState::isAddSiteOnMultisite()
                ) {
                    return DUPX_Param_item_form::STATUS_INFO_ONLY;
                } else {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                }
            },
            'wrapperClasses' => array('revalidate-on-change', 'cant-be-empty'),
            'subNote'        => function (DUPX_Param_item_form $param) {
                $archive_config = DUPX_ArchiveConfig::getInstance();
                $oldHomeUrl     = rtrim($archive_config->getRealValue('homeUrl'), '/');
                $subsiteId      = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_SUBSITE_ID);
                if (
                    DUPX_InstallerState::isInstType(
                        array(
                            DUPX_InstallerState::INSTALL_STANDALONE,
                            DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN,
                            DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER,
                            DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN,
                            DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER
                        )
                    ) &&
                    $subsiteId > 0
                ) {
                    $subsiteObj = $archive_config->getSubsiteObjById($subsiteId);
                    $oldHomeUrl = isset($subsiteObj->fullHomeUrl) ? $subsiteObj->fullHomeUrl : $oldHomeUrl;
                } else {
                    $archive_config = DUPX_ArchiveConfig::getInstance();
                }
                return 'Old value: <b>' . DUPX_U::esc_html($oldHomeUrl) . '</b>';
            },
            'postfix' => array('type' => 'button', 'label' => 'get', 'btnAction' => 'DUPX.getNewUrlByDomObj(this);')
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_PATH_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'default' => $oldMainPath
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_PATH_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => $newMainPath,
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizePath'),
            'validateCallback' => function ($value) {
                // if home path is root path is necessary do a trailingslashit
                $realPath = DupProSnapLibIOU::safePathTrailingslashit($value);
                if (!is_dir($realPath)) {
                    return false;
                }

                // don't check the return of chmod, if fail the installer must continue
                DupProSnapLibIOU::chmod($realPath, 'u+rwx');
                return true;
            },
            'invalidMessage' => 'The new path must be an existing folder on the server.<br>'
            . 'It is not possible to continue the installation without first creating the folder.'
            ),
            array(// FORM ATTRIBUTES
            'label'  => 'New Path:',
            'status' => function (DUPX_Param_item_form $param) {
                if (
                    DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_TEMPLATE) !== DUPX_Template::TEMPLATE_ADVANCED ||
                    DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE) ||
                    DUPX_InstallerState::isAddSiteOnMultisite()
                ) {
                    return DUPX_Param_item_form::STATUS_INFO_ONLY;
                } else {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                }
            },
                                        'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldMainPath) . '</b>',
                                        'wrapperClasses' => array('revalidate-on-change', 'cant-be-empty')
            )
        );

        $params[DUPX_Params_Manager::PARAM_SITE_URL_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_SITE_URL_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'default' => $oldSiteUrl
            )
        );

        $wrapClasses    = array('revalidate-on-change', 'cant-be-empty', 'auto-updatable', 'autoupdate-enabled');
        $postfixElement = array(
            'type'      => 'button',
            'label'     => 'Auto',
            'btnAction' => 'DUPX.autoUpdateToggle(this, ' . DupProSnapJsonU::wp_json_encode($defValEdit) . ');'
        );

        $params[DUPX_Params_Manager::PARAM_SITE_URL] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_SITE_URL,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => '', // if empty is generate automatically on ctrl params s0
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizeUrl'),
            'validateCallback' => array('DUPX_Params_Descriptors', 'validateUrlWithScheme'),
            'invalidMessage'   => self::INVALID_URL_EMPTY
            ),
            array(// FORM ATTRIBUTES
            'label'          => ' WP core URL:',
            'status'         => array(__CLASS__, 'statusFormOthePathsUrls'),
            'postfix'        => $postfixElement,
            'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldSiteUrl) . '</b>',
            'wrapperClasses' => $wrapClasses,
            'wrapperAttr'    => array(
                'data-auto-update-from-input' => $params[DUPX_Params_Manager::PARAM_URL_NEW]->getFormItemId()
            )
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_CONTENT_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_PATH_CONTENT_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'default' => $oldContentPath
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_CONTENT_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_PATH_CONTENT_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => '', // if empty is generate automatically on ctrl params s0
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizePath'),
            'validateCallback' => array('DUPX_Params_Descriptors', 'validatePath'),
            'invalidMessage'   => self::INVALID_PATH_EMPTY
            ),
            array(// FORM ATTRIBUTES
            'label'          => 'WP-content path:',
            'status'         => array(__CLASS__, 'statusFormOthePathsUrls'),
            'postfix'        => $postfixElement,
            'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldContentPath) . '</b>',
            'wrapperClasses' => $wrapClasses,
            'wrapperAttr'    => array(
                'data-auto-update-from-input' => $params[DUPX_Params_Manager::PARAM_PATH_NEW]->getFormItemId()
            )
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_WP_CORE_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_PATH_WP_CORE_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'default' => $oldWpAbsPath
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_WP_CORE_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_PATH_WP_CORE_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => '', // if empty is generate automatically on ctrl params s0
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizePath'),
            'validateCallback' => function ($value) {
                $homePath = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_PATH_NEW);
                return (DupProSnapLibIOU::getRelativePath($value, $homePath) !== false);
            },
            'invalidMessage'                                     => function (DUPX_Param_item $param) {
                $homePath = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_PATH_NEW);
                return 'ABSPATH have to be a equal or a child of HOMEPATH' .
                '<pre>' .
                'ABSPATH : ' . $param->getValue() . '<br>' .
                'HOMEPATH: ' . $homePath . '<br>' .
                '</pre>';
            }
            ),
            array(// FORM ATTRIBUTES
            'label'          => 'WP core path:',
            'status'         => array(__CLASS__, 'statusFormOthePathsUrls'),
            'postfix'        => $postfixElement,
            'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldWpAbsPath) . '</b>',
            'wrapperClasses' => $wrapClasses,
            'wrapperAttr'    => array(
                'data-auto-update-from-input' => $params[DUPX_Params_Manager::PARAM_PATH_NEW]->getFormItemId()
                                                                         )
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_UPLOADS_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_PATH_UPLOADS_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'default' => $oldUploadsBasePath
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_UPLOADS_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_PATH_UPLOADS_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => '', // if empty is generate automatically on ctrl params s0
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizePath'),
            'validateCallback' => function ($value) {
                $paramsManager = DUPX_Params_Manager::getInstance();
                return (
                DupProSnapLibIOU::isChildPath($value, $paramsManager->getValue(DUPX_Params_Manager::PARAM_PATH_NEW), false, false) ||
                DupProSnapLibIOU::isChildPath($value, $paramsManager->getValue(DUPX_Params_Manager::PARAM_PATH_CONTENT_NEW), false, false)
                );
            },
            'invalidMessage'                                     => 'Upload path have to be a child of wp-content path'
            ),
                                                                               array(// FORM ATTRIBUTES
            'label'          => 'Uploads path:',
            'status'         => array(__CLASS__, 'statusFormOthePathsUrls'),
            'postfix'        => $postfixElement,
            'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldUploadsBasePath) . '</b>',
            'wrapperClasses' => $wrapClasses,
            'wrapperAttr'    => array(
                'data-auto-update-from-input' => $params[DUPX_Params_Manager::PARAM_PATH_NEW]->getFormItemId()
            )
            )
        );

        $params[DUPX_Params_Manager::PARAM_URL_CONTENT_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_URL_CONTENT_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'default' => $oldContentUrl
            )
        );

        $params[DUPX_Params_Manager::PARAM_URL_CONTENT_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_URL_CONTENT_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => '', // if empty is generate automatically on ctrl params s0
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizeUrl'),
            'validateCallback' => array('DUPX_Params_Descriptors', 'validateUrlWithScheme'),
            'invalidMessage'   => self::INVALID_URL_EMPTY
            ),
            array(// FORM ATTRIBUTES
            'label'          => 'WP-content URL:',
            'status'         => array(__CLASS__, 'statusFormOthePathsUrls'),
            'postfix'        => $postfixElement,
            'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldContentUrl) . '</b>',
            'wrapperClasses' => $wrapClasses,
            'wrapperAttr'    => array(
                'data-auto-update-from-input' => $params[DUPX_Params_Manager::PARAM_URL_NEW]->getFormItemId()
            )
            )
        );

        $params[DUPX_Params_Manager::PARAM_URL_UPLOADS_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_URL_UPLOADS_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(// ITEM ATTRIBUTES
            'default' => $oldUploadUrl
            )
        );

        $params[DUPX_Params_Manager::PARAM_URL_UPLOADS_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_URL_UPLOADS_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => '', // if empty is generate automatically on ctrl params s0
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizeUrl'),
            'validateCallback' => array('DUPX_Params_Descriptors', 'validateUrlWithScheme'),
            'invalidMessage'   => self::INVALID_URL_EMPTY
            ),
            array(// FORM ATTRIBUTES
            'label'          => 'Uploads URL:',
            'status'         => array(__CLASS__, 'statusFormOthePathsUrls'),
            'postfix'        => $postfixElement,
            'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldUploadUrl) . '</b>',
            'wrapperClasses' => $wrapClasses,
            'wrapperAttr'    => array(
                'data-auto-update-from-input' => $params[DUPX_Params_Manager::PARAM_URL_NEW]->getFormItemId()
            )
            )
        );

        $params[DUPX_Params_Manager::PARAM_URL_PLUGINS_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_URL_PLUGINS_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(// ITEM ATTRIBUTES
            'default' => $oldPluginsUrl
            )
        );

        $params[DUPX_Params_Manager::PARAM_URL_PLUGINS_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_URL_PLUGINS_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => '', // if empty is generate automatically on ctrl params s0
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizeUrl'),
            'validateCallback' => array('DUPX_Params_Descriptors', 'validateUrlWithScheme'),
            'invalidMessage'   => self::INVALID_URL_EMPTY
            ),
            array(// FORM ATTRIBUTES
            'label'          => 'Plugins URL:',
            'status'         => array(__CLASS__, 'statusFormOthePathsUrls'),
            'postfix'        => $postfixElement,
            'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldPluginsUrl) . '</b>',
            'wrapperClasses' => $wrapClasses,
            'wrapperAttr'    => array(
                'data-auto-update-from-input' => $params[DUPX_Params_Manager::PARAM_URL_NEW]->getFormItemId()
            )
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_PLUGINS_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_PATH_PLUGINS_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'default'          => $oldPluginsPath,
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizePath'),
            'validateCallback' => array('DUPX_Params_Descriptors', 'validatePath'),
            'invalidMessage'   => self::INVALID_PATH_EMPTY
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_PLUGINS_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_PATH_PLUGINS_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => '', // if empty is generate automatically on ctrl params s0
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizePath'),
            'validateCallback' => array('DUPX_Params_Descriptors', 'validatePath'),
            'invalidMessage'   => self::INVALID_PATH_EMPTY
            ),
            array(// FORM ATTRIBUTES
            'label'          => 'Plugins path:',
            'status'         => array(__CLASS__, 'statusFormOthePathsUrls'),
            'postfix'        => $postfixElement,
            'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldPluginsPath) . '</b>',
            'wrapperClasses' => $wrapClasses,
            'wrapperAttr'    => array(
                'data-auto-update-from-input' => $params[DUPX_Params_Manager::PARAM_PATH_NEW]->getFormItemId()
            )
            )
        );

        $params[DUPX_Params_Manager::PARAM_URL_MUPLUGINS_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_URL_MUPLUGINS_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'default' => $oldMuPluginsUrl
            )
        );

        $params[DUPX_Params_Manager::PARAM_URL_MUPLUGINS_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_URL_MUPLUGINS_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => '', // if empty is generate automatically on ctrl params s0
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizeUrl'),
            'validateCallback' => array('DUPX_Params_Descriptors', 'validateUrlWithScheme'),
            'invalidMessage'   => self::INVALID_URL_EMPTY
            ),
            array(// FORM ATTRIBUTES
            'label'          => 'MU-plugins URL:',
            'status'         => array(__CLASS__, 'statusFormOthePathsUrls'),
            'postfix'        => $postfixElement,
            'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldMuPluginsUrl) . '</b>',
            'wrapperClasses' => $wrapClasses,
            'wrapperAttr'    => array(
                'data-auto-update-from-input' => $params[DUPX_Params_Manager::PARAM_URL_NEW]->getFormItemId()
            )
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_MUPLUGINS_OLD] = new DUPX_Param_item(
            DUPX_Params_Manager::PARAM_PATH_MUPLUGINS_OLD,
            DUPX_Param_item_form::TYPE_STRING,
            array(
            'default' => $oldMuPluginsPath
            )
        );

        $params[DUPX_Params_Manager::PARAM_PATH_MUPLUGINS_NEW] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_PATH_MUPLUGINS_NEW,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => '',
            'sanitizeCallback' => array('DUPX_Params_Descriptors', 'sanitizePath'),
            'validateCallback' => array('DUPX_Params_Descriptors', 'validatePath'),
            'invalidMessage'   => self::INVALID_PATH_EMPTY
            ),
            array(// FORM ATTRIBUTES
            'label'          => 'MU-plugins path:',
            'status'         => array(__CLASS__, 'statusFormOthePathsUrls'),
            'postfix'        => $postfixElement,
            'subNote'        => 'Old value: <b>' . DUPX_U::esc_html($oldMuPluginsPath) . '</b>',
            'wrapperClasses' => $wrapClasses,
            'wrapperAttr'    => array(
                'data-auto-update-from-input' => $params[DUPX_Params_Manager::PARAM_PATH_NEW]->getFormItemId()
            )
            )
        );
    }

    public static function statusFormOthePathsUrls(DUPX_Param_item_form $param)
    {
        if (
            DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_TEMPLATE) !== DUPX_Template::TEMPLATE_ADVANCED ||
            DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE) ||
            DUPX_InstallerState::isAddSiteOnMultisite()
        ) {
            return DUPX_Param_item_form::STATUS_INFO_ONLY;
        } else {
            return DUPX_Param_item_form::STATUS_READONLY;
        }
    }

    /**
     *
     * @param DUPX_Param_item[] $params
     */
    public static function updateParamsAfterOverwrite(&$params)
    {
        DUPX_Params_Manager::getInstance();

        $archive_config = DUPX_ArchiveConfig::getInstance();
        $paths          = $archive_config->getRealValue('archivePaths');

        $oldMainPath = $paths->home;
        $newMainPath = $params[DUPX_Params_Manager::PARAM_PATH_NEW]->getValue();

        $oldHomeUrl = rtrim($archive_config->getRealValue('homeUrl'), '/');
        $newHomeUrl = $params[DUPX_Params_Manager::PARAM_URL_NEW]->getValue();

        $oldSiteUrl      = rtrim($archive_config->getRealValue('siteUrl'), '/');
        $oldContentUrl   = rtrim($archive_config->getRealValue('contentUrl'), '/');
        $oldUploadUrl    = rtrim($archive_config->getRealValue('uploadBaseUrl'), '/');
        $oldPluginsUrl   = rtrim($archive_config->getRealValue('pluginsUrl'), '/');
        $oldMuPluginsUrl = rtrim($archive_config->getRealValue('mupluginsUrl'), '/');

        // if empty value isn't overwritten
        if (strlen($params[DUPX_Params_Manager::PARAM_PATH_WP_CORE_NEW]->getValue()) == 0) {
            $newVal = DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $paths->abs); // if empty is generate automatically on ctrl params s0
            $params[DUPX_Params_Manager::PARAM_PATH_WP_CORE_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[DUPX_Params_Manager::PARAM_PATH_CONTENT_NEW]->getValue()) == 0) {
            $newVal = DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $paths->wpcontent); // if empty is generate automatically on ctrl params s0
            $params[DUPX_Params_Manager::PARAM_PATH_CONTENT_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[DUPX_Params_Manager::PARAM_PATH_UPLOADS_NEW]->getValue()) == 0) {
            $newVal = DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $paths->uploads); // if empty is generate automatically on ctrl params s0
            $params[DUPX_Params_Manager::PARAM_PATH_UPLOADS_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[DUPX_Params_Manager::PARAM_PATH_PLUGINS_NEW]->getValue()) == 0) {
            $newVal = DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $paths->plugins); // if empty is generate automatically on ctrl params s0
            $params[DUPX_Params_Manager::PARAM_PATH_PLUGINS_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[DUPX_Params_Manager::PARAM_PATH_MUPLUGINS_NEW]->getValue()) == 0) {
            $newVal = DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $paths->muplugins); // if empty is generate automatically on ctrl params s0
            $params[DUPX_Params_Manager::PARAM_PATH_MUPLUGINS_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[DUPX_Params_Manager::PARAM_SITE_URL]->getValue()) == 0) {
            $newVal = DUPX_ArchiveConfig::getNewSubUrl($oldHomeUrl, $newHomeUrl, $oldSiteUrl); // if empty is generate automatically on ctrl params s0
            $params[DUPX_Params_Manager::PARAM_SITE_URL]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[DUPX_Params_Manager::PARAM_URL_CONTENT_NEW]->getValue()) == 0) {
            $newVal = DUPX_ArchiveConfig::getNewSubUrl($oldHomeUrl, $newHomeUrl, $oldContentUrl); // if empty is generate automatically on ctrl params s0
            $params[DUPX_Params_Manager::PARAM_URL_CONTENT_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[DUPX_Params_Manager::PARAM_URL_UPLOADS_NEW]->getValue()) == 0) {
            $newVal = DUPX_ArchiveConfig::getNewSubUrl($oldHomeUrl, $newHomeUrl, $oldUploadUrl); // if empty is generate automatically on ctrl params s0
            $params[DUPX_Params_Manager::PARAM_URL_UPLOADS_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[DUPX_Params_Manager::PARAM_URL_PLUGINS_NEW]->getValue()) == 0) {
            $newVal = DUPX_ArchiveConfig::getNewSubUrl($oldHomeUrl, $newHomeUrl, $oldPluginsUrl); // if empty is generate automatically on ctrl params s0
            $params[DUPX_Params_Manager::PARAM_URL_PLUGINS_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[DUPX_Params_Manager::PARAM_URL_MUPLUGINS_NEW]->getValue()) == 0) {
            $newVal = DUPX_ArchiveConfig::getNewSubUrl($oldHomeUrl, $newHomeUrl, $oldMuPluginsUrl); // if empty is generate automatically on ctrl params s0
            $params[DUPX_Params_Manager::PARAM_URL_MUPLUGINS_NEW]->setValue($newVal);
        }
    }
}
