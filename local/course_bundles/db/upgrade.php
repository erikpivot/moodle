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
 * Upgrade script for the local smart_klass xAPI module.
 *
 * @package   local_course_bundles
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * @global moodle_database $DB
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_course_bundles_upgrade($oldversion) {
    global $CFG, $DB;
    
    $dbman = $DB->get_manager();

    if ($oldversion < 2018032800) {
        // add new fields to the bundles table
        $table = new xmldb_table('local_course_bundles');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, '', null, null, null, null, null);
        $dbman->add_field($table, $field);
        $field = new xmldb_field('shortdescript', XMLDB_TYPE_TEXT, '', null, null, null, null, null);
        $dbman->add_field($table, $field);
        $field = new xmldb_field('credithrs', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);
        $dbman->add_field($table, $field);
    }
    
    if ($oldversion < 2018070600) {
        // modify the courses field
        $table = new xmldb_table('local_course_bundles');
        $field = new xmldb_field('courses', XMLDB_TYPE_TEXT, '', null, null, null, null, null);
        $dbman->change_field_type($table, $field);
    }


    return true;
}