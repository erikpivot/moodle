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
require_once $CFG->libdir . '/adminlib.php';

// link generation
$reviseall = '/local/revise_courses/reviseall.php';
$revhome = '/local/revise_courses/index.php';
$baseurl = new moodle_url($revhome);

// configure the context of the page
//admin_externalpage_setup('local_revise_courses', '', null, $baseurl, array());
//$context = context_system::instance();

// the page title
$titlepage = get_string('pluginname', 'local_revise_courses');
$PAGE->set_url($baseurl);
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();
$homemessage = 'This feature will revise all currently active courses for the new school year.  Only use this feature if you 
                intend to revise every single active course in the system.';
echo $OUTPUT->notification($homemessage, 'notifyproblem');

// add button to revise all courses
$reviseallurl = new moodle_url($reviseall);
echo $OUTPUT->single_button($reviseallurl, get_string('reviseallurl', 'local_revise_courses'), 'get');
echo $OUTPUT->footer();