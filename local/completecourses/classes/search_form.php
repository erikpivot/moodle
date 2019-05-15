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
 * Defines the search form on the home page
 *
 * @package   local\completecourses
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined("MOODLE_INTERNAL") || die();

require_once $CFG->libdir . '/formslib.php';

/**
 * Search form definition
 */
class search_form extends moodleform {
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
     * The search form contains fields for the first name and last name.
     */
    protected function definition() {
        global $DB;
        $mform =& $this->_form;
        $size = array("size" => 60);
        
        // form heading
        $mform->addElement('header', 'searchformheader', get_string('searchheader', 'local_completecourses'));
        
        // first name field
        $mform->addElement('text', 'userfirstname', get_string('firstnamefield', 'local_completecourses'), $size);
        $mform->setType('userfirstname', PARAM_NOTAGS);
        
        // last name field
        $mform->addElement('text', 'userlastname', get_string('lastnamefield', 'local_completecourses'), $size);
        $mform->setType('userlastname', PARAM_NOTAGS);
        
        // control panel
        $this->add_action_buttons(false, get_string('searchbutton', 'local_completecourses'));
    }
}