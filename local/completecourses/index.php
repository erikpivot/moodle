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
 * Knowledge Base Entries Manager
 *
 * @package   local_completecourses
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->libdir . '/tablelib.php';
require_once __DIR__ . '/classes/search_form.php';

// optional parameters
$firstname = optional_param('userfirstname', '', PARAM_TEXT);
$lastname = optional_param('userlastname', '', PARAM_TEXT);

// link generation
$editcompleteitem = '/local/completecourses/editcoursecompletion.php';
$managercompleteitem = '/local/completecourses/index.php';
$baseurl = new moodle_url($managercompleteitem);

// configure the context of the page
admin_externalpage_setup('local_completecourses', '', null, $baseurl, array());
$context = context_system::instance();

// retrieve a list of course completions of a user
$callbacks = local_completecourses_get_list_records($firstname, $lastname);

// the page title
$titlepage = get_string('pluginname', 'local_completecourses');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// table declaration
$table = new flexible_table('course_completion_list');

// customize the table
$table->define_columns(array('user', 'course', 'completionstate', 'completiondate', 'actions'));
$table->define_headers(array(get_string('user', 'local_completecourses'), get_string('course', 'local_completecourses'), get_string('iscompleted', 'local_completecourses'), get_string('completiondate', 'local_completecourses'), new lang_string('actions', 'moodle')));
$table->define_baseurl($baseurl);
$table->setup();

foreach($callbacks as $callback) {
    // filling of information columns
    $usercallback = html_writer::div($callback['user'], 'user');
    $coursecallback = html_writer::div($callback['coursename'], 'coursename');
    $completestate = ($callback['completionstate'] == 1 ? 'Yes' : 'No');
    $completionstatecallback = html_writer::div($completestate, 'completionstate');
    $completiondatecallback = html_writer::div(date('m/d/Y', $callback['issuedate']), 'completiondate');
    
    // link for editing
    $editlink = new moodle_url($editcompleteitem, array('completionid' => $callback['completionid'], 'completionstate' => $callback['completionstate'], 'completiondate' => $callback['issuedate'], 'certissueid' => $callback['certificateissueid'], 'userfirstname' => $firstname, 'userlastname' => $lastname));
    $edititem = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', new lang_string('edit', 'moodle')));
    
    // adding data to the table
    $table->add_data(array($usercallback, $coursecallback, $completionstatecallback, $completiondatecallback, $edititem));
}

// Form to filter tags.
/*
print('<form class="completion-filter-form" method="get" action="'.$CFG->wwwroot.'/local/completecourses/index.php">');
print('<div class="completion-management-form generalbox">'.
    '<label class="accesshide" for="id_firstname_filter">'. get_string('search_firstname', 'local_completecourses') .'</label>'.
    '<input id="id_firstname_filter" name="firstname_filter" type="text" value=' . $firstname . '>'.
    '<label class="accesshide" for="id_lastname_filter">'. get_string('search_lastname', 'local_completecourses') .'</label>'.
    '<input id="id_lastname_filter" name="lastname_filter" type="text" value=' . $lastname . '>'.
    '<input value="'. get_string('search', 'local_completecourses') .'" type="submit" class="btn btn-secondary"> '.
    '</div>');
print('</form>');
*/

// display the search form
$mform = new search_form($PAGE->url);
$mform->display();

// display the table
$table->print_html();

echo $OUTPUT->footer();