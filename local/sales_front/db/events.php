<?php
// This file is part of the sales front plugin
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
 * This plugin handles events to send to the sales front end
 *
 * @package    local
 * @subpackage sales_front
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\core\event\course_created',
        'callback' => '\local_sales_front\observer::create_product',
    ),
    array(
        'eventname' => '\core\event\course_updated',
        'callback' => '\local_sales_front\observer::update_product',
    ),
    array(
        'eventname' => '\core\event\course_deleted',
        'callback' => '\local_sales_front\observer::delete_product',
    ),
    array(
        'eventname' => '\core\event\tag_created',
        'callback' => '\local_sales_front\observer::add_product_category',
    ),
    array(
        'eventname' => '\core\event\tag_updated',
        'callback' => '\local_sales_front\observer::update_product_category',
    ),
    array(
        'eventname' => '\core\event\tag_deleted',
        'callback' => '\local_sales_front\observer::delete_product_category',
    ),
    array(
        'eventname' => '\core\event\course_module_created',
        'callback' => '\local_sales_front\observer::add_certificate'
    )
);
