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

use core\task\manager;
use mod_gitlab\http\Gitlab;
use stdClass;

class Bridge {
    private Gitlab $client;
    private string $token;
    private static int $maintainer_access_level = 40;
    private static int $developer_access_level = 30;

    public function __construct(Gitlab $client, string $token) {
        $this->client = $client;
        $this->token = $token;
    }

    private function add_user(int $repository_id, int $user_id, int $access_level) {
        $username = Helper::get_user_gitlab_username($user_id);

        if ($username == null) {
            return;
        }

        $this->client->member()->add($repository_id, $username, $access_level);
    }
    
    private function add_reviewers_as_maintainers(int $repository_id, array $reviewers) {
        foreach ($reviewers as $reviewer) {
            $this->add_user($repository_id, $reviewer, Bridge::$maintainer_access_level);
        }
    }

    private function create_instructions_issue(int $repository_id, string $content, int $due_date) {
        $this->client->issue()->create($repository_id, Resources::instructionIssue(), $content, [
            'start_date' => date('Y-m-d', time()),
            'due_date' => date('Y-m-d', $due_date),
        ]);
    }

    public function create_module(stdClass $moduleinstance) {
        global $DB;

        $group = $this->client->group()->create($moduleinstance->name, $moduleinstance->parent_group);

        $template = $this->client->project()->create($moduleinstance->name . "_template", $group->id);
        // $this->client->branch()->unprotect($template->id, $template->default_branch);

        // solution branch
        $this->client->branch()->create($template->id, Resources::solutionBranch(), $template->default_branch);
        
        // instructions
        $this->create_instructions_issue($template->id, get_string('instructions_issue_help', 'mod_gitlab'), $moduleinstance->due_date);
        
        // reviewers
        $this->add_reviewers_as_maintainers($template->id, $moduleinstance->reviewer ?? []);

        $moduleinstance->reviewers = json_encode($moduleinstance->reviewer ?: [], JSON_UNESCAPED_UNICODE);
        $moduleinstance->timecreated = time();
        $moduleinstance->group_id = $group->id;
        $moduleinstance->template_id = $template->id;

        $id = $DB->insert_record('gitlab', $moduleinstance);

        // release solution task
        if ($moduleinstance->due_date > time()) {
            $task = ReleaseSolutionTask::instance($id);
            $task->set_next_run_time($moduleinstance->due_date);
            manager::queue_adhoc_task($task);
        }

        return (object)[
            'group_id' => $group->id,
            'template_id' => $template->id,
            'module_id' => $id,
        ];
    }

    public function create_group(stdClass $moduleinstance) {
        $template = $this->client->project()->get($moduleinstance->template_id);

        $parts = parse_url($template->http_url_to_repo);
        $import_url = sprintf(
            '%s://oauth2:%s@%s%s',
            $parts['scheme'],
            rawurlencode($this->token),
            $parts['host'],
            $parts['path'],
        );

        $repository = $this->client->project()->create(
            $moduleinstance->name . "_" . bin2hex(random_bytes(8)),
            $moduleinstance->group_id,
            [
                'import_url' => $import_url,
            ],
        );

        $group_id = Group::create_group($moduleinstance->id, $repository->id);

        $task = FinalizeGroupCreationTask::instance(
            $repository->id,
            $moduleinstance->id,
        );
        manager::queue_adhoc_task($task);

        return (object)[
            'group_id' => $group_id,
        ];
    }

    public function finalize_create_group(stdClass $repository, array $reviewers, int $template_id, int $due_date) {
        $this->client->branch()->unprotect($repository->id, $repository->default_branch);

        // base branch
        $base = $this->client->branch()->create($repository->id, Resources::baseBranch(), $repository->default_branch);
        $this->client->branch()->protect($repository->id, $base->name);

        // submission merge request
        $this->client->merge_request()->create($repository->id, $repository->default_branch, $base->name, get_string('submission_merge_request_title', 'mod_gitlab'));

        // reviewers
        $this->add_reviewers_as_maintainers($repository->id, $reviewers);

        // remove all branches
        $branches = $this->client->branch()->list($repository->id);
        foreach ($branches as $branch) {
            if ($branch->name == $repository->default_branch || $branch->protected) {
                continue;
            }

            $this->client->branch()->delete($repository->id, $branch->name);
        }

        // instructions issue
        $issues = $this->client->issue()->list($template_id, [
            'search' => Resources::instructionIssue(),
            'order_by' => 'created_at',
        ]);
        if (count($issues) >= 1) {
            $issue = $issues[0];
            $this->create_instructions_issue($repository->id, $issue->description, $due_date);
        }
    }

    public function join_group(int $group_id, int $user_id, stdClass $moduleinstance): bool {
        $group = Group::group($group_id);
    
        $ok = Group::join_group($moduleinstance->id, $group_id, $user_id, $moduleinstance->group_size);
        if (!$ok) {
            return false;
        }

        $this->add_user($group->repository_id, $user_id, Bridge::$developer_access_level);

        return true;
    }

    public function release_solution(int $module_id, int $template_id) {
        $groups = Group::get_groups($module_id);
        foreach ($groups as $group) {
            $this->client->merge_request()->create(
                $template_id,
                Resources::solutionBranch(),
                Resources::baseBranch(),
                get_string('solution_merge_request_title', 'mod_gitlab'),
                ['target_project_id' => $group->repository_id],
            );
        }
    }
}