<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $ADMIN, $settings;

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('settings_gitlab', 'GitLab'));
    $settingspage = new admin_settingpage('gitlab', 'gitlab');

    if ($ADMIN->fulltree) {
        $settingspage->add(new admin_setting_configtext(
            'mod_gitlab/gitlab_host',
            get_string('setting_gitlab_host', 'mod_gitlab'),
            get_string('setting_gitlab_host_desc', 'mod_gitlab'),
            'https://gitlab.com',
        ));
    }

    $ADMIN->add('localplugins', $settingspage);
}