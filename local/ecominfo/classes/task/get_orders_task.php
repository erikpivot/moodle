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
 * A scheduled task for emailing certificates.
 *
 * @package    local_ecominfo
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_ecominfo\task;

defined('MOODLE_INTERNAL') || die();

/**
 * A scheduled task for retrieving orders from the ecommerce site
 *
 * @package    local_ecominfo
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_orders_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskgetorders', 'local_ecominfo');
    }

    /**
     * Execute.
     */
    public function execute() {
        global $DB;
        
        // retreive the most recent orders from the ecommerce site
        $sql = "SELECT orderdate FROM {local_ecominfo} ORDER BY orderid DESC";
        $current_orders = $DB->get_records_sql($sql, null, 0, 1);
        $last_order = '2018-01-01T00:00:00';
        foreach ($current_orders as $this_order) {
            $datetime = new \DateTime();
            $datetime->setTimestamp($this_order->orderdate);
            $last_order = $datetime->format('c');
        }
        
        // Make the curl request
        $config = get_config('local_sales_front');
        $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/orders/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret . "&after=" . $last_order . "&per_page=100");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // decode the response if one was returned
        if (201 == $http_status || 200 == $http_status) {
            $orders_obj = json_decode($res);
            foreach($orders_obj as $order_info) {
                $order_id = $order_info->id;
                $order_date = strtotime($order_info->date_created);
                $student_id = $order_info->customer_id;
                // go through each order item to get the courses associated with the order
                foreach($order_info->line_items as $order_item) {
                    $average_price = 0.00;
                    // search the individual courses first
                    $course_ids = $DB->get_record('course', array('productid' => $order_item->product_id), 'idnumber,credithrs');
                    if (!$course_ids) {
                        // this line item is a bundle product
                        $course_ids = $DB->get_record('local_course_bundles', array('ecommproductid' => $order_item->product_id), 'courses,credithrs');
                        // do individual records for each course in the bundle
                        $split_courses = explode(",", $course_ids->courses);
                        $average_price = $order_item->subtotal / sizeof($split_courses);
                        foreach($split_courses as $split_id) {
                            $insert_obj = new \stdClass();
                            $insert_obj->ecomstudentid = $student_id;
                            $insert_obj->courses = $split_id;
                            $insert_obj->price = $order_item->subtotal;
                            $insert_obj->ecommproductid = $order_item->product_id;
                            $insert_obj->orderid = $order_id;
                            $insert_obj->orderdate = $order_date;
                            $insert_obj->avgprice = round($average_price, 2);
                            $insert_obj->credithours = $course_ids->credithrs;
                            //echo print_r($insert_obj, true);
                            $DB->insert_record('local_ecominfo', $insert_obj, false);
                        }
                    } else {
                        $average_price = $order_item->subtotal;
                        $insert_obj = new \stdClass();
                        $insert_obj->ecomstudentid = $student_id;
                        $insert_obj->courses = $course_ids->idnumber;
                        $insert_obj->price = $order_item->subtotal;
                        $insert_obj->ecommproductid = $order_item->product_id;
                        $insert_obj->orderid = $order_id;
                        $insert_obj->orderdate = $order_date;
                        $insert_obj->avgprice = round($average_price, 2);
                        $insert_obj->credithours = $course_ids->credithrs;
                        //echo print_r($insert_obj, true);
                        $DB->insert_record('local_ecominfo', $insert_obj, false);
                    }
                    
                    // add the raw data
                    $raw_obj = new \stdClass();
                    $raw_obj->ecomstudentid = $student_id;
                    $raw_obj->courses = (!empty($course_ids->idnumber) ? $course_ids->idnumber : $course_ids->courses);
                    $raw_obj->price = $order_item->subtotal;
                    $raw_obj->ecommproductid = $order_item->product_id;
                    $raw_obj->orderid = $order_id;
                    $raw_obj->orderdate = $order_date;
                    $raw_obj->credithours = $course_ids->credithrs;
                    $DB->insert_record('local_ecominfo_raw_data', $raw_obj, false);
                }
            }
        }
    }
}
