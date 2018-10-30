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
 * A scheduled task for revising all courses for a new calendar year
 *
 * @package    local_revise_courses
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_revise_courses\task;

defined('MOODLE_INTERNAL') || die();

/**
 * A scheduled task for retrieving orders from the ecommerce site
 *
 * @package    local_revise_courses
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class revise_courses_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskrevisecourses', 'local_revise_courses');
    }

    /**
     * Execute.
     */
    public function execute() {
        global $DB;
        
        //require_once(__DIR__ . '/../../../../course/lib.php');
        
        // retreive the current courses
        $all_courses = $DB->get_records('course', array('category' => 1));
        foreach($all_courses as $course_info) {
            // modify the name of the current course
            $course_revision = "Created: " . date('m-d-Y', $course_info->timecreated) . " Ended:" . date('m-d-Y');
            $course_short = $course_info->shortname . " R." . $course_info->revisionno;
            $oldidnumber = $course_info->idnumber;
            $old_course_obj = new \stdClass();
            $old_course_obj->id = $course_info->id;
            $old_course_obj->revisiontext = $course_revision;
            $old_course_obj->shortname = $course_short;
            $old_course_obj->productid = 0;
            $old_course_obj->category = 2; // Archived
            $DB->update_record('course', $old_course_obj);
            
            // create the duplicate course
            $course_info->idnumber = '';
            $course_info->revisionno = $course_info->revisionno + 1;
            $course_info->revisiontext = '';
            $new_course = create_course($course_info);
            file_put_contents(__DIR__ . '/new_course_info.txt', print_r($new_course, true));
            
            // update course format options
            $old_course_format_options = course_get_format($course_info->id)->get_format_options();
            //echo print_r($old_course_format_options, true);
            course_get_format($new_course->id)->update_course_format_options($old_course_format_options);
            
            // copy tags
            $old_course_tags = \core_tag_tag::get_item_tags_array('core', 'course', $course_info->id);
            //echo print_r($old_course_tags, true);
            \core_tag_tag::set_item_tags('core', 'course', $new_course->id, \context_course::instance($new_course->id), $old_course_tags);
            
            // set the new course id number
            $course_no = date('Ymdhis') . "-" . $new_course->id;
            $course_obj = new \stdClass();
            $course_obj->id = $new_course->id;
            $course_obj->idnumber = $course_no;
            $DB->update_record('course', $course_obj);
            
            // copy the scorm and certificate modules associated with the revised course
            $scorm_mod = $DB->get_record('course_modules', array('course' => $course_info->id));
            
            // revise any bundles that this course is associated with
            $this->reviseCourseBundles($oldidnumber, $course_no);
            
            // log the update
            $rev_obj = new \stdClass();
            $rev_obj->newcourseidnumber = $course_no;
            $rev_obj->oldcourseidnumber = $oldidnumber;
            $rev_obj->coursename = $course_info->fullname;
            $rev_obj->revisiondate = time();
            $DB->insert_record('local_revise_courses', $rev_obj);
        }
    }
    
    /**
     * @param string $oldidnumber
     * @param string $newidnumber
     */
    public function reviseCourseBundles($oldidnumber, $newidnumber) {
        global $DB;
        // get the bundles associated with the old id number
        $select = "courses LIKE '%" . $oldidnumber . "%'";
        //file_put_contents(__DIR__ . '/update_bundle_result.txt', "SELECT: " . $select . "\n");
        $records = $DB->get_records_select('local_course_bundles', $select, array('id', 'courses', 'ecommproductid'));
        //file_put_contents(__DIR__ . '/update_bundle_result.txt', "RECORDS: " . print_r($records, true) . "\n", FILE_APPEND);
        foreach ($records as $record) {
            // rebuild the course list associated with the bundle
            $all_courses = explode(",", $record->courses);
            // remove the old course from the list
            if (($key = array_search($oldidnumber, $all_courses)) !== false) {
                unset($all_courses[$key]);
            }
            
            // build the new course list
            array_push($all_courses, $newidnumber);
            $new_course_list = implode(",", $all_courses);
            file_put_contents(__DIR__ . '/update_bundle_result.txt', "COURSE LIST: " . $new_course_list . "\n", FILE_APPEND);
            // update the bundle with the new course list
            $bundle_obj = new \stdClass();
            $bundle_obj->id = $record->id;
            $bundle_obj->courses = $new_course_list;
            //file_put_contents(__DIR__ . '/update_bundle_result.txt', "BUNDLE OBJECT: " . print_r($bundle_obj, true) . "\n", FILE_APPEND);
            $DB->update_record('local_course_bundles', $bundle_obj);
            
            // get the courses involved
            $cselect = "idnumber IN ('" . str_replace(",", "','", $new_course_list) . "')";
            //file_put_contents(__DIR__ . '/update_bundle_result.txt', "SELECT COURSE: " . $cselect . "\n", FILE_APPEND);
            $courses = $DB->get_records_select('course', $cselect, array('id', 'fullname', 'credithrs', 'summary'));
            //file_put_contents(__DIR__ . '/update_bundle_result.txt', "COURSE RECORDS: " . print_r($courses, true) . "\n", FILE_APPEND);
            $bundle_descr = $this->buildDescription($courses);
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
     * @param object $courses - contains information of all assigned courses
     * @return string - the long description for eCommerce
     */
    public function buildDescription($courses) {
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
