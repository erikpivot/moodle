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
 * This file contains the customcert element coursename's core interaction API.
 *
 * @package    customcertelement_coursetimespent
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customcertelement_coursetimespent;

defined('MOODLE_INTERNAL') || die();

/**
 * The customcert element coursename's core interaction API.
 *
 * @package    customcertelement_coursetimespent
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
        global $DB;
        $courseid = \mod_customcert\element_helper::get_courseid($this->get_id());
        // grab the scorm information for completion time
        $sql = "SELECT a.value, c.credithrs FROM {scorm_scoes_track} a
                JOIN {scorm} b
                ON a.scormid = b.id
                JOIN {course} c
                ON b.course = c.id
                WHERE a.element = 'cmi.core.total_time'
                AND userid = ? AND b.course = ?";
        $time_res = $DB->get_record_sql($sql, array($user->id, $courseid));
        $formatted_time = explode(':', $time_res->value);
        $hours = ltrim($formatted_time[0], 0);
        $minutes = ltrim($formatted_time[1], 0);
        // make sure the time spent in the course does not exceed the credit hours
        if ($hours > $time_res->credithrs) {
            $hours = $time_res->credithrs;
            $minutes = 0;
        }
        
        if (empty($hours)) {
            $hours = 0;
        }
        
        $time_str = $hours . ' hours and ' . $minutes . ' minutes';

        \mod_customcert\element_helper::render_content($pdf, $this, $time_str);
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

        return \mod_customcert\element_helper::render_html_content($this, '0 hours 0 minutes');
    }
}
