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
    private Resources $resources;

    public function __construct(Gitlab $client) {
        $this->client = $client;
        $this->resources = new Resources($client);
    }

    public function create_module(stdClass $moduleinstance) {
        global $DB;

        $group = $this->client->group()->create($moduleinstance->name, $moduleinstance->parent_group);

        $template = $this->client->project()->create($moduleinstance->name . "_template", $group->id, [
            'default_branch' => Resources::defaultBranch(),
            'initialize_with_readme' => true,
        ]);
        // $this->client->branch()->unprotect($template->id, $template->default_branch);

        // solution branch
        $this->client->branch()->create($template->id, Resources::solutionBranch(), $template->default_branch);
        
        // instructions
        $this->resources->create_instructions_issue($template->id, get_string('instructions_issue_help', 'mod_gitlab'), $moduleinstance->due_date);
        
        // reviewers
        $this->resources->add_reviewers_as_maintainers($template->id, $moduleinstance->reviewer ?? []);

        $moduleinstance->reviewers = json_encode($moduleinstance->reviewer ?: [], JSON_UNESCAPED_UNICODE);
        $moduleinstance->timecreated = time();
        $moduleinstance->group_id = $group->id;
        $moduleinstance->template_id = $template->id;

        $id = $DB->insert_record('gitlab', $moduleinstance);

        // release solution task
        if ($moduleinstance->due_date > time()) {
            $task = SubmissionTask::instance($id);
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
        $name = $moduleinstance->name . "_" . bin2hex(random_bytes(8));

        $repository = $this->client->project()->fork(
            $moduleinstance->template_id,
            $name,
            $moduleinstance->group_id,
            [
                'branches' => Resources::defaultBranch(),
                'path' => $name,    
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

    public function finalize_create_group(int $repository_id, array $reviewers, int $template_id, int $due_date) {
        $this->client->branch()->unprotect($repository_id, Resources::defaultBranch());

        // base branch
        $base = $this->client->branch()->create($repository_id, Resources::baseBranch(), Resources::defaultBranch());
        $this->client->branch()->protect($repository_id, $base->name);

        // submission merge request
        $this->client->merge_request()->create($repository_id, Resources::defaultBranch(), $base->name, get_string('submission_merge_request_title', 'mod_gitlab'));

        // reviewers
        $this->resources->add_reviewers_as_maintainers($repository_id, $reviewers);

        // instructions issue
        $issue = $this->resources->get_instructions_issue($template_id);
        if ($issue != null) {
            $this->resources->create_instructions_issue($repository_id, $issue->description, $due_date);
        }
    }

    public function join_group(int $group_id, int $user_id, stdClass $moduleinstance): bool {
        $group = Group::group($group_id);
    
        $ok = Group::join_group($moduleinstance->id, $group_id, $user_id, $moduleinstance->group_size);
        if (!$ok) {
            return false;
        }

        $this->resources->add_member($group->repository_id, $user_id, Resources::$developer_access_level);

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

    public function submit_student_merge_requests(int $module_id, int $template_id) {
        $groups = Group::get_groups($module_id);
        foreach ($groups as $group) {
            $name = implode("-", explode(',', trim($group->members, '{}'))) . ':group-' . $group->id;
        
            $this->client->merge_request()->create(
                $group->repository_id,
                Resources::defaultBranch(),
                Resources::defaultBranch(),
                get_string('template_submission_merge_request_title', 'mod_gitlab', ['name' => $name]),
                ['target_project_id' => $template_id],
            );
        }
    }

    public function leave_group(int $module_id, int $user_id): bool {
        $gitlab_user_id = $this->resources->get_gitlab_user_id($user_id);
        if ($gitlab_user_id == null) {
            return false;
        }

        $group_id = Group::user_group($module_id, $user_id);
        $group = Group::group($group_id);

        $this->client->member()->remove($group->repository_id, $gitlab_user_id);

        return Group::leave_group($module_id, $user_id);
    }

    public function set_group_members(array $members, int $max_member, int $group_id) {
        global $DB;

        $group = Group::group($group_id);

        list($not_in_sql, $params) = $DB->get_in_or_equal($members, SQL_PARAMS_NAMED, '', false, NULL);
        $params['group_id'] = $group_id;
        $to_remove = $DB->get_fieldset_select(
            'gitlab_group_members',
            'user_id',
            "group_id = :group_id AND user_id $not_in_sql",
            $params,
        );
        foreach ($to_remove as $user_id) {
            $gitlab_user_id = $this->resources->get_gitlab_user_id($user_id);
            if ($gitlab_user_id == null) {
                continue;
            }

            $this->client->member()->remove($group->repository_id, $gitlab_user_id);
        }

        $in_group = $DB->get_fieldset_select(
            'gitlab_group_members',
            'user_id',
            'group_id = :group_id',
            ['group_id' => $group_id],
        );
        foreach ($members as $member) {
            if (in_array($member, $in_group)) {
                continue;
            }

            $username = Helper::get_user_gitlab_username($member);
            if ($username == null) {
                continue;
            }

            $this->client->member()->add($group->repository_id, $username, Resources::$developer_access_level);
        }

        return Group::set_group_members($members, $max_member, $group_id);
    }
}