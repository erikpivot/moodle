<?php
// This file is part of the Local Pace States plugin
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
 * designate states as PACE compliant states
 *
 * @package    local
 * @subpackage pacestates
 * @copyright  2018 Pivot Creative
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Ensure the configurations for this site are set
if ($hassiteconfig) {
    // create the new settings page
    $settings = new admin_settingpage('local_pacestates', 'PACE States');
    
    // Create
    $ADMIN->add('localplugins', $settings);
    
    // add the settings for each state
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceal',
        get_string('paceal', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceak',
        get_string('paceak', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceaz',
        get_string('paceaz', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacear',
        get_string('pacear', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceca',
        get_string('paceca', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceco',
        get_string('paceco', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacect',
        get_string('pacect', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacede',
        get_string('pacede', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacedc',
        get_string('pacedc', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacefl',
        get_string('pacefl', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacega',
        get_string('pacega', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacehi',
        get_string('pacehi', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceid',
        get_string('paceid', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceil',
        get_string('paceil', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacein',
        get_string('pacein', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceia',
        get_string('paceia', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceks',
        get_string('paceks', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceky',
        get_string('paceky', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacela',
        get_string('pacela', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceme',
        get_string('paceme', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacemd',
        get_string('pacemd', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacema',
        get_string('pacema', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacemi',
        get_string('pacemi', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacemn',
        get_string('pacemn', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacems',
        get_string('pacems', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacemo',
        get_string('pacemo', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacemt',
        get_string('pacemt', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacene',
        get_string('pacene', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacenv',
        get_string('pacenv', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacenh',
        get_string('pacenh', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacenj',
        get_string('pacenj', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacenm',
        get_string('pacenm', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceny',
        get_string('paceny', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacenc',
        get_string('pacenc', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacend',
        get_string('pacend', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceoh',
        get_string('paceoh', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceok',
        get_string('paceok', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceor',
        get_string('paceor', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacepa',
        get_string('pacepa', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceri',
        get_string('paceri', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacesc',
        get_string('pacesc', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacesd',
        get_string('pacesd', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacetn',
        get_string('pacetn', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacetx',
        get_string('pacetx', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceut',
        get_string('paceut', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacevt',
        get_string('pacevt', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_paceva',
        get_string('paceva', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacewa',
        get_string('pacewa', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacewa',
        get_string('pacewa', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacewv',
        get_string('pacewv', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacewi',
        get_string('pacewi', 'local_pacestates'),
        '',
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'pacestates_pacewy',
        get_string('pacewy', 'local_pacestates'),
        '',
        0
    ));
}
