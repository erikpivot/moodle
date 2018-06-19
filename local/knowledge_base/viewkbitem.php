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
 * Page for viewing the knowledge base entry.
 *
 * @package   local_knowledge_base
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';
require_once $CFG->libdir . '/adminlib.php';

// Optional parameters
$kbitemid = optional_param('id', 0, PARAM_INT);

// Configure the context of the page
admin_externalpage_setup('local_knowledge_base', '', null, $baseurl, array());
$context = context_system::instance();

// Getting the data
$kbrecord = new stdClass();
$kbrecord = local_knowledge_base_get_record($kbitemid);

// the page title
$titlepage = get_string('kbitemmanage', 'local_knowledge_base');
$PAGE->navbar->add($titlepage);
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// show the information
?>
<h2 class="kb-title"><?=$kbrecord->title;?></h2>
<div class="kb-content">
<?=$kbrecord->content['text'];?>
</div>
<?php
// add back button
$backbtnloc = '/local/knowledge_base/index.php';
$backurl = new moodle_url($backbtnloc);
echo $OUTPUT->single_button($backurl, get_string('backtoindex', 'local_knowledge_base'));
echo $OUTPUT->footer();