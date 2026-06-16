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

        $module_id = $DB->get_field_sql("
            SELECT g.module_id
            FROM {gitlab_groups} g
            WHERE g.id = :group_id
        ", [
            'group_id' => $groupid,
        ]);

        $users = $DB->get_records_sql("
            SELECT DISTINCT u.*
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON e.id = ue.enrolid
            LEFT JOIN {gitlab_group_members} gm
                ON gm.user_id = u.id
            LEFT JOIN {gitlab_groups} g
                ON g.id = gm.group_id AND g.module_id = :module_id
            WHERE e.courseid = :course_id
                AND g.id IS NULL
                AND (
                    u.firstname LIKE :search
                    OR u.lastname LIKE :search
                    OR u.email LIKE :search
                )
        ", [
            'course_id' => $courseid,
            'module_id' => $module_id,
            'search' => '%' . $search . '%',
        ], 0, 50);

        $course = get_course($courseid);

        $requiredfields = ['id', 'username', 'firstname', 'lastname', 'fullname', 'profileimageurlsmall'];
        
        $results = [];
        foreach ($users as $user) {
            if ($userdetails = user_get_user_details($user, $course, $requiredfields)) {
                $results[] = $userdetails;
            }
        }

        return $results;
    }

    public static function execute_returns() {
        return new external_multiple_structure(new external_single_structure([
            'id' => new external_value(core_user::get_property_type('id'), 'ID of the user'),
            'username' => new external_value(core_user::get_property_type('username'), 'The username', VALUE_OPTIONAL),
            'firstname' => new external_value(core_user::get_property_type('firstname'), 'The first name(s) of the user', VALUE_OPTIONAL),
            'lastname' => new external_value(core_user::get_property_type('lastname'), 'The family name of the user', VALUE_OPTIONAL),
            'fullname' => new external_value(core_user::get_property_type('firstname'), 'The fullname of the user'),
            'profileimageurlsmall' => new external_value(PARAM_URL, 'User image profile URL - small version'),
        ]));
    }
}