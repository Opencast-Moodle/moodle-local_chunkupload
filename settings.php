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
 * Chunkupload settings.
 *
 * @package    local_chunkupload
 * @copyright  2020 Justus Dieckmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
global $ADMIN;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_chunkupload', get_string('pluginname', 'local_chunkupload'));
    $settings->add(new admin_setting_configtext('local_chunkupload/chunksize',
            new lang_string('setting:chunksize', 'local_chunkupload'),
            null, 64, PARAM_INT));
    $settings->add(new admin_setting_configduration('local_chunkupload/state0duration',
            new lang_string('setting:state0duration', 'local_chunkupload'),
            null, 3600, 3600));
    $settings->add(new admin_setting_configduration('local_chunkupload/state1duration',
            new lang_string('setting:state1duration', 'local_chunkupload'),
            null, 3600, 3600));
    $settings->add(new admin_setting_configduration('local_chunkupload/state2duration',
            new lang_string('setting:state2duration', 'local_chunkupload'),
            null, 86400, 86400));
    $ADMIN->add('localplugins', $settings);
}
