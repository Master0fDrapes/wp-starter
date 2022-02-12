<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUPX_REST
{
    /**
     * 
     */
    const DUPLICATOR_NAMESPACE = 'duplicator/v1/';

    /**
     * 
     * @var string
     */
    private $nonce = false;

    /**
     * 
     * @var string
     */
    private $url = false;

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
        $paramsManager = DUPX_Params_Manager::getInstance();
        $overwriteData = $paramsManager->getValue(DUPX_Params_Manager::PARAM_OVERWRITE_SITE_DATA);

        if (
            is_array($overwriteData) &&
            isset($overwriteData['restUrl']) &&
            strlen($overwriteData['restUrl']) > 0 &&
            isset($overwriteData['restNonce']) &&
            strlen($overwriteData['restNonce']) > 0
        ) {
            $this->url   = DupProSnapLibIOU::untrailingslashit($overwriteData['restUrl']);
            $this->nonce = $overwriteData['restNonce'];
        }
    }

    public function checkRest($reset = false)
    {
        $success = null;
        if (is_null($success) || $reset) {
            if ($this->nonce === false) {
                $success = false;
            } else {
                $response = Requests::get($this->getRestUrl());
                if (($success  = $response->success) == false) {
                    DUPX_Log::info('FAIL REST CHECK ON URL:' . $this->getRestUrl());
                    DUPX_Log::info(DUPX_Log::varToString($response));
                }
            }
        }
        return $success;
    }

    public function getVersions()
    {
        if (!$this->checkRest()) {
            return false;
        }

        $response = Requests::get($this->getRestUrl('versions'), array(), $this->requestAuthOptions());
        if (!$response->success) {
            return false;
        }

        if (($result = json_decode($response->body)) === null) {
            return false;
        }

        return $result;
    }

    /**
     * 
     * @param string $subSlug
     * @param string $blogTitle
     * @param int $adminUser
     * @param string $errorMessage
     * @return boolean|array // false on fail of array of new subsite info
     * 
     */
    public function createNewSubSite($subSlug, $blogTitle, $adminUser, &$errorMessage = '')
    {
        // http://www.duplicatorpronetwork.loc/wp-json/duplicator/v1/multisite/subsite/new?subSlug=abcde2&blogTitle=testnew&adminUser=1&_wpnonce=be3a2b05d3

        if (!$this->checkRest()) {
            DUPX_Log::info('REST isn\'t elable');
            return false;
        }

        $response = Requests::post(
                $this->getRestUrl('multisite/subsite/new'),
                                  array(
                ),
                                  array(
                    'subSlug'   => $subSlug,
                    'blogTitle' => $blogTitle,
                    'adminUser' => $adminUser
                ),
                                  $this->requestAuthOptions()
        );
        if (!$response->success) {
            DUPX_Log::info('REST RESPONSE: ' . DUPX_Log::varToString($response));
            return false;
        }

        if (($result = json_decode($response->body)) === null) {
            DUPX_Log::info('can\'t decode json ' . $response->body);
            return false;
        }

        if (!$result->success) {
            $errorMessage = $result->message;
            return false;
        }

        return (array) $result->subsiteInfo;
    }

    private function requestAuthOptions()
    {
        return array(
            'auth' => new DUPX_REST_AUTH($this->nonce)
        );
    }

    private function getRestUrl($subPath = '')
    {
        return $this->url ? $this->url . '/' . self::DUPLICATOR_NAMESPACE . $subPath : '';
    }
}
