<?php

/**
 * Installer params manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @final class DUPX_Params_Descriptor_Multisite package SC\DUPX\U
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class DUPX_Params_Descriptor_multisite implements DUPX_Interface_Params_Descriptor
{

    /**
     *
     * @param DUPX_Param_item[] $params
     */
    public static function init(&$params)
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();

        $params[DUPX_Params_Manager::PARAM_SUBSITE_ID] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_SUBSITE_ID,
            DUPX_Param_item_form::TYPE_INT,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'      => -1,
            'acceptValues' => array(__CLASS__, 'getSubSiteIdsAcceptValues')
            ),
            array(
            'status' => function ($paramObj) {
                if (
                    DUPX_InstallerState::isInstType(
                        array(
                            DUPX_InstallerState::INSTALL_STANDALONE,
                            DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN,
                            DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER
                        )
                    )
                ) {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                } else {
                    return DUPX_Param_item_form::STATUS_DISABLED;
                }
            },
            'label'          => 'Subsite:',
            'wrapperClasses' => array('revalidate-on-change'),
            'options'        => array(__CLASS__, 'getSubSiteIdsOptions'),
            )
        );

        $params[DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_ID] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_ID,
            DUPX_Param_item_form::TYPE_INT,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'          => -1,
            'validateCallback' => array(__CLASS__, 'overwriteSubSiteIdValidation'),
            'invalidMessage'   => 'Select a valid subsite overwrite value'
            ),
            array(
            'status' => function ($paramObj) {
                if (!DUPX_InstallerState::isAddSiteOnMultisiteAvaiable()) {
                    return DUPX_Param_item_form::STATUS_SKIP;
                }

                return DUPX_InstallerState::isAddSiteOnMultisite() ? DUPX_Param_item_form::STATUS_ENABLED : DUPX_Param_item_form::STATUS_DISABLED;
            },
            'label'          => 'Action:',
            'wrapperClasses' => array('revalidate-on-change'),
            'options'        => array(__CLASS__, 'getOverwriteSubsiteIdsOptions')
            )
        );

        $params[DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_NEW_SLUG] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_NEW_SLUG,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_TEXT,
            array(
            'default'          => '',
            'sanitizeCallback' => function ($value) {
                $result = DupProSnapLibUtil::sanitize_non_stamp_chars_newline_and_trim($value);
                return preg_replace('/[\s"\'\\\\\/&?#,\.:;]+/m', '', $result);
            },
            'validateCallback'                                             => function ($value) {
                if (
                    !DUPX_InstallerState::isAddSiteOnMultisite() ||
                    DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_ID) > 0
                ) {
                    return true;
                }

                if (strlen($value) == 0) {
                    return false;
                }

                $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);

                if (!isset($overwriteData['subsites'])) {
                    return true;
                }

                $parseUrl       = DupProSnapLibURLU::parseUrl($overwriteData['urls']['home']);
                $mainSiteDomain = DupProSnapLibURLU::wwwRemove($parseUrl['host']);
                $mainSitePath   = DupProSnapLibIOU::trailingslashit($parseUrl['path']);
                $subdomain      = (isset($overwriteData['subdomain']) && $overwriteData['subdomain']);

                foreach ($overwriteData['subsites'] as $subsite) {
                    $subsite['domain'] . $subsite['path'];

                    if ($subdomain) {
                        if (strcmp($value . '.' . $mainSiteDomain, $subsite['domain']) === 0) {
                            return false;
                        }
                    } else {
                        if (strcmp($mainSitePath . $value, DupProSnapLibIOU::untrailingslashit($subsite['path'])) === 0) {
                            return false;
                        }
                    }
                }

                return true;
            },
            'invalidMessage' => 'The new subsite slug can\'t be empty and cannot belong to an existing subsite'
            ),
                                   array(
            'status' => function (DUPX_Param_item_form $param) {
                if (!DUPX_InstallerState::isAddSiteOnMultisiteAvaiable()) {
                    return DUPX_Param_item_form::STATUS_SKIP;
                }

                if (!DUPX_InstallerState::isAddSiteOnMultisite()) {
                    return DUPX_Param_item_form::STATUS_DISABLED;
                }

                if (DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_ID) > 0) {
                    return DUPX_Param_item_form::STATUS_DISABLED;
                }

                return DUPX_Param_item_form::STATUS_ENABLED;
            },
            'label'  => 'New Subsite URL:',
            'prefix' => function (DUPX_Param_item_form $param) {
                $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
                $urlNew        = $overwriteData['urls']['home'];
                $parseUrl      = DupProSnapLibURLU::parseUrl($urlNew);

                $result = array('type' => 'label');
                if (isset($overwriteData['subdomain']) && $overwriteData['subdomain']) {
                    $result['label'] = $parseUrl['scheme'] . '://';
                } else {
                    $result['label']          = $urlNew . '/';
                    $result['attrs']['title'] = $result['label'];
                }
                return $result;
            },
            'postfix' => function (DUPX_Param_item_form $param) {
                $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
                if (!isset($overwriteData['subdomain']) || !$overwriteData['subdomain']) {
                    return array('type' => 'none');
                }
                $urlNew   = $overwriteData['urls']['home'];
                $parseUrl = DupProSnapLibURLU::parseUrl($urlNew);

                $result                   = array(
                    'type'  => 'label',
                    'label' => '.' . \DupProSnapLibURLU::wwwRemove($parseUrl['host'])
                );
                $result['attrs']['title'] = $result['label'];
                return $result;
            },
            'wrapperClasses' => array('revalidate-on-change')
            )
        );

        $params[DUPX_Params_Manager::PARAM_REPLACE_MODE] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_REPLACE_MODE,
            DUPX_Param_item_form::TYPE_STRING,
            DUPX_Param_item_form::FORM_TYPE_RADIO,
            array(
            'default'      => 'legacy',
            'acceptValues' => array(
                'legacy',
                'mapping'
            )),
            array(
            'label'   => 'Replace Mode:',
            'options' => array(
                new DUPX_Param_item_form_option('legacy', 'Standard', DUPX_Param_item_form_option::OPT_ENABLED, array('title' => 'Set the files current date time to now')),
                new DUPX_Param_item_form_option('mapping', 'Mapping', DUPX_Param_item_form_option::OPT_ENABLED, array('title' => 'Keep the files date time the same'))
            ))
        );

        $params[DUPX_Params_Manager::PARAM_MU_REPLACE] = new DUPX_Param_item_form_urlmapping(
            DUPX_Params_Manager::PARAM_MU_REPLACE,
            DUPX_Param_item_form_urlmapping::TYPE_ARRAY_STRING,
            DUPX_Param_item_form_urlmapping::FORM_TYPE_URL_MAPPING,
            array(
            'default' => $archive_config->getNewUrlsArrayIdVal()),
            array()
        );

        $params[DUPX_Params_Manager::PARAM_MULTISITE_CROSS_SEARCH] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_MULTISITE_CROSS_SEARCH,
            DUPX_Param_item_form::TYPE_BOOL,
            DUPX_Param_item_form::FORM_TYPE_CHECKBOX,
            array(
            'default' => (count($archive_config->subsites) <= MAX_SITES_TO_DEFAULT_ENABLE_CORSS_SEARCH)
            ),
                                array(
            'status' => function ($paramObj) {
                if (DUPX_MU::newSiteIsMultisite()) {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                } else {
                    return DUPX_Param_item_form::STATUS_SKIP;
                }
            },
            'label'         => 'Database search:',
            'checkboxLabel' => 'Cross-search between the sites of the network.'
            )
        );
    }

    /**
     *
     * @param DUPX_Param_item[] $params
     */
    public static function updateParamsAfterOverwrite(&$params)
    {
        // empty
    }

    /**
     *
     * @return \DUPX_Param_item_form_option[]
     */
    public static function getSubSiteIdsOptions()
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $options        = array();
        foreach ($archive_config->subsites as $subsite) {
            $optStatus = !DUPX_InstallerState::isImportFromBackendMode() || (count($subsite->filtered_tables) === 0 && count($subsite->filtered_paths) === 0) ? DUPX_Param_item_form_option::OPT_ENABLED : DUPX_Param_item_form_option::OPT_DISABLED;
            $label     = $subsite->blogname . ' [' . $subsite->domain . $subsite->path . ']';
            $options[] = new DUPX_Param_item_form_option($subsite->id, $label, $optStatus);
        }
        return $options;
    }

    /**
     *
     * @return int[]
     */
    public static function getSubSiteIdsAcceptValues()
    {
        $archive_config = DUPX_ArchiveConfig::getInstance();
        $acceptValues   = array(-1);
        foreach ($archive_config->subsites as $subsite) {
            if (!DUPX_InstallerState::isImportFromBackendMode() || (count($subsite->filtered_tables) === 0 && count($subsite->filtered_paths) === 0)) {
                $acceptValues[] = $subsite->id;
            }
        }
        return $acceptValues;
    }

    /**
     *
     * @return \DUPX_Param_item_form_option[]
     */
    public static function getOverwriteSubsiteIdsOptions()
    {
        $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
        $options       = array();

        if (!is_array($overwriteData) || !isset($overwriteData['subsites'])) {
            return $options;
        }

        $usersOptions = DUPX_Params_Descriptor_users::getContentOwnerUsres(0);
        $options[]    = new DUPX_Param_item_form_option(
            0,
            'Add as a new subsite',
            DUPX_Param_item_form_option::OPT_ENABLED,
            array(
            'data-keep-users' => DupProSnapJsonU::wp_json_encode($usersOptions)
            )
        );

        foreach ($overwriteData['subsites'] as $subsite) {
            $label        = $subsite['blogname'] . ' [' . $subsite['domain'] . $subsite['path'] . '] (overwrite coming soon)';
            $usersOptions = DUPX_Params_Descriptor_users::getContentOwnerUsres($subsite['id']);
            $options[]    = new DUPX_Param_item_form_option(
                $subsite['id'],
                $label,
                DUPX_Param_item_form_option::OPT_DISABLED,
                array(
                'data-keep-users' => DupProSnapJsonU::wp_json_encode($usersOptions)
                )
            );
        }

        return $options;
    }

    /**
     *
     * @return int[]
     */
    public static function overwriteSubSiteIdValidation($value)
    {
        if (!DUPX_InstallerState::isAddSiteOnMultisite()) {
            return true;
        }

        if ($value < 0) {
            return false;
        }

        if ($value == 0) {
            return true;
        }

        $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
        if (!is_array($overwriteData) || !isset($overwriteData['subsites'])) {
            return false;
        }

        foreach ($overwriteData['subsites'] as $subsite) {
            if ($value == $subsite['id']) {
                return true;
            }
        }

        return false;
    }
}
