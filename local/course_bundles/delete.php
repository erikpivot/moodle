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
 * Admin-only code to delete a bundle
 *
 * @package   local_course_bundles
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';

$managerbundle = '/local/course_bundles/index.php';
$baseurl = new moodle_url($managerbundle);

$id = required_param('id', PARAM_INT); // Bundle ID.
$delete = optional_param('delete', '', PARAM_ALPHANUM); // Confirmation hash.

$bundle = $DB->get_record('local_course_bundles', array('id' => $id), '*', MUST_EXIST);

$PAGE->set_url('/local/course_bundles/delete.php', array('id' => $id));
$PAGE->set_pagelayout('admin');

// Check if we've got confirmation.
if ($delete === md5($bundle->courses)) {
    // We do - time to delete the bundle.

    $strdeletingbundle = get_string("deletingbundle", "local_course_bundles", $bundle->name);

    $PAGE->navbar->add($strdeletingbundle);
    $PAGE->set_title("$SITE->shortname: $strdeletingbundle");
    $PAGE->set_heading($SITE->name);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strdeletingbundle);
    // We do this here because it spits out feedback as it goes.
    local_course_bundles_remove_record($id);
    echo $OUTPUT->heading( get_string("deletedbundle", "local_course_bundles", $bundle->name) );
    echo $OUTPUT->continue_button($baseurl);
    echo $OUTPUT->footer();
    exit; // We must exit here!!!
}

$strdeletecheck = get_string("deletecheck", "", $bundle->name);
$strdeletebundlecheck = get_string("deletebundlecheck", "local_course_bundles");
$message = "{$strdeletebundlecheck}<br /><br />{$bundle->name}";

$continueurl = new moodle_url('/local/course_bundles/delete.php', array('id' => $bundle->id, 'delete' => md5($bundle->courses)));
$continuebutton = new single_button($continueurl, get_string('delete'), 'post');

$PAGE->navbar->add($strdeletecheck);
$PAGE->set_title("$SITE->shortname: $strdeletecheck");
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->confirm($message, $continuebutton, $baseurl);
echo $OUTPUT->footer();
exit;
