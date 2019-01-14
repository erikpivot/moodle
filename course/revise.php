<?php
/**
 * Admin-only code that makes a revision to a course while
 * preserving the original course so student's currently enrolled
 * can finish the course.
 * 
 * @package core_course
 * @copyright 2018 Pivot Creative
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/filelib.php');
$id = required_param('id', PARAM_INT); // Course ID to copy
// get the course information to copy
$course = get_course($id);
// modify the name of the current course
$course_revision = "Created: " . date('m-d-Y', $course->timecreated) . " Ended:" . date('m-d-Y');
$course_short = $course->shortname . " R." . $course->revisionno;
$oldidnumber = $course->idnumber;
$old_course_obj = new stdClass();
$old_course_obj->id = $id;
$old_course_obj->revisiontext = $course_revision;
$old_course_obj->shortname = $course_short;
$old_course_obj->productid = 0;
$old_course_obj->category = 2; // Archived
$DB->update_record('course', $old_course_obj);
// create the duplicate course
$course->idnumber = '';
$course->revisionno = $course->revisionno + 1;
$course->revisiontext = '';
$new_course = create_course($course);
file_put_contents(__DIR__ . '/new_course_info.txt', print_r($new_course, true));
// update course format options
$old_course_format_options = course_get_format($id)->get_format_options();
//echo print_r($old_course_format_options, true);
course_get_format($new_course->id)->update_course_format_options($old_course_format_options);
// copy tags
$old_course_tags = core_tag_tag::get_item_tags_array('core', 'course', $id);
//echo print_r($old_course_tags, true);
core_tag_tag::set_item_tags('core', 'course', $new_course->id, context_course::instance($new_course->id), $old_course_tags);
// copy the course summary image from the old course
$new_course_context = context_course::instance($new_course->id);
$sql = "SELECT f.filename, f.contenthash FROM {context} cx 
        JOIN {course} c ON cx.instanceid = c.id 
        JOIN {files} f ON cx.id=f.contextid 
        WHERE f.filename <> '.' 
        AND f.component = 'course' 
        AND c.id = ?";
$sum_files = $DB->get_records_sql($sql, array($id));
foreach ($sum_files as $file) {
    // get the full file path from the contenthash
    $first_part = substr($file->contenthash, 0, 2);
    $second_part = substr($file->contenthash, 2, 2);
    $file_loc = $CFG->dataroot . '/filedir/' . $first_part . '/' . $second_part . '/' . $file->contenthash;
    file_put_contents(__DIR__ . '/file_test.txt', $file_loc, FILE_APPEND);
    $fs = get_file_storage();
    $file_record = array(
        'contextid' => $new_course_context->id,
        'component' => 'course',
        'filearea' => 'overviewfiles',
        'itemid' => 0,
        'filepath' => '/',
        'filename' => $file->filename,
        'timecreated' => time(),
        'timemodified' => time()
    );
    file_put_contents(__DIR__ . '/file_test.txt', print_r($file_record, true), FILE_APPEND);
    $fs->create_file_from_pathname($file_record, $file_loc);
}
// revise any bundles that this course is associated with
$course_no = '';
while (empty($course_no)) {
    // query until the idnumber is returned
    $new_course_info = get_course($new_course->id);
    $course_no = $new_course_info->idnumber;
}
reviseCourseBundles($oldidnumber, $course_no);
// direct the user to the course list
$courseurl = new moodle_url('/course/management.php');
redirect($courseurl);
/**
 * @param string $oldidnumber
 * @param string $newidnumber
 */
function reviseCourseBundles($oldidnumber, $newidnumber) {
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
        $bundle_obj = new stdClass();
        $bundle_obj->id = $record->id;
        $bundle_obj->courses = $new_course_list;
        //file_put_contents(__DIR__ . '/update_bundle_result.txt', "BUNDLE OBJECT: " . print_r($bundle_obj, true) . "\n", FILE_APPEND);
        $DB->update_record('local_course_bundles', $bundle_obj);
        
        // get the courses involved
        $cselect = "idnumber IN ('" . str_replace(",", "','", $new_course_list) . "')";
        //file_put_contents(__DIR__ . '/update_bundle_result.txt', "SELECT COURSE: " . $cselect . "\n", FILE_APPEND);
        $courses = $DB->get_records_select('course', $cselect, array('id', 'fullname', 'credithrs', 'summary'));
        //file_put_contents(__DIR__ . '/update_bundle_result.txt', "COURSE RECORDS: " . print_r($courses, true) . "\n", FILE_APPEND);
        $bundle_descr = buildDescription($courses);
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
function buildDescription($courses) {
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