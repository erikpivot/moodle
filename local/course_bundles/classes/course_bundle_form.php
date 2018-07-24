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
        $courses = $DB->get_records('course', array('category' => 1));
        $course_list = array();
        foreach($courses as $course_info) {
            // build the class name based on the states that are associated with the course
            $class_name = '';
            $sel_states = array();
            if (!empty($course_info->alapprovalno) && 0 == $course_info->alexclude) {
                $sel_states[] = 'al';
            }
            if (!empty($course_info->akapprovalno) && 0 == $course_info->akexclude) {
                $sel_states[] = 'ak';
            }
            if (!empty($course_info->azapprovalno) && 0 == $course_info->azexclude) {
                $sel_states[] = 'az';
            }
            if (!empty($course_info->arapprovalno) && 0 == $course_info->arexclude) {
                $sel_states[] = 'ar';
            }
            if (!empty($course_info->caapprovalno) && 0 == $course_info->caexclude) {
                $sel_states[] = 'ca';
            }
            if (!empty($course_info->coapprovalno) && 0 == $course_info->coexclude) {
                $sel_states[] = 'co';
            }
            if (!empty($course_info->ctapprovalno) && 0 == $course_info->ctexclude) {
                $sel_states[] = 'ct';
            }
            if (!empty($course_info->deapprovalno) && 0 == $course_info->deexclude) {
                $sel_states[] = 'de';
            }
            if (!empty($course_info->dcapprovalno) && 0 == $course_info->dcexclude) {
                $sel_states[] = 'dc';
            }
            if (!empty($course_info->flapprovalno) && 0 == $course_info->flexclude) {
                $sel_states[] = 'fl';
            }
            if (!empty($course_info->gaapprovalno) && 0 == $course_info->gaexclude) {
                $sel_states[] = 'ga';
            }
            if (!empty($course_info->hiapprovalno) && 0 == $course_info->hiexclude) {
                $sel_states[] = 'hi';
            }
            if (!empty($course_info->idapprovalno) && 0 == $course_info->idexclude) {
                $sel_states[] = 'id';
            }
            if (!empty($course_info->ilapprovalno) && 0 == $course_info->ilexclude) {
                $sel_states[] = 'il';
            }
            if (!empty($course_info->inapprovalno) && 0 == $course_info->inexclude) {
                $sel_states[] = 'in';
            }
            if (!empty($course_info->iaapprovalno) && 0 == $course_info->iaexclude) {
                $sel_states[] = 'ia';
            }
            if (!empty($course_info->ksapprovalno) && 0 == $course_info->ksexclude) {
                $sel_states[] = 'ks';
            }
            if (!empty($course_info->kyapprovalno) && 0 == $course_info->kyexclude) {
                $sel_states[] = 'ky';
            }
            if (!empty($course_info->laapprovalno) && 0 == $course_info->laexclude) {
                $sel_states[] = 'la';
            }
            if (!empty($course_info->meapprovalno) && 0 == $course_info->meexclude) {
                $sel_states[] = 'me';
            }
            if (!empty($course_info->mdapprovalno) && 0 == $course_info->mdexclude) {
                $sel_states[] = 'md';
            }
            if (!empty($course_info->maapprovalno) && 0 == $course_info->maexclude) {
                $sel_states[] = 'ma';
            }
            if (!empty($course_info->miapprovalno) && 0 == $course_info->miexclude) {
                $sel_states[] = 'mi';
            }
            if (!empty($course_info->mnapprovalno) && 0 == $course_info->mnexclude) {
                $sel_states[] = 'mn';
            }
            if (!empty($course_info->msapprovalno) && 0 == $course_info->msexclude) {
                $sel_states[] = 'ms';
            }
            if (!empty($course_info->moapprovalno) && 0 == $course_info->moexclude) {
                $sel_states[] = 'mo';
            }
            if (!empty($course_info->mtapprovalno) && 0 == $course_info->mtexclude) {
                $sel_states[] = 'mt';
            }
            if (!empty($course_info->neapprovalno) && 0 == $course_info->neexclude) {
                $sel_states[] = 'ne';
            }
            if (!empty($course_info->nvapprovalno) && 0 == $course_info->nvexclude) {
                $sel_states[] = 'nv';
            }
            if (!empty($course_info->nhapprovalno) && 0 == $course_info->nhexclude) {
                $sel_states[] = 'nh';
            }
            if (!empty($course_info->njapprovalno) && 0 == $course_info->njexclude) {
                $sel_states[] = 'nj';
            }
            if (!empty($course_info->nmapprovalno) && 0 == $course_info->nmexclude) {
                $sel_states[] = 'nm';
            }
            if (!empty($course_info->nyapprovalno) && 0 == $course_info->nyexclude) {
                $sel_states[] = 'ny';
            }
            if (!empty($course_info->ncapprovalno) && 0 == $course_info->ncexclude) {
                $sel_states[] = 'nc';
            }
            if (!empty($course_info->ndapprovalno) && 0 == $course_info->ndexclude) {
                $sel_states[] = 'nd';
            }
            if (!empty($course_info->ohapprovalno) && 0 == $course_info->ohexclude) {
                $sel_states[] = 'oh';
            }
            if (!empty($course_info->okapprovalno) && 0 == $course_info->okexclude) {
                $sel_states[] = 'ok';
            }
            if (!empty($course_info->orapprovalno) && 0 == $course_info->orexclude) {
                $sel_states[] = 'or';
            }
            if (!empty($course_info->paapprovalno) && 0 == $course_info->paexclude) {
                $sel_states[] = 'pa';
            }
            if (!empty($course_info->riapprovalno) && 0 == $course_info->riexclude) {
                $sel_states[] = 'ri';
            }
            if (!empty($course_info->scapprovalno) && 0 == $course_info->scexclude) {
                $sel_states[] = 'sc';
            }
            if (!empty($course_info->sdapprovalno) && 0 == $course_info->sdexclude) {
                $sel_states[] = 'sd';
            }
            if (!empty($course_info->tnapprovalno) && 0 == $course_info->tnexclude) {
                $sel_states[] = 'tn';
            }
            if (!empty($course_info->txapprovalno) && 0 == $course_info->txexclude) {
                $sel_states[] = 'tx';
            }
            if (!empty($course_info->utapprovalno) && 0 == $course_info->utexclude) {
                $sel_states[] = 'ut';
            }
            if (!empty($course_info->vtapprovalno) && 0 == $course_info->vtexclude) {
                $sel_states[] = 'vt';
            }
            if (!empty($course_info->vaapprovalno) && 0 == $course_info->vaexclude) {
                $sel_states[] = 'va';
            }
            if (!empty($course_info->waapprovalno) && 0 == $course_info->waexclude) {
                $sel_states[] = 'wa';
            }
            if (!empty($course_info->wvapprovalno) && 0 == $course_info->wvexclude) {
                $sel_states[] = 'wv';
            }
            if (!empty($course_info->wiapprovalno) && 0 == $course_info->wiexclude) {
                $sel_states[] = 'wi';
            }
            if (!empty($course_info->wyapprovalno) && 0 == $course_info->wyexclude) {
                $sel_states[] = 'wy';
            }
            /*
            if (1 == $course_info->lifeapprovedstates) {
                // need to find all states that are marked for the custom approval numbers
                $get_states = $DB->get_records('local_state_settings', array('customapprove' => 2));
                foreach($get_states as $state_set) {
                    $sel_states[] = $state_set->state;
                }
            }
            */
            // only do the following checks if not a state specific course
            if (0 == $course_info->includecustomstates) {
                if (!empty($course_info->paceapprovalno)) {
                    // need to find all states that are marked for the custom approval numbers
                    $get_states = $DB->get_records('local_state_settings', array('customapprove' => 1));
                    foreach($get_states as $state_set) {
                        $state_var = $state_set->state . 'exclude';
                        if (0 == $course_info->$state_var) {
                            $sel_states[] = $state_set->state;    
                        }
                    }
                }
                
                // life states
                $get_states = $DB->get_records('local_state_settings', array('customapprove' => 2));
                foreach($get_states as $state_set) {
                    $state_var = $state_set->state . 'exclude';
                    if (0 == $course_info->$state_var) {
                        $sel_states[] = $state_set->state;    
                    }
                }
                // get states that my not have numbers
                $get_states = $DB->get_records('local_state_settings', array('customapprove' => 0));
                foreach($get_states as $state_set) {
                    $state_var = $state_set->state . 'exclude';
                    if (0 == $course_info->$state_var) {
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
        $mform->addElement('editor', 'description', get_string('bundledescription', 'local_course_bundles'), 'wrap="virtual" rows="20" cols="75"');
        $mform->addRule('description', null, 'required');
        $mform->setType('description', PARAM_RAW);
        
        // bundle featured image
        //$mform->addElement('filemanager', 'featuredimage', get_string('bundlefeaturedimage', 'local_course_bundles'), array('.jpg', '.gif', '.png'));
        
        // bundle short description
        /*
        $mform->addElement('textarea', 'shortdescript', get_string('bundleshortdescription', 'local_course_bundles'), 'wrap="virtual" rows="20" cols="75"');
        $mform->addRule('shortdescript', null, 'required');
        $mform->setType('shortdescript', PARAM_RAW);
        */
        
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