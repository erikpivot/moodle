<?php
// This file is part of the customcert module for Moodle - http://moodle.org/
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
 * This file contains the customcert element completedate's core interaction API.
 *
 * @package    customcertelement_completedate
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customcertelement_completedate;

defined('MOODLE_INTERNAL') || die();

/**
 * The customcert element completedate's core interaction API.
 *
 * @package    customcertelement_completedate
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element extends \mod_customcert\element {

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public function render($pdf, $preview, $user) {
        global $USER, $DB;
        $courseid = \mod_customcert\element_helper::get_courseid($this->get_id());
        $course = get_course($courseid);
        
        // get the completed module for the user
        $course_mod = $DB->get_record('course_modules', array('course' => $courseid, 'module' => 18), 'id');
        $complete_date = $DB->get_record('course_modules_completion', array('coursemoduleid' => $course_mod->id, 'userid' => $USER-id), 'timemodified');
        
        // build the content to show for each state
        $view_str = date('F j, Y', $complete_date->timemodified);

        \mod_customcert\element_helper::render_content($pdf, $this, $view_str);
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public function render_html() {
        global $COURSE;
        
        // build the content to show for each state
        $view_str = date('F j, Y');

        return \mod_customcert\element_helper::render_html_content($this, $view_str);
    }
}
