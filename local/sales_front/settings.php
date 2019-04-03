<?php
// This file is part of the sales front plugin
//
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

defined('MOODLE_INTERNAL') || die;

// Ensure the configurations for this site are set
if ($hassiteconfig) {
    // create the new settings page
    $settings = new admin_settingpage('local_sales_front', 'Sales Frontend Settings');
    
    // Create
    $ADMIN->add('localplugins', $settings);
    
    // add text box to enter the url of the ecommerce frontend site
    $name = 'local_sales_front/ecommerce_url';
    $title = get_string('ecommerce_site', 'local_sales_front');
    $description = get_string('ecommerce_site_desc', 'local_sales_front');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $settings->add($setting);
    
    // add text box to enter the woocommerce client key for the ecommerce frontend site
    $name = 'local_sales_front/wc_client_key';
    $title = get_string('client_key', 'local_sales_front');
    $description = get_string('client_key_desc', 'local_sales_front');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $settings->add($setting);
    
    // add text box to enter the woocommerce client secret for the ecommerce frontend site
    $name = 'local_sales_front/wc_client_secret';
    $title = get_string('client_secret', 'local_sales_front');
    $description = get_string('client_secret_desc', 'local_sales_front');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $settings->add($setting);
    
}