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
use mod_gitlab\local\Group;

class leave_group extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'groupid' => new external_value(PARAM_INT, 'id'),
        ]);
    }

    public static function execute(int $groupid) {
        global $USER, $DB;

        $module_id = $DB->get_field_sql("
            SELECT g.module_id
            FROM {gitlab_groups} g
            WHERE g.id = :group_id
        ", [
            'group_id' => $groupid,
        ]);

        $ok = Group::leave_group($module_id, $USER->id);

        return ['result' => $ok ? 'ok' : 'fail'];
    }

    public static function execute_returns() {
        return new external_function_parameters([
            'result' => new external_value(PARAM_TEXT, 'result'),
        ]);
    }
}