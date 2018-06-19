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
 * @package   local_knowledge_base
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';
require_once $CFG->libdir . '/adminlib.php';
require_once $CFG->libdir . '/tablelib.php';

// optional parameters
$deleteid = optional_param('deleteid', 0, PARAM_INT);

// link generation
$editkbitem = '/local/knowledge_base/editkbitem.php';
$managerkbitem = '/local/knowledge_base/index.php';
$deletekbitem = '/local/knowledge_base/delete.php';
$viewkbitem = '/local/knowledge_base/viewkbitem.php';
$baseurl = new moodle_url($managerkbitem);

// configure the context of the page
admin_externalpage_setup('local_knowledge_base', '', null, $baseurl, array());
$context = context_system::instance();

// delete the kb item
if (boolval($deleteid)) {
    local_knowledge_base_remove_record($deleteid);
    redirect($PAGE->url, get_string('kbitemdeleted', 'local_knowledge_base'));
}

// retrieve a list of services
$callbacks = local_knowledge_base_get_list_records();

// the page title
$titlepage = get_string('pluginname', 'local_knowledge_base');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// table declaration
$table = new flexible_table('course_knowledge_base');

// customize the table
$table->define_columns(array('title', 'dateentered', 'datemodified', 'actions'));
$table->define_headers(array(get_string('title', 'local_knowledge_base'), get_string('dateentered', 'local_knowledge_base'), get_string('datemodified', 'local_knowledge_base'), new lang_string('actions', 'moodle')));
$table->define_baseurl($baseurl);
$table->setup();

foreach($callbacks as $callback) {
    // filling of information columns
    $viewkbitemlink = $viewkbitem. "?id=" . $callback->id;
    $titlecallback = html_writer::link($viewkbitemlink, $callback->title);
    $dateenteredcallback = html_writer::div(date('Y-m-d h:i:s', $callback->posteddate), 'dateentered');
    $datemodifiedcallback = html_writer::div(date('Y-m-d h:i:s', $callback->updateddate), 'datemodified');
    
    // link for editing
    $editlink = new moodle_url($editkbitem, array('kbitemid' => $callback->id));
    $edititem = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', new lang_string('edit', 'moodle')));
    
    // link to remove
    $deletelink = new moodle_url($deletekbitem, array('id' => $callback->id));
    $deleteitem = $OUTPUT->action_icon($deletelink, new pix_icon('t/delete', new lang_string('delete', 'moodle')));
    
    // adding data to the table
    $table->add_data(array($titlecallback, $dateenteredcallback, $datemodifiedcallback, $edititem . $deleteitem));
}

// display the table
$table->print_html();

// add kb item button
$addkbitemurl = new moodle_url($editkbitem);
echo $OUTPUT->single_button($addkbitemurl, get_string('addakbitem', 'local_knowledge_base'), 'get');
echo $OUTPUT->footer();