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

namespace mod_gitlab\external;

defined('MOODLE_INTERNAL') || die();

use \core_external\external_api;
use \core_external\external_function_parameters;
use \core_external\external_value;
use \core_external\external_multiple_structure;
use \core_external\external_single_structure;
use core_user;

class form_user_selector extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'course id'),
            'groupid' => new external_value(PARAM_INT, 'group id'),
            'search' => new external_value(PARAM_RAW, 'query'),
        ]);
    }

    public static function execute(int $courseid, int $groupid, string $search) {
        global $DB;

        require_once("/user/lib.php");

        $module_id = $DB->get_field_sql("
            SELECT g.module_id
            FROM {gitlab_groups} g
            WHERE g.id = :group_id
        ", [
            'group_id' => $groupid,
        ]);

        $sql_search = '%' . $search . '%';
        $users = $DB->get_records_sql("
            SELECT DISTINCT u.*
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :course_id)
            WHERE (u.firstname LIKE :search1 OR u.lastname LIKE :search2 OR u.username LIKE :search3) AND NOT EXISTS (
                SELECT 1
                FROM {gitlab_groups} g
                JOIN {gitlab_group_members} gm ON gm.group_id = g.id
                WHERE g.module_id = :module_id
                    AND gm.user_id = u.id
            )
        ", [
            'course_id' => $courseid,
            'search1'   => $sql_search,
            'search2'   => $sql_search,
            'search3'   => $sql_search,
            'module_id' => $module_id,
        ], 0, 50);

        $course = get_course($courseid);

        $results = [];
        foreach ($users as $user) {
            if ($userdetails = user_get_user_details($user, $course, ['id', 'fullname', 'profileimageurlsmall'])) {
                $results[] = $userdetails;
            }
        }

        return $results;
    }

    public static function execute_returns() {
        return new external_multiple_structure(new external_single_structure([
            'id' => new external_value(core_user::get_property_type('id'), 'ID of the user'),
            // 'username' => new external_value(core_user::get_property_type('username'), 'The username', VALUE_OPTIONAL),
            // 'firstname' => new external_value(core_user::get_property_type('firstname'), 'The first name(s) of the user', VALUE_OPTIONAL),
            // 'lastname' => new external_value(core_user::get_property_type('lastname'), 'The family name of the user', VALUE_OPTIONAL),
            'fullname' => new external_value(core_user::get_property_type('firstname'), 'The fullname of the user'),
            'profileimageurlsmall' => new external_value(PARAM_URL, 'User image profile URL - small version'),
        ]));
    }
}