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
 * Classes of modules.
 *
 * @package   local_knowledge_base
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

/**
 * Description of functions of the call of events
 */
class local_knowledge_base_events {
    /**
     * Call event when the response is received from the service
     *
     * @param number $objectid Service ID
     * @param array  $response Server response
     */
    public static function response_answer($objectid = 0, $response = array()) {
        $context = context_system::instance();

        $status = "Success";

        $event = local_knowledge_base\event\response_answer::create(
            array(
                "context"  => $context,
                "objectid" => $objectid,
                "other"    => array("status" => $status)
            )
        );

        $event->trigger();
    }
    
    /**
     * Call the event when the kb item is added
     * 
     * @param number $objectid KB Item ID
     */
    public static function kb_item_added($objectid = 0) {
        $context = context_system::instance();
        
        $event = local_knowledge_base\event\kb_item_added::create(
            array(
                'context' => $context,
                'objectid' => $objectid
            )
        );
        
        $event->trigger();
    }
    
    /**
     * Call the event when the bundle is deleted
     * 
     * @param number $objectid KB Item ID
     */
    public static function kb_item_deleted($objectid = 0) {
        $context = context_system::instance();
        
        $event = local_knowledge_base\event\kb_item_deleted::create(
            array(
                'context' => $context,
                'objectid' => $objectid
            )
        );
        
        $event->trigger();
    }
    
    /**
     * Call event when the service is updated
     * 
     * @param number $objectid KB Item ID
     */
    public static function kb_item_updated($objectid = 0) {
        $context = context_system::instance();
        
        $event = local_knowledge_base\event\kb_item_updated::create(
            array(
                'context' => $context,
                'objectid' => $objectid
            )
        );
        
        $event->trigger();
    }
}