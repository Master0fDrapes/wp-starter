<?php

/**
 * License class
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUPX_License
{
    const TYPE_UNLICENSED    = 0;
    const TYPE_PERSONAL      = 1;
    const TYPE_FREELANCER    = 2;
    const TYPE_BUSINESS_GOLD = 3;

    /**
     * Returns the license type this installer file is made of.
     *
     * @return obj	Returns an enum type of DUPX_License
     */
    public static function getType()
    {
        return max(self::getImporterLicense(), self::getInstallerLicense());
    }

    /**
     *
     * @return obj	Returns an enum type of DUPX_License
     */
    public static function getInstallerLicense()
    {
        return self::getTypeFromLimit(DUPX_ArchiveConfig::getInstance()->license_limit);
    }

    /**
     *
     * @return obj	Returns an enum type of DUPX_License
     */
    public static function getImporterLicense()
    {
        $overwriteData = DUPX_Params_Manager::getInstance()->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);
        return isset($overwriteData['dupLicense']) ? $overwriteData['dupLicense'] : self::TYPE_UNLICENSED;
    }

    /**
     *
     * @return obj	Returns an enum type of DUPX_License
     */
    protected static function getTypeFromLimit($limit)
    {
        if ($limit < 0) {
            return self::TYPE_UNLICENSED;
        } else if ($limit < 15) {
            return self::TYPE_PERSONAL;
        } else if ($limit < 500) {
            return self::TYPE_FREELANCER;
        } else {
            return self::TYPE_BUSINESS_GOLD;
        }
    }

    /**
     * 
     * @return bool
     */
    public static function multisitePlusEnabled()
    {
        return self::getType() == DUPX_License::TYPE_BUSINESS_GOLD;
    }

    /**
     * 
     * @param int $license
     * @param bool $article
     * @return string
     */
    public static function getLicenseToString($license = null, $article = false)
    {
        if (is_null($license)) {
            $license = self::getType();
        }

        switch ($license) {
            case self::TYPE_BUSINESS_GOLD:
                return ($article ? 'a ' : '') . 'Business or Gold';
            case self::TYPE_UNLICENSED:
                return ($article ? 'an ' : '') . 'unlicensed';
            case self::TYPE_PERSONAL:
                return ($article ? 'a ' : '') . 'Personal';
            case self::TYPE_FREELANCER:
                return ($article ? 'a ' : '') . 'Freelancer';
            default:
                return ($article ? 'an ' : '') . 'unknown license type';
        }
    }

    /**
     * 
     * @return string
     */
    public static function getLicenseNote($required)
    {
        if (self::getType() >= $required) {
            return '';
        }

        return 'Requires <b>' . self::getLicenseToString($required) . '</b> license. The effective license of this install is ' . self::getLicenseToString(null, false) . '.';
    }
}
