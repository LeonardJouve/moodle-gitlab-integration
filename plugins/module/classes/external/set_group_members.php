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
use mod_gitlab\http\Gitlab;
use mod_gitlab\local\bridge\Bridge;
use mod_gitlab\local\Helper;
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

        $moduleinstance = $DB->get_record('gitlab', ['id' => $module_id], '*', MUST_EXIST);
        /** @var \core\context $coursecontext */
        $coursecontext = context_course::instance($moduleinstance->course);

        foreach ($members as $user_id) {
            if (!is_enrolled($coursecontext, $user_id)) {
                throw new moodle_exception('usernotenrolled', 'mod_gitlab', '', $user_id);
            }
        }

        $token = Helper::get_course_gitlab_token($moduleinstance->course);
        $client = new Gitlab($token);
        $bridge = new Bridge($client);

        $ok = $bridge->set_group_members($members, $moduleinstance->group_size, $groupid);
        
        return ['result' => $ok ? 'ok' : 'failed'];
    }

    public static function execute_returns() {
        return new external_function_parameters([
            'result' => new external_value(PARAM_TEXT, 'result'),
        ]);
    }
}