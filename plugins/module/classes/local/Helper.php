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
 * Display information about all the mod_gitlab modules in the requested course.
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_gitlab\local;

class Helper {
    public static function get_course_gitlab_token(int $courseid): ?string {
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $customfields = $handler->get_instance_data($courseid, true);

        foreach ($customfields as $fielddata) {
            if ($fielddata->get_field()->get('shortname') === 'gitlab_token' && $fielddata->get_value() !== '') {
                return $fielddata->get_value();
            }
        }

        return null;
    }

    public static function get_user_gitlab_username(int $user_id): ?string {
        global $DB;

        $username = $DB->get_field_sql("
            SELECT d.data
            FROM {user_info_data} d
            JOIN {user_info_field} f ON f.id = d.fieldid
            WHERE d.userid = :userid
                AND f.shortname = :shortname
        ", [
            'userid' => $user_id,
            'shortname' => 'gitlab_username',
        ]);

        return $username ?: null;
    }
}
