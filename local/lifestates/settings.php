<?php
// This file is part of the Local life States plugin
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
 * This plugin creates a place where an administrator can
 * designate states as Life University Sponsorship states
 *
 * @package    local
 * @subpackage lifestates
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Ensure the configurations for this site are set
if ($hassiteconfig) {
    // create the new settings page
    $settings = new admin_settingpage('local_lifestates', 'Life University Sponsorship States');
    
    // Create
    $ADMIN->add('localplugins', $settings);
    
    // add the settings for each state
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeal',
        get_string('lifeal', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeak',
        get_string('lifeak', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeaz',
        get_string('lifeaz', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifear',
        get_string('lifear', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeca',
        get_string('lifeca', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeco',
        get_string('lifeco', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifect',
        get_string('lifect', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifede',
        get_string('lifede', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifedc',
        get_string('lifedc', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifefl',
        get_string('lifefl', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifega',
        get_string('lifega', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifehi',
        get_string('lifehi', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeid',
        get_string('lifeid', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeil',
        get_string('lifeil', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifein',
        get_string('lifein', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeia',
        get_string('lifeia', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeks',
        get_string('lifeks', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeky',
        get_string('lifeky', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifela',
        get_string('lifela', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeme',
        get_string('lifeme', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifemd',
        get_string('lifemd', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifema',
        get_string('lifema', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifemi',
        get_string('lifemi', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifemn',
        get_string('lifemn', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifems',
        get_string('lifems', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifemo',
        get_string('lifemo', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifemt',
        get_string('lifemt', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifene',
        get_string('lifene', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifenv',
        get_string('lifenv', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifenh',
        get_string('lifenh', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifenj',
        get_string('lifenj', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifenm',
        get_string('lifenm', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeny',
        get_string('lifeny', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifenc',
        get_string('lifenc', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifend',
        get_string('lifend', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeoh',
        get_string('lifeoh', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeok',
        get_string('lifeok', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeor',
        get_string('lifeor', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifepa',
        get_string('lifepa', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_liferi',
        get_string('liferi', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifesc',
        get_string('lifesc', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifesd',
        get_string('lifesd', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifetn',
        get_string('lifetn', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifetx',
        get_string('lifetx', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeut',
        get_string('lifeut', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifevt',
        get_string('lifevt', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifeva',
        get_string('lifeva', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifewa',
        get_string('lifewa', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifewa',
        get_string('lifewa', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifewv',
        get_string('lifewv', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifewi',
        get_string('lifewi', 'local_lifestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'lifestates_lifewy',
        get_string('lifewy', 'local_lifestates'),
        '',
        0
    ));
}
