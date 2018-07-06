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
 * @package   report_approvalnumstate
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
function report_approvalnumstate_get_list_records($limitfrom = 0, $limitnum = 0) {
    global $DB;
    
    // get all course data
    $courses = $DB->get_records('course', array('category' => 1), '', '*', $limitfrom, $limitnum);
    
    $all_states = array(
        'pace' => get_string('customapprove', 'report_approvalnumstate'),
        'al'=>'Alabama',
        'ak'=>'Alaska',
        'az'=>'Arizona',
        'ar'=>'Arkansas',
        'ca'=>'California',
        'co'=>'Colorado',
        'ct'=>'Connecticut',
        'de'=>'Delaware',
        'dc'=>'District of Columbia',
        'fl'=>'Florida',
        'ga'=>'Georgia',
        'hi'=>'Hawaii',
        'id'=>'Idaho',
        'il'=>'Illinois',
        'in'=>'Indiana',
        'ia'=>'Iowa',
        'ks'=>'Kansas',
        'ky'=>'Kentucky',
        'la'=>'Louisiana',
        'me'=>'Maine',
        'md'=>'Maryland',
        'ma'=>'Massachusetts',
        'mi'=>'Michigan',
        'mn'=>'Minnesota',
        'ms'=>'Mississippi',
        'mo'=>'Missouri',
        'mt'=>'Montana',
        'ne'=>'Nebraska',
        'nv'=>'Nevada',
        'nh'=>'New Hampshire',
        'nj'=>'New Jersey',
        'nm'=>'New Mexico',
        'ny'=>'New York',
        'nc'=>'North Carolina',
        'nd'=>'North Dakota',
        'oh'=>'Ohio',
        'ok'=>'Oklahoma',
        'or'=>'Oregon',
        'pa'=>'Pennsylvania',
        'ri'=>'Rhode Island',
        'sc'=>'South Carolina',
        'sd'=>'South Dakota',
        'tn'=>'Tennessee',
        'tx'=>'Texas',
        'ut'=>'Utah',
        'vt'=>'Vermont',
        'va'=>'Virginia',
        'wa'=>'Washington',
        'wv'=>'West Virginia',
        'wi'=>'Wisconsin',
        'wy'=>'Wyoming'
    );
    
    // array for the return
    $returndata = array();
    foreach($all_states as $key => $value) {
        $returndata[$key] = array(
            'title' => $value,
            'courses' => array()
        );
    }
    
    // go through the courses and fill in the results
    foreach($courses as $course) {
        // check all state columns to see if a number is present
        foreach($all_states as $key => $value) {
            $objval = $key . 'approvalno';
            if (!empty($course->$objval)) {
                // add the result to the state
                $returndata[$key]['courses'][] = array(
                    'name' => $course->fullname,
                    'approvalno' => $course->$objval
                );
            }
        }
    }
   
    return $returndata;
}