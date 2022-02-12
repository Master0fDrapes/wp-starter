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
final class DUPX_Params_Descriptor_users implements DUPX_Interface_Params_Descriptor
{

    /**
     *
     * @param DUPX_Param_item[] $params
     */
    public static function init(&$params)
    {

        $params[DUPX_Params_Manager::PARAM_KEEP_TARGET_SITE_USERS] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_KEEP_TARGET_SITE_USERS,
            DUPX_Param_item_form::TYPE_INT,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'          => 0,
            'sanitizeCallback' => function ($value) {
                if (DUPX_InstallerState::isAddSiteOnMultisite()) {
                    return 0;
                }
                // disable keep users for some db actions
                switch (DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_DB_ACTION)) {
                    case DUPX_DBInstall::DBACTION_CREATE:
                    case DUPX_DBInstall::DBACTION_MANUAL:
                    case DUPX_DBInstall::DBACTION_ONLY_CONNECT:
                        return 0;
                    case DUPX_DBInstall::DBACTION_EMPTY:
                    case DUPX_DBInstall::DBACTION_REMOVE_ONLY_TABLES:
                    case DUPX_DBInstall::DBACTION_RENAME:
                        return (int) $value;
                }
            },
            'validateCallback' => function ($value) {
                if ($value == 0) {
                    return true;
                }
                foreach (DUPX_Params_Descriptor_users::getKeepUsersByParams() as $user) {
                    if ($value == $user['id']) {
                        return true;
                    }
                }
                return false;
            }
            ),
            array(
            'status' => function () {
                if (
                    DUPX_InstallerState::getInstance()->getMode() !== DUPX_InstallerState::MODE_OVR_INSTALL ||
                    DUPX_MU::newSiteIsMultisite() ||
                    DUPX_InstallerState::isRestoreBackup()
                ) {
                    return DUPX_Param_item_form::STATUS_SKIP;
                }

                if (
                    DUPX_InstallerState::isAddSiteOnMultisite() ||
                    count(DUPX_Params_Descriptor_users::getKeepUsersByParams()) === 0
                ) {
                    return DUPX_Param_item_form::STATUS_DISABLED;
                } else {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                }
            },
                                            'label'   => 'Keep Users:',
                                            'options' => function ($item) {
                                                $result   = array();
                                                $result[] = new DUPX_Param_item_form_option(0, ' - DISABLED - ');
                                                foreach (DUPX_Params_Descriptor_users::getKeepUsersByParams() as $userData) {
                                                    $result[] = new DUPX_Param_item_form_option($userData['id'], $userData['user_login']);
                                                }
                                                return $result;
                                            },
                                            'wrapperClasses' => array('revalidate-on-change'),
                                            'subNote'        => 'Keep users of the current site and eliminate users of the original site.<br>' .
                                            '<b>Assigns all pages, posts, media and custom post types to the selected user.</b>'
            )
        );

        $params[DUPX_Params_Manager::PARAM_CONTENT_OWNER] = new DUPX_Param_item_form(
            DUPX_Params_Manager::PARAM_CONTENT_OWNER,
            DUPX_Param_item_form::TYPE_INT,
            DUPX_Param_item_form::FORM_TYPE_SELECT,
            array(
            'default'          => 0,
            'sanitizeCallback' => function ($value) {
                return DUPX_InstallerState::isAddSiteOnMultisite() ? $value : 0;
            },
            'validateCallback'                                => function ($value) {
                if (!DUPX_InstallerState::isAddSiteOnMultisite()) {
                    return true;
                }
                if ($value == 0) {
                    return false;
                }
                foreach (DUPX_Params_Descriptor_users::getContentOwnerUsres() as $user) {
                    if ($value == $user['id']) {
                        return true;
                    }
                }
                return false;
            },
            'invalidMessage' => "When importing into a multisite you must select a user from the multisite that will own " .
            "all the posts and pages of the imported site."
            ),
            array(
            'status' => function () {
                if (!DUPX_InstallerState::isAddSiteOnMultisiteAvaiable()) {
                    return DUPX_Param_item_form::STATUS_SKIP;
                }

                if (count(DUPX_Params_Descriptor_users::getContentOwnerUsres()) === 0) {
                    return DUPX_Param_item_form::STATUS_DISABLED;
                } else {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                }
            },
                                                                            'label'   => 'Content Author:',
                                                                            'options' => function ($item) {
                                                                                $result   = array();
                                                                                foreach (DUPX_Params_Descriptor_users::getContentOwnerUsres() as $userData) {
                                                                                    $result[] = new DUPX_Param_item_form_option($userData['id'], $userData['user_login']);
                                                                                }
                                                                                return $result;
                                                                            },
                                                                            'wrapperClasses' => array('revalidate-on-change'),
                                                                            'subNote'        => '<b>Author of all imported pages, posts, media and custom post types will be set to this user.</b><br>' .
                                                                            'All users of impoted site will be eliminated.</b>'
            )
        );

        $params[DUPX_Params_Manager::PARAM_USERS_PWD_RESET] = new DUPX_Param_item_form_users_pass_reset(
            DUPX_Params_Manager::PARAM_USERS_PWD_RESET,
            DUPX_Param_item_form_users_pass_reset::TYPE_ARRAY_STRING,
            DUPX_Param_item_form_users_pass_reset::FORM_TYPE_USERS_PWD_RESET,
            array(// ITEM ATTRIBUTES
            'default' => array_map(function ($value) {
                return '';
            }, DUPX_ArchiveConfig::getInstance()->getUsersLists()),
            'sanitizeCallback'                                  => array('DupProSnapLibUtil', 'sanitize_non_stamp_chars_newline_and_trim'),
            'validateCallback'                                  => function ($value) {
                return strlen($value) == 0 || strlen($value) >= DUPX_Constants::MIN_NEW_PASSWORD_LEN;
            },
            'invalidMessage' => 'can\'t have less than ' . DUPX_Constants::MIN_NEW_PASSWORD_LEN . ' characters'
            ),
            array(// FORM ATTRIBUTES
            'status' => function ($paramObj) {
                if (DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_KEEP_TARGET_SITE_USERS) > 0) {
                    return DUPX_Param_item_form::STATUS_DISABLED;
                } else {
                    return DUPX_Param_item_form::STATUS_ENABLED;
                }
            },
                                                        'label'   => 'Existing user reset password:',
                                                        'classes' => 'strength-pwd-check',
                                                        'attr'    => array(
                                                        'title'       => DUPX_Constants::MIN_NEW_PASSWORD_LEN . ' characters minimum',
                                                        'placeholder' => "Reset user password"
                                                     )
            )
        );
    }

    /**
     *
     * @return array
     */
    public static function getKeepUsersByParams()
    {
        $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);

        if (!empty($overwriteData['adminUsers'])) {
            return $overwriteData['adminUsers'];
        }

        return array();
    }

    /**
     *
     * @param null|int $subsiteId if null get current select subsite overwrite
     * @return array // restur list of content owner users
     */
    public static function getContentOwnerUsres($subsiteId = null)
    {
        $result        = array();
        $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);

        if (is_null($subsiteId)) {
            $owrIdId = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_SUBSITE_OVERWRITE_ID);
        } else {
            $owrIdId = $subsiteId;
        }

        if ($owrIdId > 0 && !empty($overwriteData['subsites'])) {
            foreach ($overwriteData['subsites'] as $subsite) {
                if ($subsite['id'] == $owrIdId) {
                    $result = $subsite['adminUsers'];
                    break;
                }
            }
        }

        if (empty($result) && !empty($overwriteData['adminUsers'])) {
            $result = $overwriteData['adminUsers'];
        }

        if (isset($overwriteData['loggedUser'])) {
            // insert the logged in user always at the beginning of the array
            foreach ($result as $key => $user) {
                if ($user['id'] == $overwriteData['loggedUser']['id']) {
                    unset($result[$key]);
                    break;
                }
            }
            array_unshift($result, $overwriteData['loggedUser']);
        }

        return $result;
    }

    /**
     *
     * @return int
     */
    public static function getKeepUserId()
    {
        $paramsManager = DUPX_Params_Manager::getInstance();
        if (DUPX_InstallerState::isAddSiteOnMultisite()) {
            return $paramsManager->getValue(DUPX_Params_Manager::PARAM_CONTENT_OWNER);
        } else {
            return $paramsManager->getValue(DUPX_Params_Manager::PARAM_KEEP_TARGET_SITE_USERS);
        }
    }

    /**
     *
     * @param DUPX_Param_item[] $params
     */
    public static function updateParamsAfterOverwrite(&$params)
    {
    }
}
