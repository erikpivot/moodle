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
 * @package   local_knowledge_base
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/classes/kb_item_form.php';
require_once __DIR__ . '/lib.php';
require_once $CFG->libdir . '/adminlib.php';

// Optional parameters
$kbitemid = optional_param('kbitemid', 0, PARAM_INT);

// Link generation
$urlparams = array('kbitemid' => $kbitemid);
$baseurl = new moodle_url('/local/knowledge_base/editkbitem.php', $urlparams);
$managerkbitem = new moodle_url('/local/knowledge_base/index.php');

// Configure the context of the page
admin_externalpage_setup('local_knowledge_base', '', null, $baseurl, array());
$context = context_system::instance();

// create an editing form
$mform = new kb_edit_form($PAGE->url);

// Cancel processing
if ($mform->is_cancelled()) {
    redirect($managerkbitem);
}

// Getting the data
$kbrecord = new stdClass();
if ($editing = boolval($kbitemid)) {
    $kbrecord = local_knowledge_base_get_record($kbitemid);
    $mform->set_data($kbrecord);
}

// processing of received data
if ($data = $mform->get_data()) {
    if ($editing) {
        $data->id = $kbitemid;
        local_knowledge_base_update_record($data, false);
        redirect($managerkbitem, get_string('eventkbitemupdated', 'local_knowledge_base'));
    } else {
        local_knowledge_base_update_record($data, true);
        redirect($managerkbitem, get_string('eventkbitemcreated', 'local_knowledge_base'));
    }
}

// the page title
$titlepage = get_string('kbitemmanage', 'local_knowledge_base');
$PAGE->navbar->add($titlepage);
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// displays the form
$mform->display();

echo $OUTPUT->footer();