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
 * @package   report_courseenrollments
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined("MOODLE_INTERNAL") || die();

require_once $CFG->libdir . '/formslib.php';

/**
 * Editing form definition
 */
class course_enrollment_filters_form extends moodleform {
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
        $mform =& $this->_form;
        $date_settings = array(
            'startyear' => '2000',
            'endyear' => '2050'
        );
        
        // enrollment start date
        $mform->addElement('date_selector', 'startdate', get_string('filterstart', 'report_courseenrollments'), $date_settings);
        
        // enrollment end date
        $mform->addElement('date_selector', 'enddate', get_string('filterend', 'report_courseenrollments'), $date_settings);
        
        // life university sponsored only
        $mform->addElement('checkbox', 'lifeonly', get_string('lifeonly', 'report_courseenrollments'));
        
        
        // control panel
        $this->add_action_buttons(false, get_string('subfilter', 'report_courseenrollments'));
    }
}