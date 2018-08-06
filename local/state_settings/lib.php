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
 * Library code used by the service control interfaces.
 *
 * @package   local_state_settings
 * @copyright 2018 Pivot Creative <team@pivotcreates.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

// include third party libraries
require_once __DIR__ . '/classes/approvals.php';
require_once __DIR__ . '/../../vendor/autoload.php';
use Automattic\WooCommerce\Client;

/**
 * Getting the state settings
 * 
 * @param number $bundleid
 * @return object
 */
function local_state_settings_get_records() {
    global $DB;
    
    $settingsrecords = $DB->get_records('local_state_settings');
    
    $settingsobj = local_state_settings_build_data($settingsrecords);
    
    return $settingsobj;
}

/**
 * Update the record in the database
 * 
 * @param object $data
 * @param boolean $insert
 * @return boolean
 */
function local_state_settings_update_record($data, $all_states) {
    global $DB, $approvals;
    file_put_contents(__DIR__ . '/state_setting_data.txt', print_r($data, true), FILE_APPEND);
    // save the settings for each state
    $woo_data = array(
        'update' => array()
    );
    foreach($all_states as $key => $value) {
        $ecommdescript = '';
        $up_data = new stdClass();
        $setapprove = $key . 'setapprove';
        
        
        $approval = $key . 'stateapprove';
        $requirements = $key . 'staterequire';
        
        // custom approval?
        if ($data->$setapprove != 0) {
            $ecommdescript .= $approvals[$data->$setapprove]['ecomm_text'];
        }
        
        // state board approved?
        if (1 == $data->$approval) {
            $ecommdescript .= "State Board Approved\n";
        }
        
        $up_data->customapprove = $data->$setapprove;
        $up_data->stateapproval = $data->$approval;
        $up_data->staterequire = $data->$requirements['text'];
        $ecommdescript .= $data->$requirements['text'];
        /*
        $sql = "UPDATE {local_state_settings} SET customapprove = " . $up_data->customapprove . ",
                stateapproval = " . $up_data->stateapproval . ", 
                staterequire = '" . str_replace("'", "\'", $up_data->staterequire) . "' WHERE state = '" . $key . "'";
        file_put_contents(__DIR__ . '/update_sql.txt', $sql . "\n", FILE_APPEND);
        */
        $sql = "UPDATE {local_state_settings} SET customapprove = ?, stateapproval = ?, staterequire = ? WHERE state = ?";
        $params = array(
            'customapprove' => $up_data->customapprove,
            'stateapproval' => (0 == $up_data->customapprove ? 1 : $up_data->stateapproval),
            'staterequire' => $up_data->staterequire,
            'state' => $key
        );
        file_put_contents(__DIR__ . '/update_sql.txt', print_r($params, true) . "\n", FILE_APPEND);
        $result = $DB->execute($sql, $params);
        
        // get the ecommerce category id
        $setting_info = $DB->get_record('local_state_settings', array('state' => $key));
        
        // update the description on the eCommerce site
        $woo_data['update'][] = array(
            'id' => $setting_info->ecommcatid,
            'description' => $ecommdescript
        );
    }
    
    // update the product categories
    $config = get_config('local_sales_front');
    $ch = curl_init($config->ecommerce_url . "/wp-json/wc/v2/products/categories/batch/?consumer_key=" . $config->wc_client_key . "&consumer_secret=" . $config->wc_client_secret);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($woo_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    
    return true;
}

/**
 * Builds the proper data object for mapping the 
 * query results to the form elements
 * 
 * @param object $records
 * @return object
 */
function local_state_settings_build_data($records) {
    $dataobj = new stdClass();
    
    foreach ($records as $setting) {
        $setapprove = $setting->state . 'setapprove';
        $stateapprove = $setting->state . 'stateapprove';
        $staterequire = $setting->state . 'staterequire';
        $dataobj->$setapprove = $setting->customapprove;
        $dataobj->$stateapprove = $setting->stateapproval;
        $dataobj->$staterequire = array('text' => $setting->staterequire, 'format' => 1);
    }
    
    return $dataobj;
}