<?php

/**
 * REST point to create new subsite
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\RESTPoints;

class SubsiteNew extends \Duplicator\Core\REST\AbstractRESTPoint
{

    protected function getRoute()
    {
        return '/multisite/subsite/new';
    }

    public function isEnable()
    {
        return is_multisite();
    }

    public function getMethods()
    {
        return array('GET', 'POST');
    }

    public function permission(\WP_REST_Request $request)
    {
        if (!current_user_can('import') || !check_ajax_referer('wp_rest', false, false)) {
            return new \WP_Error('rest_forbidden', esc_html__('You cannot execute this action.'));
        }
        return true;
    }

    protected function getArgs()
    {
        return array(
            'subSlug' => array(
                'required'          => true,
                'type'              => 'string',
                'description'       => 'Subsite subdomain o subfolder',
                'validate_callback' => function ($param, \WP_REST_Request $request, $key) {
                    $value = \DupProSnapLibUtil::sanitize_non_stamp_chars_newline_and_trim($param);
                    return strlen($value) > 0;
                }
            ),
            'blogTitle' => array(
                'required'          => true,
                'type'              => 'string',
                'description'       => 'Blog title',
                'validate_callback' => function ($param, \WP_REST_Request $request, $key) {
                    $value = \DupProSnapLibUtil::sanitize_non_stamp_chars_newline_and_trim($param);
                    return strlen($value) > 0;
                }
            ),
            'adminUser' => array(
                'required'          => true,
                'type'              => 'integer',
                'description'       => 'admin user, the id have to referr at existing user',
                'validate_callback' => function ($param, \WP_REST_Request $request, $key) {
                    if (!is_numeric($param)) {
                        return false;
                    }
                    return (get_userdata($param) !== false);
                }
            ),
        );
    }

    /**
     *
     * @global type $wp_version
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    protected function respond(\WP_REST_Request $request)
    {
        $response = array(
            'success'     => false,
            'message'     => '',
            'subsiteInfo' => false
        );

        try {
            if (!class_exists('WP_Network')) {
                throw new Exception('the current version of wordpress does not support this action.');
            }

            $networkId = function_exists('get_current_network_id') ? get_current_network_id() : 1;
            $wpNetwork = \WP_Network::get_instance($networkId);

            if (defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL) {
                $domain = $request->get_param('subSlug') . '.' . \DupProSnapLibURLU::wwwRemove($wpNetwork->domain);
                $path   = $wpNetwork->path;
            } else {
                $domain = $wpNetwork->domain;
                $path   = trailingslashit($wpNetwork->path) . $request->get_param('subSlug');
            }

            $newBlogId = wpmu_create_blog($domain, $path, $request->get_param('blogTitle'), $request->get_param('adminUser'));

            if ($newBlogId instanceof \WP_Error) {
                $response['success'] = false;
                throw new \Exception($newBlogId->get_error_message());
            }

            if (($subsiteInfo = \DUP_PRO_MU::getSubsiteInfoById($newBlogId)) == false) {
                throw new \Exception('Can\'t read new subsite info');
            }

            if (!is_user_member_of_blog(get_current_user_id(), $newBlogId)) {
                $result = add_user_to_blog($newBlogId, get_current_user_id(), 'administrator');
                if ($result instanceof \WP_Error) {
                    $response['success'] = false;
                    throw new \Exception($result->get_error_message());
                }
            }

            $response['success']     = true;
            $response['subsiteInfo'] = $subsiteInfo;

            return new \WP_REST_Response($response, 200);
        } catch (\Exception $e) {
            $exception = $e;
        } catch (\Error $e) {
            $exception = $e;
        }

        $response['success'] = false;
        $response['message'] = $exception->getMessage();

        return new \WP_REST_Response($response, 200);
    }
}
