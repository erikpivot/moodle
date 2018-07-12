<?php
// This file is part of the sales front plugin
//
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
 * This plugin handles events to send to the sales front end
 *
 * @package    local
 * @subpackage sales_front
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sales_front;

// include third party libraries
require_once __DIR__ . '/../../../course/modlib.php';
require_once __DIR__ . '/../../../mod/customcert/classes/template.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
use Automattic\WooCommerce\Client;

defined('MOODLE_INTERNAL') || die();

class observer {
    
    public static function create_product(\core\event\course_created $event) {
        global $DB, $CFG;
        $eventdata = $event->get_data();
        $course = get_course($eventdata['objectid']);
        file_put_contents(__DIR__ . 'create_product.txt', print_r($course, true) . "\n", FILE_APPEND);
        file_put_contents(__DIR__ . 'create_product.txt', print_r($eventdata, true) . "\n", FILE_APPEND);
        
        // create a unique course id for reference from the front end site, if neccessary
        if (empty($course->idnumber)) {
            $course_no = date('Ymdhis') . "-" . $eventdata['objectid'];
        
            // update the course id number
            $course_obj = new \stdClass();
            $course_obj->id = $eventdata['objectid'];
            $course_obj->idnumber = $course_no;
            $DB->update_record('course', $course_obj);    
        }
        
        // action performed depends on if the new course is a revision
        if ($course->revisionno > 0) {
            // this is a revision, update the course id number on the ecommerce site
            // create the woocommerce data object for the product
            $data = [
                'meta_data' => [
                    [
                        'key' => 'dc_course_ids',
                        'value' => $course_no
                    ]
                ]
            ];
            
            // Make the curl request
            $config = get_config('local_sales_front');
            $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/" . $course->productid . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);
            curl_close($ch);
            file_put_contents(__DIR__ . 'update_product_result.txt', $res . "\n", FILE_APPEND);
        } else {
            // get the tags selected for the course to assign as product categories

            $tag_info = \core_tag_tag::get_item_tags_array('core', 'course', $eventdata['objectid']);
            file_put_contents(__DIR__ . 'create_product.txt', "Tags Result: " . print_r($tag_info, true) . "\n", FILE_APPEND);
            // build the category array
            $product_cats = [];
            foreach($tag_info as $key => $value) {
                file_put_contents(__DIR__ . 'update_product.txt', "Category ID: " . $key . "\n", FILE_APPEND);
                $cat_res = $DB->get_record('tag', array('id' => $key), 'ecommproductcat');
                $product_cats[] = [
                    'id' => $cat_res->ecommproductcat
                ];
            }
            
            // designate states that are pace, if neccessary
            if (!empty($course->paceapprovalno) && 0 == $course->includecustomstates) {
                // get all the states that are designated as pace and life
                observer::setPACE($course, $course->paceapprovalno, 1);
            }
            
            // designate states that are life university, if necessary
            if (0 == $course->includecustomstates) {
                observer::setPACE($course, 'LIFE', 2);
            }
            
            /*
            if (1 == $course->lifeapprovedstates) {
                // get all of the states that are flagged as life
                observer::setPACE($course, '', 2);
            }
            */
            
            // find out what states were selected (based on approval numbers entered)
            observer::processStateCategoryIds($course, $product_cats);
            
            file_put_contents(__DIR__ . 'create_product.txt', "Product Cats: " . print_r($product_cats, true) . "\n", FILE_APPEND);
            
            // get any images associated with the course
            require_once($CFG->libdir. '/coursecatlib.php');
            $course_obj = new \course_in_list($course);
            $img_arr = [];
            $cur_position = 0;
            foreach ($course_obj->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                $url = file_encode_url($CFG->wwwroot . '/pluginfile.php',
                '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                if($isimage) {
                    $img_arr[] = [
                        'src' => $url,
                        'position' => $cur_position
                    ];
                    $cur_position++;
                }
            }
            
            // create the woocommerce data object for the new product
            $data = [
                'name' => $course->fullname,
                'type' => 'simple',
                'regular_price' => $course->courseprice,
                'description' => $course->summary,
                //'short_description' => $course->summary,
                'categories' => $product_cats,
                'images' => $img_arr,
                'sold_individually' => true,
                'meta_data' => [
                    [
                        'key' => 'dc_credit_hours',
                        'value' => $course->credithrs
                    ],
                    [
                        'key' => 'dc_course_ids',
                        'value' => $course_no
                    ]
                ]
            ];
            
            // Make the curl request
            $config = get_config('local_sales_front');
            $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            file_put_contents(__DIR__ . 'create_product_result.txt', $res . "\n", FILE_APPEND);
            file_put_contents(__DIR__ . 'create_product_result.txt', $http_status . "\n", FILE_APPEND);
            if (201 == $http_status) {
                // save the product id and course number to the course record
                $product = json_decode($res);
                $up_obj = new \stdClass();
                $up_obj->id = $eventdata['objectid'];
                $up_obj->productid = $product->id;
                $up_obj->idnumber = $course_no;
                $DB->update_record('course', $up_obj);
            }    
        }
    }
    
    public static function update_product(\core\event\course_updated $event) {
        global $DB, $CFG;
        $eventdata = $event->get_data();
        $course = $event->get_record_snapshot('course', $eventdata['objectid']);
        file_put_contents(__DIR__ . 'update_product.txt', print_r($course, true) . "\n", FILE_APPEND);
        file_put_contents(__DIR__ . 'update_product.txt', print_r($eventdata, true) . "\n", FILE_APPEND);
        //file_put_contents(__DIR__ . 'update_product.txt', print_r($event->get_record_snapshot('course', $eventdata['objectid']), true) . "\n", FILE_APPEND);
        $tag_info = \core_tag_tag::get_item_tags_array('core', 'course', $eventdata['objectid']);
        file_put_contents(__DIR__ . 'update_product.txt', print_r($tag_info, true) . "\n", FILE_APPEND);
        
        // get the product category ids
        $cat_array = [];
        foreach($tag_info as $key => $value) {
            file_put_contents(__DIR__ . 'update_product.txt', "Category ID: " . $key . "\n", FILE_APPEND);
            $cat_res = $DB->get_record('tag', array('id' => $key), 'ecommproductcat');
            $cat_array[] = [
                'id' => $cat_res->ecommproductcat
            ];
        }
        
        // designate states that are pace, if neccessary
        if (!empty($course->paceapprovalno) && 0 == $course->includecustomstates) {
            // get all the states that are designated as pace and life
            observer::setPACE($course, $course->paceapprovalno, 1);
        }
        
        // designate states that are life university, if necessary
        if (0 == $course->includecustomstates) {
            observer::setPACE($course, 'LIFE', 2);
        }
        
        // find out what states were selected (based on approval numbers entered)
        observer::processStateCategoryIds($course, $cat_array);
        
        file_put_contents(__DIR__ . 'update_product.txt', "Categories: " . print_r($cat_array, true) . "\n", FILE_APPEND);
        
        // get any images associated with the course
        require_once($CFG->libdir. '/coursecatlib.php');
        $course_obj = new \course_in_list($course);
        $img_arr = [];
        $cur_position = 0;
        foreach ($course_obj->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url($CFG->wwwroot . '/pluginfile.php',
            '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
            $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if($isimage) {
                $img_arr[] = [
                    'src' => $url,
                    'position' => $cur_position
                ];
                $cur_position++;
            }
        }
        
        // create the woocommerce data object for the product
        $data = [
            'name' => $course->fullname,
            'type' => 'simple',
            'regular_price' => $course->courseprice,
            'description' => $course->summary,
            //'short_description' => $course->ecommshortdescr,
            'categories' => $cat_array,
            'images' => $img_arr,
            'meta_data' => [
                [
                    'key' => 'dc_credit_hours',
                    'value' => $course->credithrs
                ]
            ]
        ];
        file_put_contents(__DIR__ . 'update_product_result.txt', print_r($data, true), FILE_APPEND);
        // Make the curl request
        $config = get_config('local_sales_front');
        $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/" . $course->productid . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        file_put_contents(__DIR__ . 'update_product_result.txt', $res . "\n", FILE_APPEND);
        
        // update any bundles the course is associated with
        observer::updateBundleDescriptions($course->idnumber);
        
    }
    
    public static function delete_product(\core\event\course_deleted $event) {
        $eventdata = $event->get_data();
        $course = $event->get_record_snapshot('course', $eventdata['objectid']);
        file_put_contents(__DIR__ . 'delete_product.txt', print_r($eventdata, true) . "\n", FILE_APPEND);
        
        // Make the curl request
        $config = get_config('local_sales_front');
        $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/" . $course->productid . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        file_put_contents(__DIR__ . 'delete_product_result.txt', $res . "\n", FILE_APPEND);
    }
    
    public static function add_product_category(\core\event\tag_created $event) {
        global $DB;
        $eventdata = $event->get_data();
        file_put_contents(__DIR__ . 'create_category.txt', print_r($eventdata, true) . "\n", FILE_APPEND);
        
        // create the woocommerce data object for the new product
        $data = [
            'name' => $eventdata['other']['rawname'],
        ];
        
        // Make the curl request
        $config = get_config('local_sales_front');
        $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/categories?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        file_put_contents(__DIR__ . 'create_category.txt', $res . "\n", FILE_APPEND);
        file_put_contents(__DIR__ . 'create_category.txt', $http_status . "\n", FILE_APPEND);
        if (201 == $http_status) {
            // save the product category id
            $product_cat = json_decode($res);
            $up_obj = new \stdClass();
            $up_obj->id = $eventdata['objectid'];
            $up_obj->ecommproductcat = $product_cat->id;
            $DB->update_record('tag', $up_obj);
        }
    }
    
    public static function update_product_category(\core\event\tag_updated $event) {
        global $DB;
        $eventdata = $event->get_data();
        $tag_info = $event->get_record_snapshot('tag', $eventdata['objectid']);
        file_put_contents(__DIR__ . 'update_category.txt', print_r($eventdata, true) . "\n", FILE_APPEND);
        
        // create the woocommerce data object for the new product
        $data = [
            'name' => $eventdata['other']['rawname'],
        ];
        
        // Make the curl request
        $config = get_config('local_sales_front');
        $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/categories/" . $tag_info->ecommproductcat . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        file_put_contents(__DIR__ . 'update_category.txt', $res . "\n", FILE_APPEND);
        file_put_contents(__DIR__ . 'update_category.txt', $http_status . "\n", FILE_APPEND);
    }
    
    public static function delete_product_category(\core\event\tag_deleted $event) {
        $eventdata = $event->get_data();
        $tag_info = $event->get_record_snapshot('tag', $eventdata['objectid']);
        file_put_contents(__DIR__ . 'delete_category.txt', print_r($eventdata, true) . "\n", FILE_APPEND);
        
        // Make the curl request
        $config = get_config('local_sales_front');
        $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/categories/" . $tag_info->ecommproductcat . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret . "&force=true");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        file_put_contents(__DIR__ . 'delete_category_result.txt', $res . "\n", FILE_APPEND);
    }
    
    public static function processStateCategoryIds($course_info, &$product_cats) {
        global $DB;
        
        // get the category ids
        $state_settings = $DB->get_records('local_state_settings');
        $state_cats = array();
        foreach($state_settings as $setting) {
            $state_cats[$setting->state . 'approvalno'] = $setting->ecommcatid;
        }
        
        // which id's need to be returned?
        if (!empty($course_info->alapprovalno)) {
            $product_cats[] = ['id' => $state_cats['alapprovalno']];
        }
        if (!empty($course_info->akapprovalno)) {
            $product_cats[] = ['id' => $state_cats['akapprovalno']];
        }
        if (!empty($course_info->azapprovalno)) {
            $product_cats[] = ['id' => $state_cats['azapprovalno']];
        }
        if (!empty($course_info->arapprovalno)) {
            $product_cats[] = ['id' => $state_cats['arapprovalno']];
        }
        if (!empty($course_info->caapprovalno)) {
            $product_cats[] = ['id' => $state_cats['caapprovalno']];
        }
        if (!empty($course_info->coapprovalno)) {
            $product_cats[] = ['id' => $state_cats['coapprovalno']];
        }
        if (!empty($course_info->ctapprovalno)) {
            $product_cats[] = ['id' => $state_cats['ctapprovalno']];
        }
        if (!empty($course_info->deapprovalno)) {
            $product_cats[] = ['id' => $state_cats['deapprovalno']];
        }
        if (!empty($course_info->dcapprovalno)) {
            $product_cats[] = ['id' => $state_cats['dcapprovalno']];
        }
        if (!empty($course_info->flapprovalno)) {
            $product_cats[] = ['id' => $state_cats['flapprovalno']];
        }
        if (!empty($course_info->gaapprovalno)) {
            $product_cats[] = ['id' => $state_cats['gaapprovalno']];
        }
        if (!empty($course_info->hiapprovalno)) {
            $product_cats[] = ['id' => $state_cats['hiapprovalno']];
        }
        if (!empty($course_info->idapprovalno)) {
            $product_cats[] = ['id' => $state_cats['idapprovalno']];
        }
        if (!empty($course_info->ilapprovalno)) {
            $product_cats[] = ['id' => $state_cats['ilapprovalno']];
        }
        if (!empty($course_info->inapprovalno)) {
            $product_cats[] = ['id' => $state_cats['inapprovalno']];
        }
        if (!empty($course_info->iaapprovalno)) {
            $product_cats[] = ['id' => $state_cats['iaapprovalno']];
        }
        if (!empty($course_info->ksapprovalno)) {
            $product_cats[] = ['id' => $state_cats['ksapprovalno']];
        }
        if (!empty($course_info->kyapprovalno)) {
            $product_cats[] = ['id' => $state_cats['kyapprovalno']];
        }
        if (!empty($course_info->laapprovalno)) {
            $product_cats[] = ['id' => $state_cats['laapprovalno']];
        }
        if (!empty($course_info->meapprovalno)) {
            $product_cats[] = ['id' => $state_cats['meapprovalno']];
        }
        if (!empty($course_info->mdapprovalno)) {
            $product_cats[] = ['id' => $state_cats['mdapprovalno']];
        }
        if (!empty($course_info->maapprovalno)) {
            $product_cats[] = ['id' => $state_cats['maapprovalno']];
        }
        if (!empty($course_info->miapprovalno)) {
            $product_cats[] = ['id' => $state_cats['miapprovalno']];
        }
        if (!empty($course_info->mnapprovalno)) {
            $product_cats[] = ['id' => $state_cats['mnapprovalno']];
        }
        if (!empty($course_info->msapprovalno)) {
            $product_cats[] = ['id' => $state_cats['msapprovalno']];
        }
        if (!empty($course_info->moapprovalno)) {
            $product_cats[] = ['id' => $state_cats['moapprovalno']];
        }
        if (!empty($course_info->mtapprovalno)) {
            $product_cats[] = ['id' => $state_cats['mtapprovalno']];
        }
        if (!empty($course_info->neapprovalno)) {
            $product_cats[] = ['id' => $state_cats['neapprovalno']];
        }
        if (!empty($course_info->nvapprovalno)) {
            $product_cats[] = ['id' => $state_cats['nvapprovalno']];
        }
        if (!empty($course_info->nhapprovalno)) {
            $product_cats[] = ['id' => $state_cats['nhapprovalno']];
        }
        if (!empty($course_info->njapprovalno)) {
            $product_cats[] = ['id' => $state_cats['njapprovalno']];
        }
        if (!empty($course_info->nmapprovalno)) {
            $product_cats[] = ['id' => $state_cats['nmapprovalno']];
        }
        if (!empty($course_info->nyapprovalno)) {
            $product_cats[] = ['id' => $state_cats['nyapprovalno']];
        }
        if (!empty($course_info->ncapprovalno)) {
            $product_cats[] = ['id' => $state_cats['ncapprovalno']];
        }
        if (!empty($course_info->ndapprovalno)) {
            $product_cats[] = ['id' => $state_cats['ndapprovalno']];
        }
        if (!empty($course_info->ohapprovalno)) {
            $product_cats[] = ['id' => $state_cats['ohapprovalno']];
        }
        if (!empty($course_info->okapprovalno)) {
            $product_cats[] = ['id' => $state_cats['okapprovalno']];
        }
        if (!empty($course_info->orapprovalno)) {
            $product_cats[] = ['id' => $state_cats['orapprovalno']];
        }
        if (!empty($course_info->paapprovalno)) {
            $product_cats[] = ['id' => $state_cats['paapprovalno']];
        }
        if (!empty($course_info->riapprovalno)) {
            $product_cats[] = ['id' => $state_cats['riapprovalno']];
        }
        if (!empty($course_info->scapprovalno)) {
            $product_cats[] = ['id' => $state_cats['scapprovalno']];
        }
        if (!empty($course_info->sdapprovalno)) {
            $product_cats[] = ['id' => $state_cats['sdapprovalno']];
        }
        if (!empty($course_info->tnapprovalno)) {
            $product_cats[] = ['id' => $state_cats['tnapprovalno']];
        }
        if (!empty($course_info->txapprovalno)) {
            $product_cats[] = ['id' => $state_cats['txapprovalno']];
        }
        if (!empty($course_info->utapprovalno)) {
            $product_cats[] = ['id' => $state_cats['utapprovalno']];
        }
        if (!empty($course_info->vtapprovalno)) {
            $product_cats[] = ['id' => $state_cats['vtapprovalno']];
        }
        if (!empty($course_info->vaapprovalno)) {
            $product_cats[] = ['id' => $state_cats['vaapprovalno']];
        }
        if (!empty($course_info->waapprovalno)) {
            $product_cats[] = ['id' => $state_cats['waapprovalno']];
        }
        if (!empty($course_info->wvapprovalno)) {
            $product_cats[] = ['id' => $state_cats['wvapprovalno']];
        }
        if (!empty($course_info->wiapprovalno)) {
            $product_cats[] = ['id' => $state_cats['wiapprovalno']];
        }
        if (!empty($course_info->wyapprovalno)) {
            $product_cats[] = ['id' => $state_cats['wyapprovalno']];
        }
        
    }
    
    public static function setPACE(&$course_info, $pace_number, $number_type) {
        // grab the pace settings
        global $DB;
        $pace_settings = $DB->get_records('local_state_settings', array('customapprove' => $number_type));
        file_put_contents(__DIR__ . 'get_pace_states.txt', print_r($pace_settings, true));
        // assign the number for the found states
        foreach ($pace_settings as $pace) {
            $c_idx = $pace->state . 'approvalno';
            $course_info->$c_idx = $pace_number;
        }
    }
    
    public static function add_certificate(\core\event\course_module_created $event) {
        // automatically add a certificate when a SCORM module is created
        global $DB;
        $eventdata = $event->get_data();
        //file_put_contents(__DIR__ . '/add_module.txt', print_r($eventdata, true) . "\n", FILE_APPEND);
        if ('scorm' == $eventdata['other']['modulename']) {
            /* GET THE COURSE */
            $course = get_course($eventdata['courseid']);
            
            // build the data object to send to the customcert library
            $cert_data = new \stdClass();
            $cert_data->name = $eventdata['other']['name'] . ' Certificate';
            $cert_data->emailstudents = 1;
            $cert_data->emailteachers = 0;
            $cert_data->emailothers = '';
            $cert_data->verifyany = 0;
            $cert_data->requiredtime = 0;
            $cert_data->visible = 1;
            $cert_data->visibleoncoursepage = 1;
            $cert_data->cmidnumber = '';
            $cert_data->groupmode = 0;
            $cert_data->groupingid = 0;
            $cert_data->availabilityconditionsjson = '{"op":"&","c":[{"type":"completion","cm":' . $eventdata['objectid'] . ',"e":1}],"showc":[true]}';
            $cert_data->completionunlocked = 1;
            $cert_data->completion = 0;
            $cert_data->completionview = 1;
            $cert_data->completionexpected = 0;
            $cert_data->tags = array();
            $cert_data->course = $eventdata['courseid'];
            $cert_data->coursemodule = 0;
            $cert_data->section = 0;
            $cert_data->module = 23;
            $cert_data->modulename = 'customcert';
            $cert_data->instance = 0;
            $cert_data->add = 'customcert';
            $cert_data->update = 0;
            $cert_data->return = 0;
            $cert_data->sr = 0;
            $cert_data->competency_rule = 0;
            $cert_data->submitbutton2 = 'Save and return to course';
            $cert_data->protection_modify = 1;
            $cert_data->protection_copy = 1;
            //$cert_data->completiongradeitemnumber = '';
            //$cert_data->conditiongradegroup = array();
            //$cert_data->conditionfieldgroup = arraay();
            //$cert_data->intro = '';
            //$cert_data->introformat = 1;
            
            /* CREATE THE MODULE */
            $mod_info = add_moduleinfo($cert_data, $course, null);
            //file_put_contents(__DIR__ . '/add_module.txt', print_r($mod_info, true) . "\n", FILE_APPEND);
            
            /* COPY MAIN TEMPLATE */
            // main template info
            $template_info = $DB->get_record('customcert_templates', array('id' => 1));
            // remove the page for the existing template
            $del_page = $DB->delete_records('customcert_pages', array('templateid' => $mod_info->templateid));
            // new template instance
            $new_template = new \mod_customcert\template($template_info);
            // copy the pages from the main template to the new one
            $new_template->copy_to_template($mod_info->templateid);
        }
    }
    
    public static function updateBundleDescriptions($idnumber) {
        global $DB;
        // get all of the bundles with the course idnumber sent
        $select = "courses LIKE '%" . $idnumber . "%'";
        file_put_contents(__DIR__ . '/update_bundle_result.txt', "SELECT: " . $select . "\n");
        $records = $DB->get_records_select('local_course_bundles', $select, array('id', 'courses', 'ecommproductid'));
        file_put_contents(__DIR__ . '/update_bundle_result.txt', "RECORDS: " . print_r($records, true) . "\n", FILE_APPEND);
        foreach ($records as $record) {
            // get the courses involved and build the new description for the bundle
            $cselect = "idnumber IN ('" . str_replace(",", "','", $record->courses) . "')";
            file_put_contents(__DIR__ . '/update_bundle_result.txt', "SELECT COURSE: " . $cselect . "\n", FILE_APPEND);
            $courses = $DB->get_records_select('course', $cselect, array('id', 'fullname', 'credithrs', 'summary'));
            file_put_contents(__DIR__ . '/update_bundle_result.txt', "COURSE RECORDS: " . print_r($courses, true) . "\n", FILE_APPEND);
            $bundle_descr = observer::buildDescription($courses);
            // send the update to the ecommerce site
            // create the woocommerce data object for the new product
            $woo_data = [
                'description' => $bundle_descr
            ];
            
            // Make the curl request
            $config = get_config('local_sales_front');
            $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/" . $record->ecommproductid . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($woo_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);
            curl_close($ch);
            file_put_contents(__DIR__ . '/update_bundle_result.txt', $res . "\n", FILE_APPEND);
        }
    }
    
    /**
     * @param object $courses - contains information of all assigned courses
     * @return string - the long description for eCommerce
     */
    public static function buildDescription($courses) {
        global $DB;
        file_put_contents(__DIR__ . '/build_bundle_descripts.txt', print_r($courses, true), FILE_APPEND);
        $descr_str = '';
        
        // go through each course
        foreach ($courses as $course_info) {
            $descr_str .= $course_info->fullname . " - Credit Hours: " . $course_info->credithrs;
            // get the categories associated with the course
            $tag_info = \core_tag_tag::get_item_tags_array('core', 'course', $course_info->id);
            //file_put_contents(__DIR__ . '/tag_info.txt', "Course: " . $course_info['fullname'] . "\n" . print_r($tag_info, true), FILE_APPEND);
            foreach($tag_info as $key => $value) {
                $descr_str .= '<span class="course-category">' . $value . '</span>';
            }
            $descr_str .= $course_info->summary . '<br /><br />';
        }
        return $descr_str;
    }
}