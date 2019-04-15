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
 * Page for editing the knowledge base entry.
 *
 * @package   local_completecourses
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/classes/edit_form.php';
require_once __DIR__ . '/lib.php';
require_once $CFG->libdir . '/adminlib.php';

// Optional parameters
$completeid = optional_param('completionid', 0, PARAM_INT);
$completestate = optional_param('completionstate', 0, PARAM_INT);
$completiondate = optional_param('completiondate', 0, PARAM_INT);
$certissueid = optional_param('certissueid', 0, PARAM_INT);
$firstname = optional_param('userfirstname', '', PARAM_TEXT);
$lastname = optional_param('userlastname', '', PARAM_TEXT);

// convert the date?
if (is_array($completiondate)) {
    $completiondatestr = $completiondate['year'] . '-' . $completiondate['month'] . '-' . $completiondate['day'];
    $completiondate = strtotime($completiondatestr);
}

// Link generation
$urlparams = array('completionid' => $completeid, 'completionstate' => $completestate, 'completiondate' => $completiondate, 'certissueid' => $certissueid, 'userfirstname' => $firstname, 'userlastname' => $lastname);
$baseurl = new moodle_url('/local/completecourses/editcoursecompletion.php', $urlparams);
//echo $baseurl . "\n";
$completeparams = array('userfirstname' => $firstname, 'userlastname' => $lastname);
$managercompleteitem = new moodle_url('/local/completecourses/index.php', $completeparams);

// Configure the context of the page
admin_externalpage_setup('local_completecourses', '', null, $baseurl, array());
$context = context_system::instance();

// create an editing form
$mform = new edit_form($PAGE->url);

// Cancel processing
if ($mform->is_cancelled()) {
    redirect($managercompleteitem);
}

// Getting the data
$completerecord = new stdClass();
if ($editing = boolval($completeid)) {
    //$completerecord = local_completecourses_get_record($completeid);
    $completerecord = new stdClass();
    $completerecord->completionstate = $completestate;
    $completerecord->completiondate = $completiondate;
    $mform->set_data($completerecord);
}

// processing of received data
if ($data = $mform->get_data()) {
    if ($editing) {
        $data->completeid = $completeid;
        $data->certissueid = $certissueid;
        //echo print_r($data, true);
        local_completecourses_update_record($data);
        redirect($managercompleteitem, get_string('eventcompleteitemupdated', 'local_completecourses'));
    }
}

// the page title
$titlepage = get_string('completeitemmanage', 'local_completecourses');
$PAGE->navbar->add($titlepage);
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// displays the form
$mform->display();

echo $OUTPUT->footer();