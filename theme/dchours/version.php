<?php
/**
 * DC Hours Theme for Moodle
 *
 * @package   theme_dchours
 * @copyright 2018 Pivot Creative
 * @website https://pivotcreates.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This line protects the file from being accessed by a URL directly.                                                               
defined('MOODLE_INTERNAL') || die();                                                                                                
 
// This is the version of the plugin.                                                                                               
$plugin->version = '2018080800.00';                                                                                                    
 
// This is the version of Moodle this plugin requires.                                                                              
$plugin->requires = '2017110800';                                                                                                   
 
// This is the component name of the plugin - it always starts with 'theme_'                                                        
// for themes and should be the same as the name of the folder.                                                                     
$plugin->component = 'theme_dchours';                                                                                                 
 
// This is a list of plugins, this plugin depends on (and their versions).                                                          
$plugin->dependencies = [                                                                                                           
    'theme_boost' => '2017111300'                                                                                                   
];