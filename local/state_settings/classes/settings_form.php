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
 * Defines forms.
 *
 * @package   local_state_settings
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined("MOODLE_INTERNAL") || die();

require_once $CFG->libdir . '/formslib.php';
require_once __DIR__ . '/approvals.php';

/**
 * Editing form definition
 */
class settings_edit_form extends moodleform {
    /**
     * @param string $baseurl
     */
    public function __construct($baseurl) {
        parent::__construct($baseurl);
    }
    
    /**
     * Defines the standard structure of the form
     */
    protected function definition() {
        global $DB;
        global $approvals;
        $mform =& $this->_form;
        $size = array("size" => 60);
        
        // states
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
        
        // loop through each state and create a settings entry
        foreach ($states_array as $key => $value) {
            $mform->addElement('header', $key . 'settingsheader', get_string($key . 'header', 'local_state_settings'));
            $radioarray = array();
            $radioarray[] = $mform->createElement('radio', $key . 'setapprove', '', get_string('none', 'local_state_settings'), 0, '');
            // loop the approvals array to make the radio buttons
            foreach ($approvals as $akey => $avalue) {
                $radioarray[] = $mform->createElement('radio', $key . 'setapprove', '', $avalue['label'], $akey, '');
            }
            $mform->addGroup($radioarray, $key . 'radio', get_string('selapproval', 'local_state_settings'), array(''), false);
            $mform->addElement('advcheckbox', $key . 'stateapprove', get_string('stateapprove', 'local_state_settings'), '', array(), array(0, 1));
            $mform->addElement('editor', $key . 'staterequire', get_string('staterequire', 'local_state_settings'), 'wrap="virtual" rows="20" cols="100"');
            $mform->setType($key . 'staterequire', PARAM_RAW);
        }
        
        // control panel
        $this->add_action_buttons(true);
    }
}