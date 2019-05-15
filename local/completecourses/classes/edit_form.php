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
 * Defines the form to edit the completion status and date for a user's course
 * 
 * @package   local\completecourses
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined("MOODLE_INTERNAL") || die();

require_once $CFG->libdir . '/formslib.php';

/**
 * Editing form definition
 */
class edit_form extends moodleform {
    /**
     * Constructor
     * 
     * @param string $baseurl the current url for the page calling the form
     */
    public function __construct($baseurl) {
        parent::__construct($baseurl);
    }
    
    /**
     * Defines the standard structure of the form
     * 
     * The form has a checkbox to flag the completion state of the course and 
     * a date selector to change the date of completion.
     */
    protected function definition() {
        global $DB;
        $mform =& $this->_form;
        $size = array("size" => 60);
        
        // form heading
        $mform->addElement('header', 'editformheader', get_string('editheader', 'local_completecourses'));
        
        // first name field
        $mform->addElement('checkbox', 'completionstate', get_string('completionstatefield', 'local_completecourses'));
        $mform->setType('completionstate', PARAM_NOTAGS);
        
        // completion date
        $mform->addElement('date_selector', 'completiondate', get_string('completiondatefield', 'local_completecourses'));
        $mform->setType('completiondate', PARAM_NOTAGS);
        
        // control panel
        $this->add_action_buttons(true);
    }
}