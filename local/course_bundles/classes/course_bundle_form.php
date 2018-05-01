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
 * @package   local_course_bundles
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined("MOODLE_INTERNAL") || die();

require_once $CFG->libdir . '/formslib.php';
require_once __DIR__ . '/../../state_settings/classes/approvals.php';

/**
 * Editing form definition
 */
class bundle_edit_form extends moodleform {
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
        global $DB, $approvals;
        $mform =& $this->_form;
        $size = array("size" => 60);
        
        // form heading
        $mform->addElement('header', 'editbundleheader', get_string('bundle', 'local_course_bundles'));
        
        // name of the bundle
        $mform->addElement('text', 'name', get_string('name', 'local_course_bundles'), $size);
        $mform->addRule('name', null, 'required');
        $mform->setType('name', PARAM_NOTAGS);
        
        // state selector
        $all_states = array(
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
        $mform->addElement('select', 'state', get_string('statelist', 'local_course_bundles'), $all_states);
        
        // get all courses to set up as checkboxes
        $courses = $DB->get_records('course', array('revisionno' => 0));
        $course_list = array();
        foreach($courses as $course_info) {
            // build the class name based on the states that are associated with the course
            $class_name = '';
            $sel_states = array();
            if (!empty($course_info->alapprovalno)) {
                $sel_states[] = 'al';
            }
            if (!empty($course_info->akapprovalno)) {
                $sel_states[] = 'ak';
            }
            if (!empty($course_info->azapprovalno)) {
                $sel_states[] = 'az';
            }
            if (!empty($course_info->arapprovalno)) {
                $sel_states[] = 'ar';
            }
            if (!empty($course_info->caapprovalno)) {
                $sel_states[] = 'ca';
            }
            if (!empty($course_info->coapprovalno)) {
                $sel_states[] = 'co';
            }
            if (!empty($course_info->ctapprovalno)) {
                $sel_states[] = 'ct';
            }
            if (!empty($course_info->deapprovalno)) {
                $sel_states[] = 'de';
            }
            if (!empty($course_info->dcapprovalno)) {
                $sel_states[] = 'dc';
            }
            if (!empty($course_info->flapprovalno)) {
                $sel_states[] = 'fl';
            }
            if (!empty($course_info->gaapprovalno)) {
                $sel_states[] = 'ga';
            }
            if (!empty($course_info->hiapprovalno)) {
                $sel_states[] = 'hi';
            }
            if (!empty($course_info->idapprovalno)) {
                $sel_states[] = 'id';
            }
            if (!empty($course_info->ilapprovalno)) {
                $sel_states[] = 'il';
            }
            if (!empty($course_info->inapprovalno)) {
                $sel_states[] = 'in';
            }
            if (!empty($course_info->iaapprovalno)) {
                $sel_states[] = 'ia';
            }
            if (!empty($course_info->ksapprovalno)) {
                $sel_states[] = 'ks';
            }
            if (!empty($course_info->kyapprovalno)) {
                $sel_states[] = 'ky';
            }
            if (!empty($course_info->laapprovalno)) {
                $sel_states[] = 'la';
            }
            if (!empty($course_info->meapprovalno)) {
                $sel_states[] = 'me';
            }
            if (!empty($course_info->mdapprovalno)) {
                $sel_states[] = 'md';
            }
            if (!empty($course_info->maapprovalno)) {
                $sel_states[] = 'ma';
            }
            if (!empty($course_info->miapprovalno)) {
                $sel_states[] = 'mi';
            }
            if (!empty($course_info->mnapprovalno)) {
                $sel_states[] = 'mn';
            }
            if (!empty($course_info->msapprovalno)) {
                $sel_states[] = 'ms';
            }
            if (!empty($course_info->moapprovalno)) {
                $sel_states[] = 'mo';
            }
            if (!empty($course_info->mtapprovalno)) {
                $sel_states[] = 'mt';
            }
            if (!empty($course_info->neapprovalno)) {
                $sel_states[] = 'ne';
            }
            if (!empty($course_info->nvapprovalno)) {
                $sel_states[] = 'nv';
            }
            if (!empty($course_info->nhapprovalno)) {
                $sel_states[] = 'nh';
            }
            if (!empty($course_info->njapprovalno)) {
                $sel_states[] = 'nj';
            }
            if (!empty($course_info->nmapprovalno)) {
                $sel_states[] = 'nm';
            }
            if (!empty($course_info->nyapprovalno)) {
                $sel_states[] = 'ny';
            }
            if (!empty($course_info->ncapprovalno)) {
                $sel_states[] = 'nc';
            }
            if (!empty($course_info->ndapprovalno)) {
                $sel_states[] = 'nd';
            }
            if (!empty($course_info->ohapprovalno)) {
                $sel_states[] = 'oh';
            }
            if (!empty($course_info->okapprovalno)) {
                $sel_states[] = 'ok';
            }
            if (!empty($course_info->orapprovalno)) {
                $sel_states[] = 'or';
            }
            if (!empty($course_info->paapprovalno)) {
                $sel_states[] = 'pa';
            }
            if (!empty($course_info->riapprovalno)) {
                $sel_states[] = 'ri';
            }
            if (!empty($course_info->scapprovalno)) {
                $sel_states[] = 'sc';
            }
            if (!empty($course_info->sdapprovalno)) {
                $sel_states[] = 'sd';
            }
            if (!empty($course_info->tnapprovalno)) {
                $sel_states[] = 'tn';
            }
            if (!empty($course_info->txapprovalno)) {
                $sel_states[] = 'tx';
            }
            if (!empty($course_info->utapprovalno)) {
                $sel_states[] = 'ut';
            }
            if (!empty($course_info->vtapprovalno)) {
                $sel_states[] = 'vt';
            }
            if (!empty($course_info->vaapprovalno)) {
                $sel_states[] = 'va';
            }
            if (!empty($course_info->waapprovalno)) {
                $sel_states[] = 'wa';
            }
            if (!empty($course_info->wvapprovalno)) {
                $sel_states[] = 'wv';
            }
            if (!empty($course_info->wiapprovalno)) {
                $sel_states[] = 'wi';
            }
            if (!empty($course_info->wyapprovalno)) {
                $sel_states[] = 'wy';
            }
            if (!empty($course_info->paceapprovalno)) {
                // need to find all states that are marked for the custom approval numbers
                foreach($approvals as $key => $value) {
                    $get_states = $DB->get_records('local_state_settings', array('customapprove' => $key));
                    foreach($get_states as $state_set) {
                        $sel_states[] = $state_set->state;
                    }
                }
            }
            //echo print_r($course_info, true);
            //echo '<pre>';
            //echo print_r($sel_states, true);
            //echo '</pre>';
            $class_name = implode(' ', $sel_states);
            //echo $class_name . "<br />";
            //$course_list[$course_info->id][] =& $mform->createElement('advcheckbox', $course_info->id, $course_info->fullname, '', array('states' => $class_name), array($course_info->id));
            $mform->addElement('advcheckbox', 'courses' . $course_info->idnumber, $course_info->fullname, '', array('group' => 1, 'states' => $class_name, 'hours' => $course_info->credithrs), array(0, $course_info->idnumber));
        }
        
        // Displays groups of items
        /*
        foreach($course_list as $key => $value) {
            $mform->addGroup($value, "courses", $key, "<br />", true);
        }
        */
       
        // hidden element for selected courses
        $mform->addElement('hidden', 'courses', '');
        
        // hidden element for ecommerce id
        $mform->addElement('hidden', 'ecommproductid', '');
        
        // bundle description
        $mform->addElement('textarea', 'description', get_string('bundledescription', 'local_course_bundles'), 'wrap="virtual" rows="20" cols="75"');
        $mform->addRule('description', null, 'required');
        $mform->setType('description', PARAM_RAW);
        
        // bundle short description
        $mform->addElement('textarea', 'shortdescript', get_string('bundleshortdescription', 'local_course_bundles'), 'wrap="virtual" rows="20" cols="75"');
        $mform->addRule('shortdescript', null, 'required');
        $mform->setType('shortdescript', PARAM_RAW);
        
        // total credit hours
        $mform->addElement('text', 'credithrs', get_string('bundlecredithours', 'local_course_bundles'));
        $mform->addRule('credithrs', null, 'required');
        $mform->setType('credithrs', PARAM_INT);
        
        // bundle price
        $mform->addElement('text', 'price', get_string('price', 'local_course_bundles'));
        $mform->addRule('price', null, 'required');
        $mform->setType('price', PARAM_FLOAT);
        
        // control panel
        $this->add_action_buttons(true);
    }
}