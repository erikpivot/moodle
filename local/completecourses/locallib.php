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
 * The file contains the event triggers
 *
 * @package   local\completecourses
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

/**
 * Description of functions of the call of events
 */
class local_completecourses_events {
    /**
     * Call event when the response is received from the service
     *
     * @param int $objectid Service ID
     * @param array  $response Server response
     */
    public static function response_answer($objectid = 0, $response = array()) {
        $context = context_system::instance();

        $status = "Success";

        $event = local_completecourses\event\response_answer::create(
            array(
                "context"  => $context,
                "objectid" => $objectid,
                "other"    => array("status" => $status)
            )
        );

        $event->trigger();
    }
    
    /**
     * Call event when the service is updated
     * 
     * @param int $objectid Bundle ID
     */
    public static function course_completion_updated($objectid = 0) {
        $context = context_system::instance();
        
        $event = local_completecourses\event\bundle_updated::create(
            array(
                'context' => $context,
                'objectid' => $objectid
            )
        );
        
        $event->trigger();
    }
}