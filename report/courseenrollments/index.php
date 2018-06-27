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
 * @subpackage courseenrollments
 * @copyright  2018 Pivot Creative
 * @author     Pivot Creative <info@pivotcreates.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/classes/filters_form.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->libdir . '/tablelib.php';

// optional parameters
$startdt = optional_param('startdate', 0, PARAM_INT);
$enddt = optional_param('enddate', 0, PARAM_INT);
$life = optional_param('life', 0, PARAM_INT);
$limitfrom = optional_param('page', 0, PARAM_INT);
//$limitnum = 3; // total items per page
//$limitfrom *= $limitnum;

// link generation
$basepage = '/report/courseenrollments/index.php';
$baseurl = new moodle_url($basepage);

// configure the context of the page
//admin_externalpage_setup('report_courseenrollments', '', null, $baseurl, array());
//$context = context_system::instance();

// add filter form
$mform = new course_enrollment_filters_form($PAGE->url);
if ($data = $mform->get_data()) {
    $query_str = "?startdate=" . $data->startdate . "&enddate=" . $data->enddate;
    $query_str .= "&life=" . $data->lifeonly;
    redirect($baseurl . $query_str);
} else {
    // set the form data (if necessary)
    $default_data = new stdClass();
    if (!empty($startdt)) {
        $default_data->startdate = $startdt;
    }
    
    if (!empty($enddt)) {
        $default_data->enddate = $enddt;
    }
    
    if (!empty($life)) {
        $default_data->lifeonly = $life;
    }
    
    $mform->set_data($default_data);
}

// retrieve a data for the report
$callbacks = report_courseenrollments_get_list_records($startdt, $enddt, $limitfrom, $limitnum, $life);

// the page title
$titlepage = get_string('pluginname', 'report_courseenrollments');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// table declaration
$table = new flexible_table('course_enrollment_report');
//$table->pagesize(1, 3);

// customize the table
$table->define_columns(array('course', 'totalenrolled', 'totalcompleted'));
$table->define_headers(array(get_string('course', 'report_courseenrollments'), get_string('totalenrolled', 'report_courseenrollments'), get_string('totalcompleted', 'report_courseenrollments')));
$table->define_baseurl($baseurl);
$table->setup();

foreach($callbacks['results'] as $callback) {
    // filling of information columns
    $coursecallback = html_writer::div($callback['name'], 'course');
    $totalenrollcallback = html_writer::div($callback['total_enroll'], 'totalenrollments');
    $totalcompletecallback = html_writer::div($callback['total_complete'], 'totalcompleted');
    
    // adding data to the table
    $table->add_data(array($coursecallback, $totalenrollcallback, $totalcompletecallback));
}

$mform->display();

// display the table
$table->print_html();

// add kb item button
echo $OUTPUT->footer();