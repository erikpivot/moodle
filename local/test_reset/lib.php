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
 * @package   local_test_reset
 * @copyright 2019 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

/**
 * Getting a list of all tests that are frozen
 * 
 * @param number $limitfrom
 * @param number $limitnum
 * @return array
 */
function local_test_reset_list_records($limitfrom = 0, $limitnum = 0) {
    global $DB;
    
    $sql = "SELECT CONCAT(usr.firstname, \" \", usr.lastname, \" (\", usr.username, \")\") AS username, st.userid, st.scormid, 
            st.value AS coursestarttime, crs.fullname AS course 
            FROM {scorm_scoes_track} st
            JOIN {user} usr ON st.userid = usr.id
            JOIN {scorm} srm ON st.scormid = srm.id
            JOIN {course} crs ON srm.course = crs.id
            WHERE st.element = 'x.start.time'
            AND st.userid IN (SELECT userid FROM {scorm_scoes_track} WHERE element = 'cmi.core.lesson_status' AND value = 'failed')
            AND st.scormid IN (SELECT scormid FROM {scorm_scoes_track} WHERE element = 'cmi.core.lesson_status' AND value = 'failed')";
    $listtritems = $DB->get_records_sql($sql);
    
    return $listtritems;
}


/**
 * Delete the frozen test items
 * 
 * @param number $userid
 * @param number $scormid
 */
function local_test_reset_remove_record($userid = 0, $scormid = 0) {
    global $DB;

    // remove the test items
    $select = "element LIKE 'cmi.interactions_%' AND userid = ? AND scormid = ?";
    $DB->delete_records_select('scorm_scoes_track', $select, array($userid, $scormid));
    
    // set the course status to incomplete
    $sql = "UPDATE {scorm_scoes_track} SET value = 'incomplete' WHERE element = 'cmi.core.lesson_status'
            AND userid = ? AND scormid = ?";
    $DB->execute($sql, array($userid, $scormid));
}
