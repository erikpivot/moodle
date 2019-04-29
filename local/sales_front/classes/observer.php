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
        file_put_contents(__DIR__ . 'create_product.txt', date('Y-m-d h:i:s') . "\n", FILE_APPEND);
        file_put_contents(__DIR__ . 'create_product.txt', "------------------------------------\n", FILE_APPEND);
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
                // do not include charlie's tag
                if ($key != 118) {
                    file_put_contents(__DIR__ . 'update_product.txt', "Category ID: " . $key . "\n", FILE_APPEND);
                    $cat_res = $DB->get_record('tag', array('id' => $key), 'ecommproductcat');
                    $product_cats[] = [
                        'id' => $cat_res->ecommproductcat
                    ];   
                }
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
            $included_states = [];
            observer::processStateCategoryIds($course, $product_cats, $included_states);
            
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
        file_put_contents(__DIR__ . '/update_product.txt', print_r($course, true) . "\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/update_product.txt', print_r($eventdata, true) . "\n", FILE_APPEND);
        //file_put_contents(__DIR__ . 'update_product.txt', print_r($event->get_record_snapshot('course', $eventdata['objectid']), true) . "\n", FILE_APPEND);
        $tag_info = \core_tag_tag::get_item_tags_array('core', 'course', $eventdata['objectid']);
        file_put_contents(__DIR__ . '/update_product.txt', print_r($tag_info, true) . "\n", FILE_APPEND);
        
        // get the product category ids
        $cat_array = [];
        foreach($tag_info as $key => $value) {
            // do not include charlie's tag
            if ($key != 118) {
                file_put_contents(__DIR__ . '/update_product.txt', "Category ID: " . $key . "\n", FILE_APPEND);
                $cat_res = $DB->get_record('tag', array('id' => $key), 'ecommproductcat');
                $cat_array[] = [
                    'id' => $cat_res->ecommproductcat
                ];
            }
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
        $included_states = [];
        observer::processStateCategoryIds($course, $cat_array, $included_states);
        
        file_put_contents(__DIR__ . '/update_product.txt', "Categories: " . print_r($cat_array, true) . "\n", FILE_APPEND);
        
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
        file_put_contents(__DIR__ . '/update_product_result.txt', print_r($data, true), FILE_APPEND);
        // Make the curl request
        $config = get_config('local_sales_front');
        $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/" . $course->productid . "/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        file_put_contents(__DIR__ . '/update_product_result.txt', $res . "\n", FILE_APPEND);
        
        // update any bundles the course is associated with
        observer::bundleStates($course->idnumber, $course->credithrs);
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
        
        // update any bundles that include the remove course
        observer::reviseCourseBundles($course->idnumber, $course->credithrs);
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
    
    public static function processStateCategoryIds($course_info, &$product_cats, &$included_states) {
        global $DB;
        
        // get the category ids
        $state_settings = $DB->get_records('local_state_settings');
        $state_cats = array();
        foreach($state_settings as $setting) {
            $state_cats[$setting->state . 'approvalno'] = $setting->ecommcatid;
        }
        
        // which id's need to be returned?
        if (!empty($course_info->alapprovalno) && 0 == $course_info->alexclude) {
            $product_cats[] = ['id' => $state_cats['alapprovalno']];
            $included_states[] = 'al';
        }
        if (!empty($course_info->akapprovalno) && 0 == $course_info->akexclude) {
            $product_cats[] = ['id' => $state_cats['akapprovalno']];
            $included_states[] = 'ak';
        }
        if (!empty($course_info->azapprovalno) && 0 == $course_info->azexclude) {
            $product_cats[] = ['id' => $state_cats['azapprovalno']];
            $included_states[] = 'az';
        }
        if (!empty($course_info->arapprovalno) && 0 == $course_info->arexclude) {
            $product_cats[] = ['id' => $state_cats['arapprovalno']];
            $included_states[] = 'ar';
        }
        if (!empty($course_info->caapprovalno) && 0 == $course_info->caexclude) {
            $product_cats[] = ['id' => $state_cats['caapprovalno']];
            $included_states[] = 'ca';
        }
        if (!empty($course_info->coapprovalno) && 0 == $course_info->coexclude) {
            $product_cats[] = ['id' => $state_cats['coapprovalno']];
            $included_states[] = 'co';
        }
        if (!empty($course_info->ctapprovalno) && 0 == $course_info->ctexclude) {
            $product_cats[] = ['id' => $state_cats['ctapprovalno']];
            $included_states[] = 'ct';
        }
        if (!empty($course_info->deapprovalno) && 0 == $course_info->deexclude) {
            $product_cats[] = ['id' => $state_cats['deapprovalno']];
            $included_states[] = 'de';
        }
        if (!empty($course_info->dcapprovalno) && 0 == $course_info->dcexclude) {
            $product_cats[] = ['id' => $state_cats['dcapprovalno']];
            $included_states[] = 'dc';
        }
        if (!empty($course_info->flapprovalno) && 0 == $course_info->flexclude) {
            $product_cats[] = ['id' => $state_cats['flapprovalno']];
            $included_states[] = 'fl';
        }
        if (!empty($course_info->gaapprovalno) && 0 == $course_info->gaexclude) {
            $product_cats[] = ['id' => $state_cats['gaapprovalno']];
            $included_states[] = 'ga';
        }
        if (!empty($course_info->hiapprovalno) && 0 == $course_info->hiexclude) {
            $product_cats[] = ['id' => $state_cats['hiapprovalno']];
            $included_states[] = 'hi';
        }
        if (!empty($course_info->idapprovalno) && 0 == $course_info->idexclude) {
            $product_cats[] = ['id' => $state_cats['idapprovalno']];
            $included_states[] = 'id';
        }
        if (!empty($course_info->ilapprovalno) && 0 == $course_info->ilexclude) {
            $product_cats[] = ['id' => $state_cats['ilapprovalno']];
            $included_states[] = 'il';
        }
        if (!empty($course_info->inapprovalno) && 0 == $course_info->inexclude) {
            $product_cats[] = ['id' => $state_cats['inapprovalno']];
            $included_states[] = 'in';
        }
        if (!empty($course_info->iaapprovalno) && 0 == $course_info->iaexclude) {
            $product_cats[] = ['id' => $state_cats['iaapprovalno']];
            $included_states[] = 'ia';
        }
        if (!empty($course_info->ksapprovalno) && 0 == $course_info->ksexclude) {
            $product_cats[] = ['id' => $state_cats['ksapprovalno']];
            $included_states[] = 'ks';
        }
        if (!empty($course_info->kyapprovalno) && 0 == $course_info->kyexclude) {
            $product_cats[] = ['id' => $state_cats['kyapprovalno']];
            $included_states[] = 'ky';
        }
        if (!empty($course_info->laapprovalno) && 0 == $course_info->laexclude) {
            $product_cats[] = ['id' => $state_cats['laapprovalno']];
            $included_states[] = 'la';
        }
        if (!empty($course_info->meapprovalno) && 0 == $course_info->meexclude) {
            $product_cats[] = ['id' => $state_cats['meapprovalno']];
            $included_states[] = 'me';
        }
        if (!empty($course_info->mdapprovalno) && 0 == $course_info->mdexclude) {
            $product_cats[] = ['id' => $state_cats['mdapprovalno']];
            $included_states[] = 'md';
        }
        if (!empty($course_info->maapprovalno) && 0 == $course_info->maexclude) {
            $product_cats[] = ['id' => $state_cats['maapprovalno']];
            $included_states[] = 'ma';
        }
        if (!empty($course_info->miapprovalno) && 0 == $course_info->miexclude) {
            $product_cats[] = ['id' => $state_cats['miapprovalno']];
            $included_states[] = 'mi';
        }
        if (!empty($course_info->mnapprovalno) && 0 == $course_info->mnexclude) {
            $product_cats[] = ['id' => $state_cats['mnapprovalno']];
            $included_states[] = 'mn';
        }
        if (!empty($course_info->msapprovalno) && 0 == $course_info->msexclude) {
            $product_cats[] = ['id' => $state_cats['msapprovalno']];
            $included_states[] = 'ms';
        }
        if (!empty($course_info->moapprovalno) && 0 == $course_info->moexclude) {
            $product_cats[] = ['id' => $state_cats['moapprovalno']];
            $included_states[] = 'mo';
        }
        if (!empty($course_info->mtapprovalno) && 0 == $course_info->mtexclude) {
            $product_cats[] = ['id' => $state_cats['mtapprovalno']];
            $included_states[] = 'mt';
        }
        if (!empty($course_info->neapprovalno) && 0 == $course_info->neexclude) {
            $product_cats[] = ['id' => $state_cats['neapprovalno']];
            $included_states[] = 'ne';
        }
        if (!empty($course_info->nvapprovalno) && 0 == $course_info->nvexclude) {
            $product_cats[] = ['id' => $state_cats['nvapprovalno']];
            $included_states[] = 'nv';
        }
        if (!empty($course_info->nhapprovalno) && 0 == $course_info->nhexclude) {
            $product_cats[] = ['id' => $state_cats['nhapprovalno']];
            $included_states[] = 'nh';
        }
        if (!empty($course_info->njapprovalno) && 0 == $course_info->njexclude) {
            $product_cats[] = ['id' => $state_cats['njapprovalno']];
            $included_states[] = 'nj';
        }
        if (!empty($course_info->nmapprovalno) && 0 == $course_info->nmexclude) {
            $product_cats[] = ['id' => $state_cats['nmapprovalno']];
            $included_states[] = 'nm';
        }
        if (!empty($course_info->nyapprovalno) && 0 == $course_info->nyexclude) {
            $product_cats[] = ['id' => $state_cats['nyapprovalno']];
            $included_states[] = 'ny';
        }
        if (!empty($course_info->ncapprovalno) && 0 == $course_info->ncexclude) {
            $product_cats[] = ['id' => $state_cats['ncapprovalno']];
            $included_states[] = 'nc';
        }
        if (!empty($course_info->ndapprovalno) && 0 == $course_info->ndexclude) {
            $product_cats[] = ['id' => $state_cats['ndapprovalno']];
            $included_states[] = 'nd';
        }
        if (!empty($course_info->ohapprovalno) && 0 == $course_info->ohexclude) {
            $product_cats[] = ['id' => $state_cats['ohapprovalno']];
            $included_states[] = 'oh';
        }
        if (!empty($course_info->okapprovalno) && 0 == $course_info->okexclude) {
            $product_cats[] = ['id' => $state_cats['okapprovalno']];
            $included_states[] = 'ok';
        }
        if (!empty($course_info->orapprovalno) && 0 == $course_info->orexclude) {
            $product_cats[] = ['id' => $state_cats['orapprovalno']];
            $included_states[] = 'or';
        }
        if (!empty($course_info->paapprovalno) && 0 == $course_info->paexclude) {
            $product_cats[] = ['id' => $state_cats['paapprovalno']];
            $included_states[] = 'pa';
        }
        if (!empty($course_info->riapprovalno) && 0 == $course_info->riexclude) {
            $product_cats[] = ['id' => $state_cats['riapprovalno']];
            $included_states[] = 'ri';
        }
        if (!empty($course_info->scapprovalno) && 0 == $course_info->scexclude) {
            $product_cats[] = ['id' => $state_cats['scapprovalno']];
            $included_states[] = 'sc';
        }
        if (!empty($course_info->sdapprovalno) && 0 == $course_info->sdexclude) {
            $product_cats[] = ['id' => $state_cats['sdapprovalno']];
            $included_states[] = 'sd';
        }
        if (!empty($course_info->tnapprovalno) && 0 == $course_info->tnexclude) {
            $product_cats[] = ['id' => $state_cats['tnapprovalno']];
            $included_states[] = 'tn';
        }
        if (!empty($course_info->txapprovalno) && 0 == $course_info->txexclude) {
            $product_cats[] = ['id' => $state_cats['txapprovalno']];
            $included_states[] = 'tx';
        }
        if (!empty($course_info->utapprovalno) && 0 == $course_info->utexclude) {
            $product_cats[] = ['id' => $state_cats['utapprovalno']];
            $included_states[] = 'ut';
        }
        if (!empty($course_info->vtapprovalno) && 0 == $course_info->vtexclude) {
            $product_cats[] = ['id' => $state_cats['vtapprovalno']];
            $included_states[] = 'vt';
        }
        if (!empty($course_info->vaapprovalno) && 0 == $course_info->vaexclude) {
            $product_cats[] = ['id' => $state_cats['vaapprovalno']];
            $included_states[] = 'va';
        }
        if (!empty($course_info->waapprovalno) && 0 == $course_info->waexclude) {
            $product_cats[] = ['id' => $state_cats['waapprovalno']];
            $included_states[] = 'wa';
        }
        if (!empty($course_info->wvapprovalno) && 0 == $course_info->wvexclude) {
            $product_cats[] = ['id' => $state_cats['wvapprovalno']];
            $included_states[] = 'wv';
        }
        if (!empty($course_info->wiapprovalno) && 0 == $course_info->wiexclude) {
            $product_cats[] = ['id' => $state_cats['wiapprovalno']];
            $included_states[] = 'wi';
        }
        if (!empty($course_info->wyapprovalno) && 0 == $course_info->wyexclude) {
            $product_cats[] = ['id' => $state_cats['wyapprovalno']];
            $included_states[] = 'wy';
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
        $woo_data = array(
            'update' => array()
        );
        foreach ($records as $record) {
            // get the courses involved and build the new description for the bundle
            $cselect = "idnumber IN ('" . str_replace(",", "','", $record->courses) . "')";
            file_put_contents(__DIR__ . '/update_bundle_result.txt', "SELECT COURSE: " . $cselect . "\n", FILE_APPEND);
            $courses = $DB->get_records_select('course', $cselect, array('id', 'fullname', 'credithrs', 'summary'));
            file_put_contents(__DIR__ . '/update_bundle_result.txt', "COURSE RECORDS: " . print_r($courses, true) . "\n", FILE_APPEND);
            $bundle_descr = observer::buildDescription($courses);
            // send the update to the ecommerce site
            // create the woocommerce data object for the new product
            $woo_data['update'][] = array(
                'id' => $record->ecommproductid,
                'description' => $bundle_descr
            );
        }
        file_put_contents(__DIR__ . '/update_bundle_result.txt', "UPDATE ARRAY: " . print_r($woo_data, true) . "\n", FILE_APPEND);
        $req_json = json_encode($woo_data);
        file_put_contents(__DIR__ . '/update_bundle_result.txt', "UPDATE JSON STRING: " . $req_json . "\n", FILE_APPEND);
        // Make the curl request
        $config = get_config('local_sales_front');
        $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/batch/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        file_put_contents(__DIR__ . '/update_bundle_result.txt', $res . "\n", FILE_APPEND);
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
                // do not include charlie's tag
                if ($key != 118) {
                    $descr_str .= '<span class="course-category">' . $value . '</span>';
                }
            }
            $descr_str .= $course_info->summary . '<br /><br />';
        }
        return $descr_str;
    }
    
    /**
     * @param string $idnumber
     * @param int $credithours
     */
    public static function reviseCourseBundles($idnumber, $credithours) {
        global $DB;
        // get the bundles associated with the old id number
        $select = "courses LIKE '%" . $idnumber . "%'";
        //file_put_contents(__DIR__ . '/update_bundle_result.txt', "SELECT: " . $select . "\n");
        $records = $DB->get_records_select('local_course_bundles', $select, array('id', 'courses', 'ecommproductid', 'credithrs'));
        //file_put_contents(__DIR__ . '/update_bundle_result.txt', "RECORDS: " . print_r($records, true) . "\n", FILE_APPEND);
        foreach ($records as $record) {
            // rebuild the course list associated with the bundle
            $all_courses = explode(",", $record->courses);
            // remove the old course from the list
            if (($key = array_search($idnumber, $all_courses)) !== false) {
                unset($all_courses[$key]);
            }
            
            // build the new course list
            $new_course_list = implode(",", $all_courses);
            file_put_contents(__DIR__ . '/update_bundle_result.txt', "COURSE LIST: " . $new_course_list . "\n", FILE_APPEND);
            // update the bundle with the new course list
            $bundle_obj = new \stdClass();
            $bundle_obj->id = $record->id;
            $bundle_obj->courses = $new_course_list;
            // subtract the credit hours removed
            $bundle_obj->credithrs = $record->credithrs - $credithours;
            //file_put_contents(__DIR__ . '/update_bundle_result.txt', "BUNDLE OBJECT: " . print_r($bundle_obj, true) . "\n", FILE_APPEND);
            $DB->update_record('local_course_bundles', $bundle_obj);
            
            // get the courses involved
            $cselect = "idnumber IN ('" . str_replace(",", "','", $new_course_list) . "')";
            //file_put_contents(__DIR__ . '/update_bundle_result.txt', "SELECT COURSE: " . $cselect . "\n", FILE_APPEND);
            $courses = $DB->get_records_select('course', $cselect, array('id', 'fullname', 'credithrs', 'summary'));
            //file_put_contents(__DIR__ . '/update_bundle_result.txt', "COURSE RECORDS: " . print_r($courses, true) . "\n", FILE_APPEND);
            $bundle_descr = observer::buildDescription($courses);
            // send the update to the ecommerce site
            // create the woocommerce data object for the new product
            $woo_data = [
                'description' => $bundle_descr,
                'meta_data' => [
                    [
                        'key' => 'dc_course_ids',
                        'value' => $new_course_list
                    ]
                ]
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
            //file_put_contents(__DIR__ . '/update_bundle_result.txt', $res . "\n", FILE_APPEND);
        }
    }
    
    /**
     * Revises course bundles if a state was removed from a course
     * @param string $idnumber
     * @param number $credithours
     */
    public static function bundleStates($idnumber, $credithours) {
        global $DB;
        // get the course state information
        $course_info = $DB->get_record('course', array('idnumber' => $idnumber));
        $is_custom_approve = false;
        if (!empty($course_info->paceapprovalno)) {
            $is_custom_approve = true;
        }
        // get the bundles associated with the idnumber
        $select = "courses LIKE '%" . $idnumber . "%'";
        $records = $DB->get_records_select('local_course_bundles', $select, array('id', 'courses', 'name', 'state', 'credithrs'));
        foreach ($records as $bundle_record) {
            $remove_from_bundle = false;
            // is this a custom approved course?
            if ($is_custom_approve) {
                // check if the state associated with the bundle is excluded, if so it needs
                // to be removed from the bundle.  If not, check for an approval number
                $obj_name = $bundle_record->state . 'exclude';
                $obj_name_2 = $bundle_record->state . 'approvalno';
                if ($course_info->$obj_name == 1) {
                    $remove_from_bundle = true;
                } else if (empty($course_info->$obj_name_2)) {
                    $remove_from_bundle = true;
                }
            } else {
                // see if there is an approval number
                $obj_name = $bundle_record->state . 'approvalno';
                if (empty($course_info->$obj_name)) {
                    $remove_from_bundle = true;
                }
            }
            
            if ($remove_from_bundle) {
                // extract the course number from the bundle
                $new_course_numbers = str_replace($idnumber, "", $bundle_record->courses);
                $new_course_numbers = str_replace(",,", ",", $new_course_numbers);
                //echo $new_course_numbers . "<br />";
                
                // subtract the credit hours of the course from the bundle
                $new_credit_hours = $bundle_record->credithrs - $credithours;
                //echo "New Credit Hours: " . $new_credit_hours . "<br />";
                
                // re-build the title text of the bundle
                $new_bundle_title = str_replace($bundle_record->credithrs, $new_credit_hours, $bundle_record->name);
                //echo "New Title: " . $new_bundle_title . "<br />";
                
                // update the bundle record
                $update_obj = new \stdClass();
                $update_obj->id = $bundle_record->id;
                $update_obj->name = $new_bundle_title;
                $update_obj->credithrs = $new_credit_hours;
                $update_obj->courses = $new_course_numbers;
                //echo '<pre>';
                //echo print_r($update_obj, true);
                //echo '</pre>';
                $DB->update_record('local_course_bundles', $update_obj);
            }
        }
    }
}