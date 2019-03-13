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
 * Upgrade script for local ecominfo
 *
 * @package   local_ecominfo
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * @global moodle_database $DB
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_ecominfo_upgrade($oldversion) {
    global $CFG, $DB;
    
    $dbman = $DB->get_manager();

    if ($oldversion < 2018091900) {
        // add new fields to the bundles table
        $table = new xmldb_table('local_ecominfo');
        $field = new xmldb_field('orderid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);
        $dbman->add_field($table, $field);
        $field = new xmldb_field('orderdate', XMLDB_TYPE_INTEGER, '10', null, null, null, null, null);
        $dbman->add_field($table, $field);
    }
    
    if ($oldversion < 2018092000) {
        // add new fields to the bundles table
        $table = new xmldb_table('local_ecominfo');
        $field = new xmldb_field('avgprice', XMLDB_TYPE_FLOAT, '20,2', null, XMLDB_NOTNULL, null, '0', null);
        $dbman->add_field($table, $field);
    }
    
    if ($oldversion < 2018100200) {
        // add new fields to the bundles table
        $table = new xmldb_table('local_ecominfo');
        $field = new xmldb_field('credithours', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);
        $dbman->add_field($table, $field);
    }
    
    if ($oldversion < 2018100300) {
        // add new fields to the bundles table
        $table = new xmldb_table('local_ecominfo_raw_data');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('ecomstudentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);
        $table->add_field('courses', XMLDB_TYPE_TEXT, '', null, XMLDB_NOTNULL, null, null, null);
        $table->add_field('price', XMLDB_TYPE_FLOAT, '20,2', null, XMLDB_NOTNULL, null, '0', null);
        $table->add_field('ecommproductid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);
        $table->add_field('orderid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);
        $table->add_field('orderdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);
        $table->add_field('credithours', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', null);
        
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        $dbman->create_table($table);
    }
    
    if ($oldversion < 2019031301) {
        // add new fields for categories in the ecommerce info table
        $table = new xmldb_table('local_ecominfo');
        $field = new xmldb_field('categories', XMLDB_TYPE_TEXT, '', null, XMLDB_NULL, null, null, null);
        $dbman->add_field($table, $field);
        
        $table = new xmldb_table('local_ecominfo_raw_data');
        $field = new xmldb_field('categories', XMLDB_TYPE_TEXT, '', null, XMLDB_NULL, null, null, null);
        $dbman->add_field($table, $field);
    }
    

    return true;
}