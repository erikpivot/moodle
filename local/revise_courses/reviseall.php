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
 * Revise All Courses
 *
 * @package   local_revise_courses
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once __DIR__ . '/../../config.php';
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/util/ui/import_extensions.php');

// Must pass login
//require_login($course);
// Must hold restoretargetimport in the current course
//require_capability('moodle/restore:restoretargetimport', $context);

// retreive the current courses
echo $OUTPUT->header();

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
    //$course_no = date('Ymdhis') . "-" . $new_course->id;
    //$course_obj = new \stdClass();
    //$course_obj->id = $new_course->id;
    //$course_obj->idnumber = $course_no;
    //$DB->update_record('course', $course_obj);
    
    // copy the scorm and certificate modules associated with the revised course
    $scorm_mod = $DB->get_record('course_modules', array('course' => $course_info->id));
    
    // revise any bundles that this course is associated with
    reviseCourseBundles($oldidnumber, $course_no);
    
    // copy the activities
    // Load the course and context
    $courseid = $new_course->id; // course to import to
    $importcourseid = $course_info->id; // course to import from
    $restoretarget = 1; // always 1
    
    $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
    $context = context_course::instance($courseid);
    
    // Set up the page
    $PAGE->set_title(get_string('revisealltitle', 'local_revise_courses'));
    $PAGE->set_heading(get_string('revisealltitle', 'local_revise_courses'));
    $PAGE->set_url(new moodle_url('/local/revise_courses/import.php'));
    $PAGE->set_context($context);
    
    // Prepare the backup renderer
    $renderer = $PAGE->get_renderer('core','backup');
    
    // Load the course +context to import from
    $importcourse = $DB->get_record('course', array('id'=>$importcourseid), '*', MUST_EXIST);
    $importcontext = context_course::instance($importcourseid);
    
    // Make sure the user can backup from that course
    require_capability('moodle/backup:backuptargetimport', $importcontext);
    
    $bc = new backup_controller(backup::TYPE_1COURSE, $importcourse->id, backup::FORMAT_MOODLE,
                            backup::INTERACTIVE_YES, backup::MODE_IMPORT, $USER->id);
    
    $bc->get_plan()->get_setting('users')->set_status(backup_setting::LOCKED_BY_CONFIG);
    $settings = $bc->get_plan()->get_settings();
    
    $backupid = $bc->get_backupid();
    
    // process the import
    
    
    // Start the progress display - we split into 2 chunks for backup and restore.
    $progress = new \core\progress\display();
    $progress->start_progress('', 2);
    $bc->set_progress($progress);
    
    // Prepare logger for backup.
    $logger = new core_backup_html_logger($CFG->debugdeveloper ? backup::LOG_DEBUG : backup::LOG_INFO);
    $bc->add_logger($logger);
    
    $bc->finish_ui();
    
    // First execute the backup
    $bc->execute_plan();
    //$backup->destroy();
    //unset($backup);
    
    // Note that we've done that progress.
    $progress->progress(1);
    
    // Check whether the backup directory still exists. If missing, something
    // went really wrong in backup, throw error. Note that backup::MODE_IMPORT
    // backups don't store resulting files ever
    file_put_contents(__DIR__ . '/testing.txt', $CFG->tempdir . '/backup/' . $backupid);
    $tempdestination = $CFG->tempdir . '/backup/' . $backupid;
    if (!file_exists($tempdestination) || !is_dir($tempdestination)) {
        print_error('unknownbackupexporterror'); // shouldn't happen ever
    }
    
    // Prepare the restore controller. We don't need a UI here as we will just use what
    // ever the restore has (the user has just chosen).
    $rc = new restore_controller($backupid, $course->id, backup::INTERACTIVE_YES, backup::MODE_IMPORT, $USER->id, $restoretarget);
    
    // Start a progress section for the restore, which will consist of 2 steps
    // (the precheck and then the actual restore).
    $progress->start_progress('Restore process', 2);
    $rc->set_progress($progress);
    
    // Set logger for restore.
    $rc->add_logger($logger);
    
    // Convert the backup if required.... it should NEVER happen
    if ($rc->get_status() == backup::STATUS_REQUIRE_CONV) {
        $rc->convert();
    }
    // Mark the UI finished.
    $rc->finish_ui();
    // Execute prechecks
    $warnings = false;
    if (!$rc->execute_precheck()) {
        $precheckresults = $rc->get_precheck_results();
        if (is_array($precheckresults)) {
            if (!empty($precheckresults['errors'])) { // If errors are found, terminate the import.
                fulldelete($tempdestination);
    
                echo $renderer->precheck_notices($precheckresults);
                echo $OUTPUT->continue_button(new moodle_url('/local/revise_courses/index.php'));
                echo $OUTPUT->footer();
                die();
            }
            if (!empty($precheckresults['warnings'])) { // If warnings are found, go ahead but display warnings later.
                $warnings = $precheckresults['warnings'];
            }
        }
    }
    if ($restoretarget == backup::TARGET_CURRENT_DELETING || $restoretarget == backup::TARGET_EXISTING_DELETING) {
        restore_dbops::delete_course_content($course->id);
    }
    // Execute the restore.
    $rc->execute_plan();
    
    // Delete the temp directory now
    fulldelete($tempdestination);
    
    // End restore section of progress tracking (restore/precheck).
    $progress->end_progress();
    
    // All progress complete. Hide progress area.
    $progress->end_progress();
    
    // Display a notification and a continue button
    if ($warnings) {
        echo $OUTPUT->box_start();
        echo $OUTPUT->notification(get_string('warning'), 'notifyproblem');
        echo html_writer::start_tag('ul', array('class'=>'list'));
        foreach ($warnings as $warning) {
            echo html_writer::tag('li', $warning);
        }
        echo html_writer::end_tag('ul');
        echo $OUTPUT->box_end();
    }
    echo $OUTPUT->notification($course_info->fullname . ' has been revised.', 'notifysuccess');
    
    // Get and display log data if there was any.
    $loghtml = $logger->get_html();
    if ($loghtml != '') {
        echo $renderer->log_display($loghtml);
    }
    
    
    // log the update
    $rev_obj = new \stdClass();
    $rev_obj->newcourseidnumber = $new_course->id;
    $rev_obj->oldcourseidnumber = $oldidnumber;
    $rev_obj->coursename = $course_info->fullname;
    $rev_obj->revisiondate = time();
    $DB->insert_record('local_revise_courses', $rev_obj);
}
echo $OUTPUT->continue_button(new moodle_url('/course/management.php'));
echo $OUTPUT->footer();
die();

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