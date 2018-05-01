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
 * Course Bundle Manager
 *
 * @package   local_course_bundles
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
$editbundle = '/local/course_bundles/editbundle.php';
$managerbundle = '/local/course_bundles/index.php';
$deletebundle = '/local/course_bundles/delete.php';
$baseurl = new moodle_url($managerbundle);

// configure the context of the page
admin_externalpage_setup('local_course_bundles', '', null, $baseurl, array());
$context = context_system::instance();

// delete the bundle
if (boolval($deleteid)) {
    local_course_bundles_remove_record($deleteid);
    redirect($PAGE->url, get_string('eventbundledeleted', 'local_course_bundles'));
}

// retrieve a list of services
$callbacks = local_course_bundles_get_list_records();

// the page title
$titlepage = get_string('pluginname', 'local_course_bundles');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// table declaration
$table = new flexible_table('course_bundles_table');

// customize the table
$table->define_columns(array('name', 'price', 'actions'));
$table->define_headers(array(get_string('name', 'local_course_bundles'), get_string('price', 'local_course_bundles'), new lang_string('actions', 'moodle')));
$table->define_baseurl($baseurl);
$table->setup();

foreach($callbacks as $callback) {
    // filling of information columns
    $namecallback = html_writer::div($callback->name, 'name');
    $pricecallback = html_writer::div($callback->price, 'price');
    
    // link for editing
    $editlink = new moodle_url($editbundle, array('bundleid' => $callback->id));
    $edititem = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', new lang_string('edit', 'moodle')));
    
    // link to remove
    $deletelink = new moodle_url($deletebundle, array('id' => $callback->id));
    $deleteitem = $OUTPUT->action_icon($deletelink, new pix_icon('t/delete', new lang_string('delete', 'moodle')));
    
    // adding data to the table
    $table->add_data(array($namecallback, $pricecallback, $edititem . $deleteitem));
}

// display the table
$table->print_html();

// add bundle button
$addbundleurl = new moodle_url($editbundle);
echo $OUTPUT->single_button($addbundleurl, get_string('addabundle', 'local_course_bundles'), 'get');

echo $OUTPUT->footer();