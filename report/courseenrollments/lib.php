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
 * @package   report_courseenrollments
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

/**
 * Getting a list of all kb entries
 * 
 * @param number $limitfrom
 * @param number $limitnum
 * @return array
 */
function report_courseenrollments_get_list_records($startdt = 0, $enddt = 0, $limitfrom = 0, $limitnum = 0, $lifeonly = 0) {
    global $DB;
   
   if (1 == $lifeonly) {
       // get all life university states
       $states = $DB->get_records('local_state_settings', array('customapprove' => 2), '', 'state', $limitfrom, $limitnum);
       $column_filter = '';
       foreach($states as $state) {
           if (!empty($column_filter)) {
               $column_filter .= " AND ";
           }
           $column_filter .= $state->state . "approvalno != ''";
       }
       
       // nab the courses
       $sql = "SELECT id, fullname FROM {course} WHERE category = 1 AND " . $column_filter;
       $courses = $DB->get_records_sql($sql);
   } else {
       // retrieve all of the courses
       $courses = $DB->get_records('course', array('category' => 1), '', 'id, fullname', $limitfrom, $limitnum);
   }
   
   $resultset = array(
        'total_courses' => 0,
        'results' => array()
   );
   $idx = 0;
   foreach($courses as $course) {
       $resultset['total_courses']++;
       $resultset['results'][$idx]['name'] = $course->fullname;
       $filter_arr = array();
       $filter_arr[0] = $course->id;
       // get enrollment information for the course to get the total started
       $sql = "SELECT COUNT(a.id) AS Total_Enrollments FROM {enrol} a
                INNER JOIN {user_enrolments} b
                ON a.id = b.enrolid
                WHERE a.courseid = ?";
        if (!empty($startdt)) {
            $sql .= " AND b.timecreated BETWEEN ? AND ?";
            $filter_arr[1] = $startdt;
            $filter_arr[2] = $enddt;
        }
        
        $total_enroll = $DB->get_record_sql($sql, $filter_arr);
        //file_put_contents(__DIR__ . '/testing.txt', print_r($total_enroll, true));
        
        // get the total completed
        $sql = "SELECT COUNT(a.id) AS Total_Complete FROM {course_modules_completion} a
                INNER JOIN {course_modules} b
                ON a.coursemoduleid = b.id AND a.completionstate = 1
                INNER JOIN {scorm} c
                ON c.id = b.instance
                WHERE b.course = ?";
        if (!empty($startdt)) {
            $sql .= " AND a.timemodified BETWEEN ? AND ?";
        }
        $total_complete = $DB->get_record_sql($sql, $filter_arr);
        //file_put_contents(__DIR__ . '/testing.txt', print_r($total_complete, true), FILE_APPEND);
        
        $resultset['results'][$idx]['total_enroll'] = $total_enroll->total_enrollments;
        $resultset['results'][$idx]['total_complete'] = $total_complete->total_complete;
        // increment the index
        $idx++;
   }
   
    return $resultset;
}