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
 * @package   local_test_reset
 * @copyright 2019 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';

$managertritem = '/local/test_reset/index.php';
$baseurl = new moodle_url($managertritem);

$userid = required_param('userid', PARAM_INT); // User ID that took the test
$scormid = required_param('scormid', PARAM_INT); // ID of the scorm file that has the test
$delete = optional_param('delete', '', PARAM_ALPHANUM); // Confirmation hash.

$delete_check = $userid . $scormid;

$PAGE->set_url('/local/knowledge_base/delete.php', array('userid' => $userid, 'scormid' => $scormid));
$PAGE->set_pagelayout('admin');

// Check if we've got confirmation.
if ($delete === md5($delete_check)) {
    // We do - time to delete the knowledge base tiem.

    $strdeletingtritem = get_string("deletingtritem", "local_test_reset");

    $PAGE->navbar->add($strdeletingtritem);
    $PAGE->set_title("$SITE->shortname: $strdeletingtritem");
    $PAGE->set_heading($SITE->name);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strdeletingtritem);
    // We do this here because it spits out feedback as it goes.
    local_test_reset_remove_record($userid, $scormid);
    echo $OUTPUT->heading( get_string("deletedtritem", "local_test_reset") );
    echo $OUTPUT->continue_button($baseurl);
    echo $OUTPUT->footer();
    exit; // We must exit here!!!
}

$strdeletecheck = get_string("deletetritemcheck", "local_test_reset");
$message = "{$strdeletecheck}<br /><br />";

$continueurl = new moodle_url('/local/test_reset/delete.php', array('userid' => $userid, 'scormid' => $scormid, 'delete' => md5($delete_check)));
$continuebutton = new single_button($continueurl, get_string('delete'), 'post');

$PAGE->navbar->add($strdeletecheck);
$PAGE->set_title("$SITE->shortname: Remove Test Items");
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->confirm($message, $continuebutton, $baseurl);
echo $OUTPUT->footer();
exit;
