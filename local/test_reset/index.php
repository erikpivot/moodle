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
 * Test Reset Manager
 *
 * @package   local_test_reset
 * @copyright 2019 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->libdir . '/tablelib.php';

// optional parameters
$userid = optional_param('userid', 0, PARAM_INT);
$scormid = optional_param('scormid', 0, PARAM_INT);

// link generation
$managertritem = '/local/test_reset/index.php';
$deletetritem = '/local/test_reset/delete.php';
$baseurl = new moodle_url($managertritem);

// configure the context of the page
admin_externalpage_setup('local_test_reset', '', null, $baseurl, array());
$context = context_system::instance();

// delete the kb item
if (boolval($deleteid)) {
    local_test_reset_remove_record($deleteid);
    redirect($PAGE->url, get_string('tritemdeleted', 'local_knowledge_base'));
}

// retrieve a list of services
$callbacks = local_test_reset_list_records();

// the page title
$titlepage = get_string('pluginname', 'local_test_reset');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();
?>
<div>
    <h3>Below is a list of users where their test is frozen.  Click the trash can icon to reset their test so they can complete the course.</h3>
</div>
<?php
// table declaration
$table = new flexible_table('users_test_frozen');

// customize the table
$table->define_columns(array('user', 'course', 'coursestart', 'actions'));
$table->define_headers(array(get_string('user', 'local_test_reset'), get_string('course', 'local_test_reset'), get_string('coursestart', 'local_test_reset'), new lang_string('actions', 'moodle')));
$table->define_baseurl($baseurl);
$table->setup();

foreach($callbacks as $callback) {
    // filling of information columns
    $usercallback = html_writer::div($callback->username, 'user');
    $coursecallback = html_writer::div($callback->course, 'course');
    $coursestartcallback = html_writer::div(date('Y-m-d h:i:s', $callback->coursestarttime), 'coursestart');
    
    // link to remove
    $deletelink = new moodle_url($deletetritem, array('userid' => $callback->userid, 'scormid' => $callback->scormid));
    $deleteitem = $OUTPUT->action_icon($deletelink, new pix_icon('t/delete', new lang_string('delete', 'moodle')));
    
    // adding data to the table
    $table->add_data(array($usercallback, $coursecallback, $coursestartcallback, $deleteitem));
}

// display the table
$table->print_html();

// output the footer
echo $OUTPUT->footer();