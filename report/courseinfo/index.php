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
 * Course completion progress report
 *
 * @package    report
 * @subpackage courseinfo
 * @copyright  2018 Pivot Creative
 * @author     Pivot Creative <info@pivotcreates.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->libdir . '/tablelib.php';

// optional parameters
$limitfrom = optional_param('page', 0, PARAM_INT);
//$limitnum = 3; // total items per page
//$limitfrom *= $limitnum;

// link generation
$basepage = '/report/courseinfo/index.php';
$baseurl = new moodle_url($basepage);

// retrieve a data for the report
$callbacks = report_courseinfo_get_list_records($limitfrom, $limitnum);

// the page title
$titlepage = get_string('pluginname', 'report_courseenrollments');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// table declaration
$table = new flexible_table('course_information_report');
//$table->pagesize(1, 3);

// customize the table
$table->define_columns(array('course', 'credithours', 'price'));
$table->define_headers(array(get_string('course', 'report_courseinfo'), get_string('credithours', 'report_courseinfo'), get_string('price', 'report_courseinfo')));
$table->define_baseurl($baseurl);
$table->setup();

foreach($callbacks as $callback) {
    // filling of information columns
    $coursecallback = html_writer::div($callback->fullname, 'course');
    $credithrscallback = html_writer::div($callback->credithrs, 'credithours');
    $pricecallback = html_writer::div($callback->courseprice, 'price');
    
    // adding data to the table
    $table->add_data(array($coursecallback, $credithrscallback, $pricecallback));
}

// display the table
$table->print_html();

echo $OUTPUT->footer();