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
 * The event handler.
 *
 * @package   local_course_bundles
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_bundles;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../lib.php';
require_once __DIR__ . '/../locallib.php';

require_once $CFG->libdir . '/filelib.php';

/**
 * Defines how to work with events
 * 
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class handler {
    /**
     * External handler
     * 
     * @param object $event
     */
    public static function events($event) {
        $data = $event->get_data();
        
        if ($callbacks = local_course_bundles_get_list_records()) {
            foreach ($callbacks as $callback) {
                self::handler_callback($data, $callback);
            }
        }
    }
    
    /**
     * Process each callback
     * 
     * @param array $data
     * @param object $callback
     */
    private static function handler_callback($data, $callback) {
        global $CFG;
        
        \local_course_bundles_events::response_answer($callback->id, $data);
    }
}