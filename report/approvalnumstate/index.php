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
 * @subpackage approvalnumstate
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
$basepage = '/report/approvalnumstate/index.php';
$baseurl = new moodle_url($basepage);

// configure the context of the page
//admin_externalpage_setup('report_courseenrollments', '', null, $baseurl, array());
//$context = context_system::instance();



// retrieve a data for the report
$callbacks = report_approvalnumstate_get_list_records($limitfrom, $limitnum);

// the page title
$titlepage = get_string('pluginname', 'report_approvalnumstate');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// go through the results
foreach($callbacks as $callback) {
    // output the header for the table
    echo '<h2>' . $callback['title'] . '</h2>';
    // table declaration
    $table = new flexible_table('approvalnumstate_report_' . $callback['title']);
    //$table->pagesize(1, 3);
    
    // customize the table
    $table->define_columns(array('course', 'approvalno'));
    $table->define_headers(array(get_string('course', 'report_approvalnumstate'), get_string('approvalno', 'report_approvalnumstate')));
    $table->define_baseurl($baseurl);
    $table->setup();
    
    foreach($callback['courses'] as $callback2) {
        // filling of information columns
        $coursecallback = html_writer::div($callback2['name'], 'course');
        $approvalnocallback = html_writer::div($callback2['approvalno'], 'approvalno');
        
        // adding data to the table
        $table->add_data(array($coursecallback, $approvalnocallback));
    }
    $table->print_html();
    
}

// add kb item button
echo $OUTPUT->footer();