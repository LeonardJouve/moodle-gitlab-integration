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

use context_module;
use \core_external\external_api;
use \core_external\external_function_parameters;
use \core_external\external_value;
use mod_gitlab\http\Gitlab;
use mod_gitlab\local\bridge\Bridge;
use mod_gitlab\local\Helper;

class release_solution extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'moduleid' => new external_value(PARAM_INT, 'id'),
        ]);
    }

    public static function execute(int $moduleid) {
        global $DB;

        self::validate_parameters(self::execute_parameters(), ['moduleid' => $moduleid]);

        $module = $DB->get_record('gitlab', ['id' => $moduleid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('gitlab', $module->id, 0, false, MUST_EXIST);
        /** @var \core\context $context */
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/gitlab:releasesolution', $context);
       
        $token = Helper::get_course_gitlab_token($module->course);
        $client = new Gitlab($token);
        $bridge = new Bridge($client);

        $bridge->release_solution($moduleid, $module->template_id);

        return ['result' => 'ok'];
    }

    public static function execute_returns() {
        return new external_function_parameters([
            'result' => new external_value(PARAM_TEXT, 'result'),
        ]);
    }
}