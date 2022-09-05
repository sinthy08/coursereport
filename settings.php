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
 * Links and settings
 *
 * Contains settings used by logs report.
 *
 * @package    local_plugin (coursereport)
 * @author     2022 Nawaz, Solin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    // Heading.
    $settings = new admin_settingpage('local_coursereport', get_string('pluginname', 'local_coursereport'));
    $ADMIN->add('localplugins', $settings);

    // Custom sender (from) email address.
    $default = '';
    $name = 'local_coursereport/customfield';
    $title = get_string('customfield', 'local_coursereport');
    $description = get_string('customfield_description', 'local_coursereport');
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $name = 'local_coursereport/formheadercolor';
    $title = get_string('formheadercolor', 'local_coursereport');
    $desc = get_string('formheadercolor_desc', 'local_coursereport');
    $setting = new admin_setting_configcolourpicker($name, $title, $desc, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);


    $name = 'local_coursereport/oddtuple';
    $title = get_string('oddtuple', 'local_coursereport');
    $desc = get_string('oddtuple_desc', 'local_coursereport');
    $setting = new admin_setting_configcolourpicker($name, $title, $desc, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $name = 'local_coursereport/border';
    $title = get_string('border', 'local_coursereport');
    $desc = get_string('border_desc', 'local_coursereport');
    $setting = new admin_setting_configcolourpicker($name, $title, $desc, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
