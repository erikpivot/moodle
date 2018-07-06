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
 * @package   report_courseinfo
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
function report_courseinfo_get_list_records($limitfrom = 0, $limitnum = 0) {
    global $DB;
   
   // retrieve all of the active courses
   $courses = $DB->get_records('course', array('category' => 1), '', 'id, fullname, credithrs, courseprice', $limitfrom, $limitnum);
   
    return $courses;
}