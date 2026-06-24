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

class FinalizeGroupCreationTask extends adhoc_task {
    public static function instance(int $repository_id, int $module_id): self {
        $task = new self();
        $task->set_custom_data((object) [
            'repository_id' => $repository_id,
            'module_id' => $module_id,
        ]);

        return $task;
    }

    public function execute() {
        global $DB;

        $custom_data = $this->get_custom_data();
        $repository_id = $custom_data->repository_id;
        $module_id = $custom_data->module_id;

        $module = $DB->get_record('gitlab', ['id' => $module_id], '*', MUST_EXIST);

        $reviewers = json_decode($module->reviewers, true) ?? [];
        $token = Helper::get_course_gitlab_token($module->course);
        
        $client = new Gitlab($token);
        $bridge = new Bridge($client);

        $interval = 2;
        $timeout = time() + 120;
        do {
            $repository = $client->project()->get($repository_id);

            if ($repository->import_status === 'finished') {
                break;
            }

            if ($repository->import_status === 'failed' || time() > $timeout) {
                throw new \RuntimeException("GitLab import failed");
            }

            sleep($interval);
        } while (true);

        $bridge->finalize_create_group($repository->id, $reviewers, $module->template_id, $module->due_date);
    }

    public function retry_until_success(): bool {
        return false;
    }
}