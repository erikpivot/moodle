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
 * This file contains the customcert element statelicenseinfo's core interaction API.
 *
 * @package    customcertelement_statelicenseinfo
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customcertelement_statelicenseinfo;

defined('MOODLE_INTERNAL') || die();

/**
 * The customcert element statelicenseinfo's core interaction API.
 *
 * @package    customcertelement_statelicenseinfo
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
        
        // get the license numbers for the user
        $user_info_data = $DB->get_records('user_info_data', array('userid' => $USER->id));
        //file_put_contents(__DIR__ . '/debug.txt', print_r($user_info_data, true));
        
        $primary = '';
        $secondary = '';
        $tertiary = '';
        
        // go through the records and find the primary, secondary and tertiary license numbers
        foreach($user_info_data as $license_info) {
            switch ($license_info->fieldid) {
                case 1:
                    $primary = "Primary: " . $license_info->data . " - ";
                    break;
                case 3:
                    $secondary = "Secondary: " . $license_info->data . " - ";
                    break;
                case 5:
                    $tertiary = "Tertiary: " . $license_info->data . " - ";
                    break;
                case 2:
                    $primary .= $license_info->data;
                    break;
                case 4:
                    if (empty($license_info->data)) {
                        $secondary = '';
                    } else {
                        $secondary .= $license_info->data;
                    }
                    break;
                case 6:
                    if (empty($license_info->data)) {
                        $tertiary = '';
                    } else {
                        $tertiary .= $license_info->data;
                    }
                    break;
            }
        }
        
        // build the content to show for each state
        $view_str = "<b>Licensing</b><br>";
        $view_str .= $primary . "<br>";
        if (!empty($secondary)) {
            $view_str .= $secondary . "<br>";
        }
        if (!empty($tertiary)) {
            $view_str .= $tertiary . "<br>";
        }

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
        $view_str = '<b>Licensing</b><br />';
        $view_str .= 'Primary: State - Number<br />';
        $view_str .= 'Secondary: State - Number<br />';
        $view_str .= 'Tertiary: State - Number<br />';

        return \mod_customcert\element_helper::render_html_content($this, $view_str);
    }
}
