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
 * Admin-only code to delete a knowledge base item
 *
 * @package   local_knowledge_base
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';

$managerkbitem = '/local/knowledge_base/index.php';
$baseurl = new moodle_url($managerkbitem);

$id = required_param('id', PARAM_INT); // KB Entry ID.
$delete = optional_param('delete', '', PARAM_ALPHANUM); // Confirmation hash.

$kbitem = $DB->get_record('local_knowledge_base', array('id' => $id), '*', MUST_EXIST);

$PAGE->set_url('/local/knowledge_base/delete.php', array('id' => $id));
$PAGE->set_pagelayout('admin');

// Check if we've got confirmation.
if ($delete === md5($kbitem->title)) {
    // We do - time to delete the knowledge base tiem.

    $strdeletingkbitem = get_string("deletingkbitem", "local_knowledge_base", $kbitem->title);

    $PAGE->navbar->add($strdeletingkbitem);
    $PAGE->set_title("$SITE->shortname: $strdeletingkbitem");
    $PAGE->set_heading($SITE->name);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strdeletingkbitem);
    // We do this here because it spits out feedback as it goes.
    local_knowledge_base_remove_record($id);
    echo $OUTPUT->heading( get_string("deletedkbitem", "local_knowledge_base", $kbitem->title) );
    echo $OUTPUT->continue_button($baseurl);
    echo $OUTPUT->footer();
    exit; // We must exit here!!!
}

$strdeletecheck = get_string("deletecheck", "", $kbitem->title);
$strdeletekbitemcheck = get_string("deletekbitemcheck", "local_knowledge_base");
$message = "{$strdeletekbitemcheck}<br /><br />{$kbitem->title}";

$continueurl = new moodle_url('/local/knowledge_base/delete.php', array('id' => $kbitem->id, 'delete' => md5($kbitem->title)));
$continuebutton = new single_button($continueurl, get_string('delete'), 'post');

$PAGE->navbar->add($strdeletecheck);
$PAGE->set_title("$SITE->shortname: $strdeletecheck");
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->confirm($message, $continuebutton, $baseurl);
echo $OUTPUT->footer();
exit;
