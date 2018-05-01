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
 * @package    customcertelement_approvalnumbers
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customcertelement_approvalnumbers;

require_once __DIR__ . '/../../../../../local/state_settings/classes/approvals.php';

defined('MOODLE_INTERNAL') || die();

/**
 * The customcert element approvalnumbers's core interaction API.
 *
 * @package    customcertelement_approvalnumbers
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
        global $USER, $DB, $approvals;
        $courseid = \mod_customcert\element_helper::get_courseid($this->get_id());
        $course = get_course($courseid);
        
        // state references
        $state_abbr = array(
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
        
        $user_states = array();
        
        // get the states the user is associated with
        $user_info_data = $DB->get_records('user_info_data', array('userid' => $USER->id));
        //file_put_contents(__DIR__ . '/debug.txt', print_r($user_info_data, true));
        
        // go through the records and find the primary, secondary and tertiary license numbers
        foreach($user_info_data as $license_info) {
            switch ($license_info->fieldid) {
                case 1:
                case 3:
                case 5:
                    $key = array_search(trim($license_info->data), $state_abbr);
                    $user_states[$license_info->fieldid + 1] = array(
                                                                    'numberkey' => $key . 'approvalno',
                                                                    'state' => $key,
                                                                    'state_name' => trim($license_info->data)
                                                                );
                    break;
                case 2:
                case 4:
                case 6:
                    // does the state need to be removed?
                    if (empty($license_info->data)) {
                        unset($user_states[$license_info->fieldid]);
                    }
                    break;
            }
        }
        
        $state_rows = '';
        // go through each state found and see how to build the table rows for the approval numbers
        foreach($user_states as $state_info) {
            //file_put_contents(__DIR__ . '/debug.txt', print_r($state_info, true), FILE_APPEND);
            // start building the row for the state
            $state_rows .= '<tr nobr="true">';
            $state_rows .= '<td width="25%">' . $state_info['state_name'] . '</td><td width="75%">';
            // grab settings for the state
            $state_settings = $DB->get_record('local_state_settings', array('state' => $state_info['state']));
            //file_put_contents(__DIR__ . '/debug.txt', print_r($state_settings, true), FILE_APPEND);
            foreach($approvals as $key => $value) {
                if ($state_settings->customapprove == $key) {
                    $state_rows .= $value['cert_text'] . " " . $course->paceapprovalno;
                }
            }
            /*
            if (1 == $state_settings->pace) {
                $state_rows .= 'PACE Approval No. ' . $course->paceapprovalno . ' ';
            }
            if (1 == $state_settings->life) {
                $state_rows .= 'Sponsored by Life University ';
            }
            */
            if (1 == $state_settings->stateapproval && !empty($course->$state_info['numberkey'])) {
                $state_rows .= 'State Board Approval No. ' . $course->$state_info['numberkey'];
            }
            $state_rows .= '</td></tr>';
        }
        
        // build the content to show for each state
        $view_str = '<table border="1" cellpadding="5">';
        $view_str .= '<thead>';
        $view_str .= '<tr><th width="25%">STATE</th><th width="75%">SPONSOR INFORMATION AND APPROVAL NUMBERS</th></tr>';
        $view_str .= '</thead>';
        $view_str .= '<tbody>';
        $view_str .= $state_rows;
        $view_str .= '</tbody>';
        $view_str .= '</table>';

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
        $style = 'style="border: 1px solid; padding-right: 20px; padding-left: 10px;"';
        $view_str = '<table style="width: 700px;">';
        $view_str .= '<thead>';
        $view_str .= '<tr><th ' . $style . '>STATE</th><th ' .$style . '>SPONSOR INFORMATION AND APPROVAL NUMBERS</th></tr>';
        $view_str .= '</thead>';
        $view_str .= '<tbody>';
        $view_str .= "<tr><td " . $style . ">Primary</td><td " . $style . ">Numbers Here</td></tr>";
        $view_str .= "<tr><td " . $style . ">Secondary</td><td " . $style . ">Numbers Here</td></tr>";
        $view_str .= "<tr><td " . $style . ">Tertiary</td><td " . $style . ">Numbers Here</td></tr>";
        $view_str .= '</tbody>';
        $view_str .= '</table>';

        return \mod_customcert\element_helper::render_html_content($this, $view_str);
    }
}
