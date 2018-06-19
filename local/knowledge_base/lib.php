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
 * @package   local_knowledge_base
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
function local_knowledge_base_get_list_records($limitfrom = 0, $limitnum = 0) {
    global $DB;
    
    $listkbitems = $DB->get_records('local_knowledge_base', null, 'id', '*', $limitfrom, $limitnum);
    
    return $listkbitems;
}

/**
 * Getting information about the kb entry
 * 
 * @param number $kbitemid
 * @return object
 */
function local_knowledge_base_get_record($kbitemid = 0) {
    global $DB;
    
    $kbitemrecord = $DB->get_record('local_knowledge_base', array('id' => $kbitemid), '*', MUST_EXIST);
    // interperet the description of the html editor
    $kbitemrecord->content = array('text' => $kbitemrecord->content, 'format' => 1);
    
    return $kbitemrecord;
}

/**
 * Clear the database table
 */
function local_knowledge_base_remove_list_records() {
    global $DB;
    
    $DB->delete_records('local_knowledge_base', null);
}

/**
 * Delete the record
 * 
 * @param number $kbitemid
 */
function local_knowledge_base_remove_record($kbitemid = 0) {
    global $DB;
    $DB->delete_records('local_knowledge_base', array('id' => $kbitemid));
}

/**
 * Update the record in the database
 * 
 * @param object $data
 * @param boolean $insert
 * @return boolean
 */
function local_knowledge_base_update_record($data, $insert = true) {
    global $DB;
    
    // rebuild the data content property to the text of the html editor
    $data->content = $data->content['text'];

    if (boolval($insert)) {
        $data->posteddate = strtotime(date('Y-m-d h:i:s'));
        $data->updateddate = $data->posteddate;
        $result = $DB->insert_record('local_knowledge_base', $data, true, false);
    } else {
        $data->updateddate = strtotime(date('Y-m-d h:i:s'));
        $result = $DB->update_record('local_knowledge_base', $data, false);
    }
    
    return boolval($result);
}