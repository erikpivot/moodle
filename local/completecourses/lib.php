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
 * Library code that contains the interfaces to the database
 *
 * @package   local\completecourses
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

/**
 * Retrieves a list of courses and their completion status for a user
 * 
 * This function generates the list of courses registered to users.  The list contains the user's name,
 * the course name, completion state and completion date.  Also provides the ids needed to perform an update
 * to a completion record.
 * 
 * @param string $firstname user's first name
 * @param string $lastname user's last name
 * @param int $limitfrom record limit start (NOT USED AT THE MOMENT)
 * @param int $limitnum record limit end (NOT USED AT THE MOMENT)
 * @return array $return_data list of users with their courses
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
            $sql = "SELECT crs.fullname, cmods.id as moduleid, crs.id as courseid, customcrt.id as certid, enroll.timecreated
                    FROM
                    {user_enrolments} uenroll
                    JOIN {enrol} enroll
                    ON uenroll.enrolid = enroll.id
                    JOIN {course_modules} cmods
                    ON enroll.courseid = cmods.course
                    JOIN {course} crs
                    ON cmods.course = crs.id
                    JOIN {customcert} customcrt
                    ON customcrt.course = crs.id
                    WHERE uenroll.userid = ? 
                    AND cmods.module = 18";
            $enroll_info = $DB->get_records_sql($sql, array($userid->id));
            // get the completion information and build the return data
            foreach($enroll_info as $info) {
                $ret_info = array(
                    'userid' => $userid->id,
                    'user' => $userid->firstname . ' ' . $userid->lastname,
                    'coursename' => $info->fullname,
                    'completionid' => 0,
                    'completionstate' => 0,
                    'issuedate' => '',
                    'certificateissueid' => 0,
                    'certificateid' => $info->certid,
                    'enrolldate' => $info->timecreated,
                    'moduleid' => $info->moduleid
                );
                $sql = "SELECT id, completionstate, timemodified FROM {course_modules_completion} 
                        WHERE userid = ? AND coursemoduleid = ?";
                $complete_info = $DB->get_record_sql($sql, array($userid->id, $info->moduleid));
                if (!empty($complete_info)) {
                    // there is completion information for this user
                    $ret_info['completionid'] = $complete_info->id;
                    $ret_info['completionstate'] = $complete_info->completionstate;
                    $ret_info['issuedate'] = $complete_info->timemodified;

                    // retrieve the certificate issue date
                    $sql = "SELECT certissues.id FROM mdl_customcert_issues certissues
                            JOIN mdl_customcert cert ON certissues.customcertid = cert.id
                            WHERE certissues.userid = ? AND cert.course = ?";
                    $cert_info = $DB->get_record_sql($sql, array($userid->id, $info->courseid));
                    if (!empty($cert_info)) {
                        $ret_info['certificateissueid'] = $cert_info->id;
                    }
                }
                
                // add to the return data
                $return_data[] = $ret_info;
            }
        }   
    }
    
    return $return_data;
}

/**
 * Updates the completion record for the user in the database
 * 
 * @param object $data contains the updated information
 * @return boolean whether the update succeeded or not
 */
function local_completecourses_update_record($data) {
    global $DB;
    
    //file_put_contents(__DIR__ . '/testing.txt', print_r($data, true) . "\n");
    //echo print_r($data, true);
    
    // update or add a course completion
    if (0 == $data->completeid) {
        // create a new completion record
        $ins_obj = new stdClass();
        $ins_obj->coursemoduleid = $data->moduleid;
        $ins_obj->userid = $data->userid;
        $ins_obj->completionstate = $data->completionstate;
        $ins_obj->viewed = 1;
        $ins_obj->timemodified = $data->completiondate;
        $result = $DB->insert_record('course_modules_completion', $ins_obj);

        // add the certificate
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $ins_cert = new stdClass();
        $ins_cert->userid = $data->userid;
        $ins_cert->customcertid = $data->customcertid;
        $ins_cert->code = $randomString;
        $ins_cert->emailed = 0;
        $ins_cert->timecreated = $data->completiondate;
        $result = $DB->insert_record('customcert_issues', $ins_cert);
    } else {
        // update the course module completion
        $completeinfo = new stdClass();
        $completeinfo->id = $data->completeid;
        $completeinfo->completionstate = $data->completionstate;
        $completeinfo->timemodified = $data->completiondate;
        $result = $DB->update_record('course_modules_completion', $completeinfo, false);

        // update the certificate issue date
        $certinfo = new stdClass();
        $certinfo->id = $data->certissueid;
        $certinfo->timecreated = $data->completiondate;
        //echo print_r($certinfo, true);
        
        $result = $DB->update_record('customcert_issues', $certinfo, false);
        //echo boolval($result);
    }
    
    return boolval($result);
}