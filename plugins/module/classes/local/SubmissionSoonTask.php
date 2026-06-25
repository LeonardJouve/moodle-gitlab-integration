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

namespace mod_gitlab\local;

use core\task\adhoc_task;
use mod_gitlab\http\Gitlab;

class SubmissionSoonTask extends adhoc_task {
    public static function instance(int $module_id): self {
        $task = new self();
        $task->set_custom_data((object) [
            'module_id' => $module_id,
        ]);

        return $task;
    }

    public function execute() {
        global $DB;

        $custom_data = $this->get_custom_data();
        $module_id = $custom_data->module_id;

        $module = $DB->get_record('gitlab', ['id' => $module_id], '*', MUST_EXIST);

        $token = Helper::get_course_gitlab_token($module->course);
        $client = new Gitlab($token);
        $bridge = new Bridge($client);

        $bridge->send_submission_notifications($module, true);
    }

    public function retry_until_success(): bool {
        return false;
    }
}