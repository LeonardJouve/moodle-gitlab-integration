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
 * Plugin strings are defined here.
 *
 * @package     local_gitlab
 * @category    string
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_gitlab_install() {
    $handler = \core_customfield\handler::get_handler('core_course', 'course');
    
    $category = $handler->create_category('GitLab');
    
    $token = new \core_customfield\field(0, (object)[
        'shortname' => 'gitlab_token',
        'name' => 'GitLab Token',
        'type' => 'text',
        'categoryid' => $category,
        'configdata' => json_encode([
            'required' => 0,
            'uniquevalues' => 0,
            'ispassword' => 0,
            'displaysize' => 50,
            'maxlength' => 255
        ]),
    ]);
    $token->save();

    $groups = new \core_customfield\field(0, (object)[
        'shortname' => 'gitlab_groups',
        'name' => 'GitLab Groups',
        'type' => 'select',
        'categoryid' => $category,
        'configdata' => json_encode([
            'options' => '',
            'defaultvalue' => '',
            'multiple' => 0,
            'ispassword' => 0,
            'separator' => '\n'
        ]),
    ]);
    $groups->save();
}