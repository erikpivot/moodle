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
 * Page for editing the bundle.
 *
 * @package   local_course_bundles
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/classes/course_bundle_form.php';
require_once __DIR__ . '/lib.php';
require_once $CFG->libdir . '/adminlib.php';

// Optional parameters
$bundleid = optional_param('bundleid', 0, PARAM_INT);

// Link generation
$urlparams = array('bundleid' => $bundleid);
$baseurl = new moodle_url('/local/course_bundles/editbundle.php', $urlparams);
$managerbundle = new moodle_url('/local/course_bundles/index.php');

// Configure the context of the page
admin_externalpage_setup('local_course_bundles', '', null, $baseurl, array());
$context = context_system::instance();

// create an editing form
$mform = new bundle_edit_form($PAGE->url);

// Cancel processing
if ($mform->is_cancelled()) {
    redirect($managerbundle);
}

// Getting the data
$bundlerecord = new stdClass();
if ($editing = boolval($bundleid)) {
    $bundlerecord = local_course_bundles_get_record($bundleid);
    // break up the courses so the proper checkboxes will be selected
    $all_courses = explode(",", $bundlerecord->courses);
    foreach($all_courses as $course_id) {
        $checkbox_id = "courses" . $course_id;
        $bundlerecord->$checkbox_id = $course_id;
    }
    //file_put_contents(__DIR__ . '/bundle_record.txt', print_r($bundlerecord, true));
    $mform->set_data($bundlerecord);
}

// processing of received data
if ($data = $mform->get_data()) {
    if ($editing) {
        $data->id = $bundleid;
        local_course_bundles_update_record($data, false);
        redirect($managerbundle, get_string('eventbundleupdated', 'local_course_bundles'));
    } else {
        local_course_bundles_update_record($data, true);
        redirect($managerbundle, get_string('eventbundlecreated', 'local_course_bundles'));
    }
}

// the page title
$titlepage = get_string('coursebundlemanage', 'local_course_bundles');
$PAGE->navbar->add($titlepage);
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
$PAGE->requires->js('/local/course_bundles/javascript/actions.js');
echo $OUTPUT->header();

// displays the form
$mform->display();

echo $OUTPUT->footer();