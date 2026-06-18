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
use \core_external\external_multiple_structure;
use \core_external\external_value;
use context_course;
use mod_gitlab\local\Group;
use moodle_exception;

class set_group_members extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'groupid' => new external_value(PARAM_INT, 'id'),
            'members' => new external_multiple_structure(
                new external_value(PARAM_INT, 'id'),
            ),
        ]);
    }

    public static function execute(int $groupid, array $members) {
        global $DB;
    
        $module_id = $DB->get_field_sql("
            SELECT g.module_id
            FROM {gitlab_groups} g
            WHERE g.id = :group_id
        ", [
            'group_id' => $groupid,
        ]);

        $cm = get_coursemodule_from_id('gitlab', $module_id, 0, false, MUST_EXIST);
        $moduleinstance = $DB->get_record('gitlab', ['id' => $cm->instance], '*', MUST_EXIST);
        $coursecontext = context_course::instance($cm->course);

        foreach ($members as $user_id) {
            if (!is_enrolled($coursecontext, $user_id)) {
                throw new moodle_exception('usernotenrolled', 'mod_gitlab', '', $user_id);
            }
        }

        $ok = Group::set_group_members($module_id, $members, $moduleinstance->group_size, $groupid);
        
        return ['result' => $DB->get_in_or_equal($members, SQL_PARAMS_NAMED, 'user_id', true)];
        // return ['result' => $ok ? 'ok' : 'failed'];
    }

    public static function execute_returns() {
        return new external_function_parameters([
            'result' => new external_value(PARAM_TEXT, 'result'),
        ]);
    }
}