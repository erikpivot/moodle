<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library code used by the service control interfaces.
 *
 * @package   local_course_bundles
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

//require_once __DIR__ . '/locallib.php';

// include third party libraries
require_once __DIR__ . '/../../vendor/autoload.php';
use Automattic\WooCommerce\Client;

/**
 * Getting a list of all bundles
 * 
 * @param number $limitfrom
 * @param number $limitnum
 * @return array
 */
function local_course_bundles_get_list_records($limitfrom = 0, $limitnum = 0) {
    global $DB;
    
    $listbundles = $DB->get_records('local_course_bundles', null, 'name', '*', $limitfrom, $limitnum);
    
    return $listbundles;
}

/**
 * Getting information about the bundle
 * 
 * @param number $bundleid
 * @return object
 */
function local_course_bundles_get_record($bundleid = 0) {
    global $DB;
    
    $bundlerecord = $DB->get_record('local_course_bundles', array('id' => $bundleid), '*', MUST_EXIST);
    // interperet the description of the html editor
    $bundlerecord->description = array('text' => $bundlerecord->description, 'format' => 1);
    
    return $bundlerecord;
}

/**
 * Clear the database table
 */
function local_course_bundles_remove_list_records() {
    global $DB;
    
    $DB->delete_records('local_course_bundles', null);
}

/**
 * Delete the record
 * 
 * @param number $bundleid
 */
function local_course_bundles_remove_record($bundleid = 0) {
    global $DB;
    // get the product id
    $product = $DB->get_record('local_course_bundles', array('id' => $bundleid), "ecommproductid");
    // Make the curl request
    $config = get_config('local_sales_front');
    $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/" . $product->ecommproductid . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    file_put_contents(__DIR__ . '/delete_product_result.txt', $res . "\n", FILE_APPEND);
    $DB->delete_records('local_course_bundles', array('id' => $bundleid));
    //local_course_bundles_events::bundle_deleted($bundleid);
}

/**
 * Update the record in the database
 * 
 * @param object $data
 * @param boolean $insert
 * @return boolean
 */
function local_course_bundles_update_record($data, $insert = true) {
    global $DB;
    
    // to attach to course description
    $sel_courses = '<br /><br />Courses Included:<br /><ul>';
    
    // grab the course names
    $all_courses = explode(",", $data->courses);
    $courses = array();
    $product_cats = [];
    foreach ($all_courses as $course_id) {
        $course_info = $DB->get_record('course', array('idnumber' => $course_id));
        $courses[] = array(
            'id' => $course_info->id,
            'fullname' => $course_info->fullname,
            'summary' => $course_info->summary,
            'credithrs' => $course_info->credithrs,
            'idnumber' => $course_id,
            'removed' => false
        );
        $sel_courses .= '<li>' . $course_info->fullname . '</li>';
        // get the product category ids
        /*
        $tag_info = \core_tag_tag::get_item_tags_array('core', 'course', $course_info->id);
        foreach($tag_info as $key => $value) {
            $cat_res = $DB->get_record('tag', array('id' => $key), 'ecommproductcat');
            $product_cats[] = [
                'id' => $cat_res->ecommproductcat
            ];
        }
        */
    }
    $sel_courses .= '</ul>';
    
    // rebuild the data description property to the text of the html editor
    $data->description = $data->description['text'];

    if (boolval($insert)) {
        file_put_contents(__DIR__ . '/create_product_result.txt', print_r($data, true) . "\n", FILE_APPEND);
        $result = $DB->insert_record('local_course_bundles', $data, true, false);
        //$product_cats[] = ['id' => 45]; // Bundle Category
        processStateCategoryIds($data->state, $product_cats);
        file_put_contents(__DIR__ . '/create_product.txt', "Product Cats: " . print_r($product_cats, true) . "\n", FILE_APPEND);
        // create the woocommerce data object for the new product
        $woo_data = [
            'name' => $data->name,
            'type' => 'simple',
            'regular_price' => (string)$data->price,
            'description' => buildDescription($courses),
            'short_description' => $data->description,
            'categories' => $product_cats,
            'sold_individually' => true,
            'meta_data' => [
                [
                    'key' => 'dc_credit_hours',
                    'value' => $data->credithrs
                ],
                [
                    'key' => 'dc_course_ids',
                    'value' => $data->courses
                ]
            ]
        ];
        
        // Make the curl request
        $config = get_config('local_sales_front');
        $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($woo_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        file_put_contents(__DIR__ . '/create_product_result.txt', $res . "\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/create_product_result.txt', $http_status . "\n", FILE_APPEND);
        if (201 == $http_status) {
            // save the product id and course number to the course record
            $product = json_decode($res);
            $up_obj = new stdClass();
            $up_obj->id = $result;
            $up_obj->ecommproductid = $product->id;
            file_put_contents(__DIR__. '/create_product_result.txt', print_r($up_obj, true) . "\n", FILE_APPEND);
            $DB->update_record('local_course_bundles', $up_obj);
            
            // update the course short descriptions
            //updateCourseShortDescripts($result, $courses);
        }
        //local_course_bundles_events::bundle_added($result);
    } else {
        // get the previously saved course id numbers before updating
        $previds = $DB->get_record('local_course_bundles', array('id' => $data->id), 'courses');
        if (!empty($previds)) {
            // compare the ids found to the currently selected course ids
            $prev_courses = explode(",", $previds->courses);
            foreach($prev_courses as $this_course) {
                if (array_search($this_course, array_column($courses, 'idnumber')) === false) {
                    // add to the courses array to be removed
                    $courses[] = array(
                        'idnumber' => $this_course,
                        'removed' => true
                    );
                }
            }
        }
        $result = $DB->update_record('local_course_bundles', $data, false);
        //$product_cats[] = ['id' => 45]; // Bundle Category
        processStateCategoryIds($data->state, $product_cats);
        file_put_contents(__DIR__ . '/update_product.txt', "Product Cats: " . print_r($product_cats, true) . "\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/update_product.txt', "Data: " . print_r($data, true) . "\n", FILE_APPEND);
        // create the woocommerce data object for the new product
        $woo_data = [
            'name' => $data->name,
            'type' => 'simple',
            'regular_price' => (string)$data->price,
            'description' => buildDescription($courses),
            'short_description' => $data->description,
            'categories' => $product_cats,
            'meta_data' => [
                [
                    'key' => 'dc_credit_hours',
                    'value' => $data->credithrs
                ],
                [
                    'key' => 'dc_course_ids',
                    'value' => $data->courses
                ]
            ]
        ];
        
        // Make the curl request
        $config = get_config('local_sales_front');
        file_put_contents(__DIR__ . '/update_product_result.txt', $config->ecommerce_url . "/wp-json/wc/v2/products/" . $data->ecommproductid . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret . "\n", FILE_APPEND);
        $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/" . $data->ecommproductid . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($woo_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        file_put_contents(__DIR__ . '/update_product_result.txt', $res . "\n", FILE_APPEND);
        //local_course_bundles_events::bundle_updated($data->id);
        // update the course short descriptions
        //updateCourseShortDescripts($data->id, $courses);
    }
    
    return boolval($result);
}

function processStateCategoryIds($state, &$product_cats) {
    global $DB;
        
    // get the category ids
    $state_settings = $DB->get_records('local_state_settings');
    $state_cats = array();
    foreach($state_settings as $setting) {
        $state_cats[$setting->state] = $setting->ecommbundlecatid;
    }
    
    // which id's need to be returned?
    $product_cats[] = ['id' => $state_cats[$state]];
    
}
    
function setPACE(&$course_info, $pace_number) {
    // grab the pace settings
    global $DB;
    $pace_settings = $DB->get_records_select('config', "value = '1' AND name LIKE 'pacestates_pace%'", null, '', 'name');
    file_put_contents(__DIR__ . 'get_pace_states.txt', print_r($pace_settings, true));
    // assign the number for the found states
    foreach ($pace_settings as $pace) {
        $this_state = substr($pace->name, (strlen($pace->name) - 2), 2);
        $c_idx = $this_state . 'approvalno';
        $course_info->$c_idx = $pace_number;
    }
}

/**
 * @param object $courses - contains information of all assigned courses
 * @return string - the long description for eCommerce
 */
function buildDescription($courses) {
    global $DB;
    $descr_str = '';
    
    // go through each course
    foreach ($courses as $course_info) {
        $descr_str .= '<div class="bundle-course-block">';
        $descr_str .= '<p class="bundle-title-line"><span class="bundle-course-title">' . $course_info['fullname'] . '</span> - <span class="bundle-course-credit-hrs">Credit Hours: ' . $course_info['credithrs'] . '</span>';
        // get the categories associated with the course
        $tag_info = \core_tag_tag::get_item_tags_array('core', 'course', $course_info['id']);
        //file_put_contents(__DIR__ . '/tag_info.txt', "Course: " . $course_info['fullname'] . "\n" . print_r($tag_info, true), FILE_APPEND);
        foreach($tag_info as $key => $value) {
            $descr_str .= '<span class="course-category">' . $value . '</span>';
        }
        $descr_str .= '</p>';
        $descr_str .= '<div class="bundle-course-summary">' . $course_info['summary'] . '</div>';
        $descr_str .= '</div>';
    }
    return $descr_str;
}

function updateCourseShortDescripts($bundle_id, $courses) {
    global $DB;
    $config = get_config('local_sales_front');
    // loop through the individual courses and see if they are associated with
    // other bundles
    file_put_contents(__DIR__ . '/course_short_descripts.txt', print_r($courses, true), FILE_APPEND);
    foreach ($courses as $courseinfo) {
        $select = "courses LIKE '%" . $courseinfo['idnumber'] . "%' AND id != " . $bundle_id;
        $records = $DB->get_records_select('local_course_bundles', $select, array('id'));
        if (empty($records)) {
            // the course is not a part of another bundle
            // check if the short description needs to be removed or added
            if ($courseinfo['removed']) {
                $woo_data = [
                    'short_description' => ''
                ];
            } else {
                $woo_data = [
                    'short_description' => '<div class="has-bundle">This course is part of a bundle.</div>'
                ];
            }
            // get the ecommerce id for the course
            $course_rec = $DB->get_record('course', array('idnumber' => $courseinfo['idnumber']));
            if (!empty($course_rec)) {
                // send the update to the eCommerce site
                $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/" . $course_rec->productid . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($woo_data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $res = curl_exec($ch);
                curl_close($ch);
            }
        }
    }
}