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

/**
 * Define upgrade steps to be performed to upgrade the plugin from the old version to the current one.
 *
 * @param int $oldversion Version number the plugin is being upgraded from.
 */
function xmldb_local_gitlab_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026042701) {
        $category = $DB->get_record('customfield_category', [
            'component' => 'local_gitlab',
            'area' => 'course'
        ]);
        if (!$category) {
            $category = (object)[
                'name' => 'GitLab',
                'component' => 'local_gitlab',
                'area' => 'course',
                'itemid' => 0,
                'sortorder' => 0
            ];
            $category->id = $DB->insert_record('customfield_category', $category);
        }

        $token = $DB->get_record('customfield_field', [
            'shortname' => 'gitlab_token',
            'categoryid' => $category->id,
            'area' => 'course'
        ]);
        if (!$token) {
            $DB->insert_record('customfield_field', [
                'shortname' => 'gitlab_token',
                'name' => 'GitLab Token',
                'type' => 'text',
                'categoryid' => $category->id,
                'configdata' => json_encode([]),
                'timecreated' => time(),
            ]);
        }

        $parent_group = $DB->get_record('customfield_field', [
            'shortname' => 'gitlab_parent_group',
            'categoryid' => $category->id,
            'area' => 'course'
        ]);
        if (!$parent_group) {
            $DB->insert_record('customfield_field', [
                'shortname' => 'gitlab_parent_group',
                'name' => 'GitLab Parent Group',
                'type' => 'select',
                'categoryid' => $category->id,
                'configdata' => json_encode([
                    'options' => "placeholder"
                ]),
                'timecreated' => time(),
            ]);
        }

        upgrade_plugin_savepoint(true, 2026042701, 'local', 'gitlab');
    }

    return true;
}
