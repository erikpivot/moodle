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

$id = required_param('id', PARAM_INT); // Course ID to copy

// get the course information to copy
$course = get_course($id);

// modify the name of the current course
$course_revision = "Created: " . date('m-d-Y', $course->timecreated) . " Ended:" . date('m-d-Y');
$course_short = $course->shortname . " R." . $course->revisionno;
$old_course_obj = new stdClass();
$old_course_obj->id = $id;
$old_course_obj->revisiontext = $course_revision;
$old_course_obj->shortname = $course_short;
$old_course_obj->productid = 0;
$DB->update_record('course', $old_course_obj);

// create the duplicate course
$course->idnumber = '';
$course->revisionno = $course->revisionno + 1;
$course->revisiontext = '';
$new_course = create_course($course);

// update course format options
$old_course_format_options = course_get_format($id)->get_format_options();
//echo print_r($old_course_format_options, true);
course_get_format($new_course->id)->update_course_format_options($old_course_format_options);

// copy tags
$old_course_tags = core_tag_tag::get_item_tags_array('core', 'course', $id);
//echo print_r($old_course_tags, true);
core_tag_tag::set_item_tags('core', 'course', $new_course->id, context_course::instance($new_course->id), $old_course_tags);

// set the new course id
/*
$course_obj = new stdClass();
$course_obj->id = $new_course->id;
$course_obj->idnumber = date('Ymdhis') . "-" . $new_course->id;
$DB->update_record('course', $course_obj);
*/

// direct the user to the course list
$courseurl = new moodle_url('/course/management.php');
redirect($courseurl);