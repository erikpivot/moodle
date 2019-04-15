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
 * @package   local_completecourses
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
function local_completecourses_get_list_records($firstname = '', $lastname = '', $limitfrom = 0, $limitnum = 0) {
    global $DB;
    
    $return_data = array();
    
    if (!empty($firstname) || !empty($lastname)) {
        // find the user ids based on the user information sent
        $sql = "SELECT id, firstname, lastname FROM {user} WHERE ";
        if (!empty($firstname)) {
            $sql .= "firstname LIKE '%" . $firstname . "%'";
        }
        
        if (!empty($lastname)) {
            if (!empty($firstname)) {
                $sql .= " AND ";
            }
            $sql .= "lastname LIKE '%" . $lastname . "%'";
        }
        
        $userids = $DB->get_records_sql($sql, array());
        
        foreach ($userids as $userid) {
            // grab the completion state of each course for the user
            $sql = "SELECT modcompl.id AS completeid, modcompl.completionstate, modcompl.timemodified, crs.fullname, 
                    certissues.id AS certissueid
                    FROM
                    {user_enrolments} uenroll
                    JOIN {enrol} enroll
                    ON uenroll.enrolid = enroll.id
                    JOIN {course_modules} cmods
                    ON enroll.courseid = cmods.course
                    JOIN {course} crs
                    ON cmods.course = crs.id
                    JOIN {course_modules_completion} modcompl
                    ON cmods.id = modcompl.coursemoduleid
                    JOIN {customcert} cert
                    ON crs.id = cert.course
                    JOIN {customcert_issues} certissues
                    ON cert.id = certissues.customcertid
                    WHERE uenroll.userid = ? AND modcompl.userid = ?
                    AND certissues.userid = ?
                    AND cmods.module = 18";
            $course_completion_info = $DB->get_records_sql($sql, array($userid->id,$userid->id,$userid->id));
            
            // build the return data
            foreach($course_completion_info as $info) {
                $return_data[] = array(
                    'userid' => $userid->id,
                    'user' => $userid->firstname . ' ' . $userid->lastname,
                    'coursename' => $info->fullname,
                    'completionid' => $info->completeid,
                    'completionstate' => $info->completionstate,
                    'issuedate' => $info->timemodified,
                    'certificateissueid' => $info->certissueid
                );   
            }
        }   
    }
    
    return $return_data;
}

/**
 * Update the record in the database
 * 
 * @param object $data
 * @param boolean $insert
 * @return boolean
 */
function local_completecourses_update_record($data) {
    global $DB;
    
    //echo print_r($data, true);
    
    $completeinfo = new stdClass();
    $completeinfo->id = $data->completeid;
    $completeinfo->completionstate = $data->completionstate;
    $completeinfo->timemodified = $data->completiondate;
    
    // update the course module completion
    $result = $DB->update_record('course_modules_completion', $completeinfo, false);
    
    // update the certificate issue date
    $certinfo = new stdClass();
    $certinfo->id = $data->certissueid;
    $certinfo->timecreated = $data->completiondate;
    //echo print_r($certinfo, true);
    
    $result = $DB->update_record('customcert_issues', $certinfo, false);
    //echo boolval($result);
    
    return boolval($result);
}