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
 * State Approval Settings
 *
 * @package   local_state_settings
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/classes/settings_form.php';
require_once $CFG->libdir . '/adminlib.php';

// link generation
$managersettings = '/local/state_settings/index.php';
$baseurl = new moodle_url($managersettings);

// configure the context of the page
admin_externalpage_setup('local_state_settings', '', null, $baseurl, array());
$context = context_system::instance();

// Getting the data
$settingsrecord = new stdClass();
$settingsrecord = local_state_settings_get_records();

// create an editing form
$mform = new settings_edit_form($PAGE->url);

$mform->set_data($settingsrecord);

// states list
$states_array = array(
    'al'=>'Alabama',
    'ak'=>'Alaska',
    'az'=>'Arizona',
    'ar'=>'Arkansas',
    'ca'=>'California',
    'co'=>'Colorado',
    'ct'=>'Connecticut',
    'de'=>'Delaware',
    'dc'=>'District of Columbia',
    'fl'=>'Florida',
    'ga'=>'Georgia',
    'hi'=>'Hawaii',
    'id'=>'Idaho',
    'il'=>'Illinois',
    'in'=>'Indiana',
    'ia'=>'Iowa',
    'ks'=>'Kansas',
    'ky'=>'Kentucky',
    'la'=>'Louisiana',
    'me'=>'Maine',
    'md'=>'Maryland',
    'ma'=>'Massachusetts',
    'mi'=>'Michigan',
    'mn'=>'Minnesota',
    'ms'=>'Mississippi',
    'mo'=>'Missouri',
    'mt'=>'Montana',
    'ne'=>'Nebraska',
    'nv'=>'Nevada',
    'nh'=>'New Hampshire',
    'nj'=>'New Jersey',
    'nm'=>'New Mexico',
    'ny'=>'New York',
    'nc'=>'North Carolina',
    'nd'=>'North Dakota',
    'oh'=>'Ohio',
    'ok'=>'Oklahoma',
    'or'=>'Oregon',
    'pa'=>'Pennsylvania',
    'ri'=>'Rhode Island',
    'sc'=>'South Carolina',
    'sd'=>'South Dakota',
    'tn'=>'Tennessee',
    'tx'=>'Texas',
    'ut'=>'Utah',
    'vt'=>'Vermont',
    'va'=>'Virginia',
    'wa'=>'Washington',
    'wv'=>'West Virginia',
    'wi'=>'Wisconsin',
    'wy'=>'Wyoming'
);

// processing of received data
if ($data = $mform->get_data()) {
    local_state_settings_update_record($data, $states_array);
    redirect($managersettings, get_string('eventstatesettingupdated', 'local_state_settings'));
}

// the page title
$titlepage = get_string('pluginname', 'local_state_settings');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// display the form
$mform->display();

echo $OUTPUT->footer();