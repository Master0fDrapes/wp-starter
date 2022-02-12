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
final class DUPX_Params_Descriptor_configs implements DUPX_Interface_Params_Descriptor
{

    /**
     *
     * @param DUPX_Param_item[] $params
     */
    public static function init(&$params)
    {
        if (!isset($params[DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE])) {
            throw new Exception('Init engine descriptor before');
        }

        $params[DUPX_Params_Manager::PARAM_INST_TYPE] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_INST_TYPE,
            DUPX_Param_item_form::TYPE_INT,
            DUPX_Param_item_form::FORM_TYPE_RADIO,
            array(
            'default'        => DUPX_InstallerState::INSTALL_NOT_SET,
            'acceptValues'   => array(__CLASS__, 'getInstallTypesAcceptValues'),
            'invalidMessage' => 'Multisite install type invalid value'
            ),
            array(
            'status' => function ($paramObj) {
                if (
                    DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE)
                ) {
                    return DUPX_Param_item_form::STATUS_INFO_ONLY;
                } else {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                }
            },
            'label'          => 'Install Type:',
            'wrapperClasses' => array('group-block', 'revalidate-on-change'),
            'options'        => self::getInstallTypeOptions()
            )
        );

        $params[DUPX_Params_Manager::PARAM_WP_CONFIG] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_WP_CONFIG,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'      => 'modify',
            'acceptValues' => array(
                'modify',
                'nothing',
                'new'
            )),
            array(
            'label'          => 'WordPress wp-config:',
            'wrapperClasses' => 'medium',
            'status'         => function ($paramObj) {
                if (
                    DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE) ||
                    DUPX_InstallerState::isAddSiteOnMultisite()
                ) {
                    return DUPX_Param_item_form::STATUS_INFO_ONLY;
                } else {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                }
            },
            'options' => array(
                new DUPX_Param_item_form_option('nothing', 'Do nothing'),
                new DUPX_Param_item_form_option('modify', 'Modify original'),
                new DUPX_Param_item_form_option('new', 'Create new from wp-config sample')
            ))
        );

        $params[DUPX_Params_Manager::PARAM_HTACCESS_CONFIG] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_HTACCESS_CONFIG,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'      => $params[DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE]->getValue() ? 'original' : 'new',
            'acceptValues' => array(
                'new',
                'original',
                'nothing'
            )),
            array(
            'label'          => 'Apache .htaccess:',
            'wrapperClasses' => 'medium',
            'status'         => function ($paramObj) {
                if (
                    DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE) ||
                    DUPX_InstallerState::isAddSiteOnMultisite()
                ) {
                    return DUPX_Param_item_form::STATUS_INFO_ONLY;
                } else {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                }
            },
            'options' => array(
                new DUPX_Param_item_form_option('nothing', 'Do nothing'),
                new DUPX_Param_item_form_option('original', 'Retain original'),
                new DUPX_Param_item_form_option('new', 'Create new')
            ))
        );

        $params[DUPX_Params_Manager::PARAM_OTHER_CONFIG] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_OTHER_CONFIG,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'      => $params[DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE]->getValue() ? 'original' : 'new',
            'acceptValues' => array(
                'new',
                'original',
                'nothing'
            )
            ),
            array(
            'label'          => 'Other Configurations (web.config, user.ini):',
            'wrapperClasses' => 'medium',
            'status'         => function ($paramObj) {
                if (
                    DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_RESTORE_BACKUP_MODE) ||
                    DUPX_InstallerState::isAddSiteOnMultisite()
                ) {
                    return DUPX_Param_item_form::STATUS_INFO_ONLY;
                } else {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                }
            },
            'options' => array(
                new DUPX_Param_item_form_option('nothing', 'Do nothing'),
                new DUPX_Param_item_form_option('original', 'Retain original'),
                new DUPX_Param_item_form_option('new', 'Reset')
            )
            )
        );
    }

    /**
     *
     * @param DUPX_Param_item[] $params
     */
    public static function updateParamsAfterOverwrite(&$params)
    {
        if ($params[DUPX_Params_Manager::PARAM_INST_TYPE]->getValue() == DUPX_InstallerState::INSTALL_NOT_SET) {
            $acceptValues = $params[DUPX_Params_Manager::PARAM_INST_TYPE]->getAcceptValues();
            if (in_array(DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN, $acceptValues)) {
                $params[DUPX_Params_Manager::PARAM_INST_TYPE]->setValue(DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN);
            } elseif (in_array(DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER, $acceptValues)) {
                $params[DUPX_Params_Manager::PARAM_INST_TYPE]->setValue(DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER);
            } elseif (in_array(DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN, $acceptValues)) {
                $params[DUPX_Params_Manager::PARAM_INST_TYPE]->setValue(DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN);
            } elseif (in_array(DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER, $acceptValues)) {
                $params[DUPX_Params_Manager::PARAM_INST_TYPE]->setValue(DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER);
            } elseif (count($acceptValues) > 0) {
                $params[DUPX_Params_Manager::PARAM_INST_TYPE]->setValue($acceptValues[0]);
            }
        }
    }

    /**
     *
     * @return \DUPX_Param_item_form_option[]
     */
    protected static function getInstallTypeOptions()
    {
        $result = array();

        $option   = new DUPX_Param_item_form_option(DUPX_InstallerState::INSTALL_SINGLE_SITE, 'Install single site', array(__CLASS__, 'typeOptionsVisibility'));
        $option->setNote(array(__CLASS__, 'getInstallTypesNotes'));
        $result[] = $option;

        $option   = new DUPX_Param_item_form_option(DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN, 'Restore multisite network', array(__CLASS__, 'typeOptionsVisibility'));
        $option->setNote(array(__CLASS__, 'getInstallTypesNotes'));
        $result[] = $option;

        $option   = new DUPX_Param_item_form_option(DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER, 'Restore multisite network', array(__CLASS__, 'typeOptionsVisibility'));
        $option->setNote(array(__CLASS__, 'getInstallTypesNotes'));
        $result[] = $option;

        $option   = new DUPX_Param_item_form_option(DUPX_InstallerState::INSTALL_STANDALONE, 'Convert subsite to standalone', array(__CLASS__, 'typeOptionsVisibility'));
        $option->setNote(array(__CLASS__, 'getInstallTypesNotes'));
        $result[] = $option;

        $option   = new DUPX_Param_item_form_option(DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN, 'Install single site on multisite network', array(__CLASS__, 'typeOptionsVisibility'));
        $option->setNote(array(__CLASS__, 'getInstallTypesNotes'));
        $result[] = $option;

        $option   = new DUPX_Param_item_form_option(DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER, 'Install single site on multisite network', array(__CLASS__, 'typeOptionsVisibility'));
        $option->setNote(array(__CLASS__, 'getInstallTypesNotes'));
        $result[] = $option;

        $option   = new DUPX_Param_item_form_option(DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN, 'Install subsite on multisite network', array(__CLASS__, 'typeOptionsVisibility'));
        $option->setNote(array(__CLASS__, 'getInstallTypesNotes'));
        $result[] = $option;

        $option   = new DUPX_Param_item_form_option(DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER, 'Install subsite on multisite network', array(__CLASS__, 'typeOptionsVisibility'));
        $option->setNote(array(__CLASS__, 'getInstallTypesNotes'));
        $result[] = $option;

        return $result;
    }

    public static function typeOptionsVisibility(DUPX_Param_item_form_option $option)
    {
        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
        $isOwrMode     = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_INSTALLER_MODE) === DUPX_InstallerState::MODE_OVR_INSTALL;

        switch ($option->value) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
                if ($archiveConfig->mu_mode > 0) {
                    return DUPX_Param_item_form_option::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN:
                if ($archiveConfig->mu_mode != 1) {
                    return DUPX_Param_item_form_option::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER:
                if ($archiveConfig->mu_mode != 2) {
                    return DUPX_Param_item_form_option::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_STANDALONE:
                if ($archiveConfig->mu_mode == 0) {
                    return DUPX_Param_item_form_option::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
                if (!$isOwrMode || $archiveConfig->mu_mode > 0 || !$overwriteData['isMultisite'] || !$overwriteData['subdomain']) {
                    return DUPX_Param_item_form_option::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
                if (!$isOwrMode || $archiveConfig->mu_mode > 0 || !$overwriteData['isMultisite'] || $overwriteData['subdomain']) {
                    return DUPX_Param_item_form_option::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN:
                if (!$isOwrMode || $archiveConfig->mu_mode == 0 || !$overwriteData['isMultisite'] || !$overwriteData['subdomain']) {
                    return DUPX_Param_item_form_option::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER:
                if (!$isOwrMode || $archiveConfig->mu_mode == 0 || !$overwriteData['isMultisite'] || $overwriteData['subdomain']) {
                    return DUPX_Param_item_form_option::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_NOT_SET:
            default:
                throw new Exception('Install type not valid ' . $option->value);
        }

        $acceptValues = self::getInstallTypesAcceptValues();
        return in_array($option->value, $acceptValues) ? DUPX_Param_item_form_option::OPT_ENABLED : DUPX_Param_item_form_option::OPT_DISABLED;
    }

    /**
     *
     * @param array $overwriteData
     * @return int[]
     */
    public static function getInstallTypesAcceptValues()
    {
        $acceptValues  = array();
        $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        $isManaged     = DUPX_Custom_Host_Manager::getInstance()->isManaged();

        switch ($archiveConfig->mu_mode) {
            case 0:
                $acceptValues[] = DUPX_InstallerState::INSTALL_SINGLE_SITE;
                break;
            case 1:
                if (!$isManaged && !$archiveConfig->isPartialNetwork()) {
                    $acceptValues[] = DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN;
                }
                break;
            case 2:
                if (!$isManaged && !$archiveConfig->isPartialNetwork()) {
                    $acceptValues[] = DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER;
                }
                break;
        }

        if (
            $archiveConfig->mu_mode > 0 &&
            DUPX_License::multisitePlusEnabled() &&
            !DUPX_InstallerState::isRestoreBackup() &&
            !DUPX_InstallerState::isRecoveryMode()
        ) {
            $acceptValues[] = DUPX_InstallerState::INSTALL_STANDALONE;
        }

        if (
            DUPX_InstallerState::isMuImportEnabled() &&
            DUPX_InstallerState::isImportFromBackendMode() &&
            $overwriteData['isMultisite'] &&
            DUPX_License::multisitePlusEnabled() &&
            !DUPX_InstallerState::isRestoreBackup() &&
            !DUPX_InstallerState::isRecoveryMode()
        ) {
            if (
                version_compare($overwriteData['dupVersion'], DUPX_InstallerState::SUBSITE_IMPORT_DUP_MIN_VERSION, '>=') &&
                version_compare($overwriteData['wpVersion'], DUPX_InstallerState::SUBSITE_IMPORT_WP_MIN_VERSION, '>=')
            ) {
                if ($archiveConfig->mu_mode == 0) {
                    if ($overwriteData['subdomain']) {
                        $acceptValues[] = DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN;
                    } else {
                        $acceptValues[] = DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER;
                    }
                } else {
                    if ($overwriteData['subdomain']) {
                        $acceptValues[] = DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN;
                    } else {
                        $acceptValues[] = DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER;
                    }
                }
            } else {
                $msg = "The option to import the site into the multisite network has been disabled " .
                    "since it's only available for <b>Duplicator " . DUPX_InstallerState::SUBSITE_IMPORT_DUP_MIN_VERSION . " +</b> " .
                    "and Wordpress <b>" . DUPX_InstallerState::SUBSITE_IMPORT_WP_MIN_VERSION . " +</b>.<br>";
                $msg .= " To overcome the issue please update Duplicator Pro and/or Wordpress to the most recent version.";

                $noticeManager = DUPX_NOTICE_MANAGER::getInstance();
                $noticeManager->addNextStepNotice(array(
                    'shortMsg'    => 'Import site into network disabled',
                    'level'       => DUPX_NOTICE_ITEM::NOTICE,
                    'longMsg'     => $msg,
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML
                    ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'import-site-into-network-disabled');
            }
        }

        return $acceptValues;
    }

    public static function getInstallTypesNotes(DUPX_Param_item_form_option $option)
    {
        switch ($option->value) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER:
                return '';
            case DUPX_InstallerState::INSTALL_STANDALONE:
                return DUPX_License::getLicenseNote(DUPX_License::TYPE_BUSINESS_GOLD);
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER:
                $notes = array();

                $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
                if (!DUPX_InstallerState::isImportFromBackendMode()) {
                    $notes[] = 'This functionality is active only in the Drag&Drop import.';
                } else {
                    if (
                        !isset($overwriteData['dupVersion']) ||
                        version_compare($overwriteData['dupVersion'], DUPX_InstallerState::SUBSITE_IMPORT_DUP_MIN_VERSION, '<')
                    ) {
                        $notes[] = 'Duplicator ' . DUPX_InstallerState::SUBSITE_IMPORT_DUP_MIN_VERSION . '+ is required on current multisite to enabled this function.';
                    }

                    if (
                        !isset($overwriteData['wpVersion']) ||
                        version_compare($overwriteData['wpVersion'], DUPX_InstallerState::SUBSITE_IMPORT_DUP_MIN_VERSION, '<')
                    ) {
                        $notes[] = 'Wordpress ' . DUPX_InstallerState::SUBSITE_IMPORT_WP_MIN_VERSION . '+ is required on current multisite to enabled this function.';
                    }

                    if (empty($notes) && !DUPX_InstallerState::isMuImportEnabled()) {
                        $notes[] = 'To enable this option see Settings > General > Beta Feathures > MU Subsite Import';
                    }
                }

                if (strlen($licenseNote = DUPX_License::getLicenseNote(DUPX_License::TYPE_BUSINESS_GOLD)) > 0) {
                    $notes[] = $licenseNote;
                }

                return implode('<br>', $notes);
            case DUPX_InstallerState::INSTALL_NOT_SET:
            default:
                throw new Exception('Install type not valid ' . $option->value);
        }
    }
}
