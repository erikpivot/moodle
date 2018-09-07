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
 * @package   report_studentinformation
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
function report_studentinformation_get_list_records($firstname = '', $lastname = '', $limitfrom = 0, $limitnum = 0) {
    global $DB;
    
    // set up the return array
    $resultset = array(
        'total_results' => 0,
        'results' => array()
    );
    $result_idx = 0;
    // only query results if a name is sent
    if (!empty($firstname) || !empty($lastname)) {
        $addand = false;
        $sql = "SELECT id, firstname, lastname FROM {user} WHERE ";
        if (!empty($firstname)) {
            $sql .= "firstname LIKE '%" . $firstname . "%'";
            $addand = true;
        }
        if (!empty($lastname)) {
            if ($addand) {
                $sql .= " AND ";
            }
            $sql .= "lastname LIKE '%" . $lastname . "%'";
        }
        $students = $DB->get_records_sql($sql);
        if (sizeof($students) > 0) {
            // found the students, get their enrollment information
            foreach($students as $student_info) {
                $sql = "SELECT a.id, a.fullname, a.credithrs FROM mdl_course a
                        INNER JOIN mdl_enrol b
                        ON a.id = b.courseid
                        INNER JOIN mdl_user_enrolments c
                        ON b.id = c.enrolid
                        WHERE c.userid = " . $student_info->id;
                $courses = $DB->get_records_sql($sql);
                
                // continue as long as the student is enrolled in courses
                if (sizeof($courses) > 0) {
                    foreach($courses as $course_info) {
                        // start saving the course information
                        $resultset['results'][$result_idx] = array(
                            'student_name' => $student_info->firstname . " " . $student_info->lastname,
                            'course_name' => $course_info->fullname,
                            'credit_hours' => $course_info->credithrs,
                            'total_time' => '00:00:00.00',
                            'date_completed' => 'NOT COMPLETE'
                        );
                        // get the amount of time spent in the course
                        $sql = "SELECT a.value FROM mdl_scorm_scoes_track a
                                INNER JOIN mdl_scorm b
                                ON a.scormid = b.id
                                WHERE b.course = " . $course_info->id . "
                                AND a.element = 'cmi.core.total_time'
                                AND a.userid = " . $student_info->id;
                        $timespent = $DB->get_records_sql($sql);
                        if (sizeof($timespent) > 0) {
                            // found the time spent
                            foreach($timespent as $time_info) {
                                $resultset['results'][$result_idx]['total_time'] = $time_info->value;
                                break;
                            }
                        }
                        
                        // grab the completion status of the course
                        $sql = "SELECT b.completionstate, b.timemodified FROM mdl_course_modules a
                                INNER JOIN mdl_course_modules_completion b
                                ON a.id = b.coursemoduleid
                                WHERE a.course = " . $course_info->id . " 
                                AND b.userid = " . $student_info->id;
                        $completions = $DB->get_records_sql($sql);
                        foreach($completions as $complete_info) {
                            $resultset['results'][$result_idx]['date_completed'] = date("m/d/Y", $complete_info->timemodified);
                            break;
                        }
                    }
                    // increment the result index
                    $result_idx++;
                }
            }
        }
    }
    return $resultset;
}