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
 * @subpackage studentinformation
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
$studfirstname = optional_param('studentfirstname', '', PARAM_TEXT);
$studlastname = optional_param('studentlastname', '', PARAM_TEXT);
$limitfrom = optional_param('page', 0, PARAM_INT);
//$limitnum = 3; // total items per page
//$limitfrom *= $limitnum;

// link generation
$basepage = '/report/studentinformation/index.php';
$baseurl = new moodle_url($basepage);

// configure the context of the page
//admin_externalpage_setup('report_courseenrollments', '', null, $baseurl, array());
//$context = context_system::instance();

// add filter form
$mform = new student_information_filters_form($PAGE->url);
if ($data = $mform->get_data()) {
    $query_str = "?studentfirstname=" . $data->studentfirstname . "&studentlastname=" . $data->studentlastname;
    redirect($baseurl . $query_str);
} else {
    // set the form data (if necessary)
    $default_data = new stdClass();
    if (!empty($studfirstname)) {
        $default_data->studentfirstname = $studfirstname;
    }
    
    if (!empty($studlastname)) {
        $default_data->studentlastname = $studlastname;
    }
    
    $mform->set_data($default_data);
}



// retrieve a data for the report
$callbacks = report_studentinformation_get_list_records($studfirstname, $studlastname);

// the page title
$titlepage = get_string('pluginname', 'report_studentinformation');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/report/studentinformation/js/printThis.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/report/studentinformation/js/custom.js'));
echo $OUTPUT->header();

// table declaration
$table = new flexible_table('studentinformation_report');

// customize the table
$table->define_columns(array('studentname', 'coursename', 'credithours', 'coursetime', 'completedate'));
$header_arr = array(
    get_string('studentname', 'report_studentinformation'),
    get_string('coursename', 'report_studentinformation'),
    get_string('credithours', 'report_studentinformation'),
    get_string('coursetime', 'report_studentinformation'),
    get_string('completedate', 'report_studentinformation')
);
$table->define_headers($header_arr);
$table->define_baseurl($baseurl);
$table->setup();
$table->set_attribute('id', 'studs_info_report');

// go through the results
foreach($callbacks['results'] as $callback) {
    // filling of information columns
    $studnamecallback = html_writer::div($callback['student_name'], 'studentname');
    $coursecallback = html_writer::div($callback['course_name'], 'coursename');
    $credithourscallback = html_writer::div($callback['credit_hours'], 'credithours');
    $totaltimecallback = html_writer::div($callback['total_time'], 'totalcoursetime');
    $completedatecallback = html_writer::div($callback['date_completed'], 'completiondate');
    
    // adding data to the table
    $table->add_data(array($studnamecallback, $coursecallback, $credithourscallback, $totaltimecallback, $completedatecallback));
}
// output the form
$mform->display();
echo '<a href="#" id="print-btn" class="btn btn-primary">Print</a><br />';

// output the results table
$table->print_html();

// add kb item button
echo $OUTPUT->footer();