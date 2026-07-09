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

use core\url;
use mod_gitlab\http\Gitlab;
use mod_gitlab\http\RuntimeException;
use stdClass;

class Resources {
    private Gitlab $client;
    
    public static int $maintainer_access_level = 40;
    public static int $developer_access_level = 30;

    public function __construct(Gitlab $client) {
        $this->client = $client;
    }

    public static function solutionBranch() {
        return 'solution';
    }

    public static function baseBranch() {
        return 'base';
    }

    public static function instructionIssue() {
        return 'instructions';
    }

    public static function defaultBranch() {
        return 'main';
    }

    public static function submissionMergeRequestLabel(int $group_id) {
        return "group-$group_id";
    }

    public static function updateGroupRepositoryMergeRequestLabel() {
        return 'update-group-repository';
    }

    public static function webhookModuleHeader() {
        return 'module-id';
    }

    public function get_gitlab_user_id(int $user_id): ?int {
        $username = Helper::get_user_gitlab_username($user_id);
        if ($username == null) {
            return null;
        }

        $gitlab_users = $this->client->user()->list(['username' => $username]);
        if (count($gitlab_users) != 1) {
            return null;
        }

        return $gitlab_users[0]->id;
    }

    public function get_instructions_issue(int $repository_id): ?stdClass {
        $issues = $this->client->issue()->list($repository_id, [
            'search' => Resources::instructionIssue(),
            'order_by' => 'created_at',
        ]);
        if (count($issues) == 0) {
            return null;
        }
            
        return $issues[0];
    }

    public function update_instructions_issue(int $project_id, int $issue_iid, string $description) {
        $this->client->issue()->update($project_id, $issue_iid, [
            'description' => $description,
        ]);
    }

    public function add_member(int $repository_id, int $user_id, int $access_level) {
        $username = Helper::get_user_gitlab_username($user_id);

        if ($username == null) {
            return;
        }

        $this->client->member()->add($repository_id, $username, $access_level);
    }
    
    public function add_reviewers_as_maintainers(int $repository_id, array $reviewers) {
        foreach ($reviewers as $reviewer) {
            $this->add_member($repository_id, $reviewer, Resources::$maintainer_access_level);
        }
    }

    public function create_instructions_issue(int $repository_id, string $content, int $due_date) {
        $this->client->issue()->create($repository_id, Resources::instructionIssue(), $content, [
            'start_date' => date('Y-m-d', time()),
            'due_date' => date('Y-m-d', $due_date),
        ]);
    }

    private function get_merge_request(int $repository_id, string $source, string $target, array $extra = []): ?stdClass {
        $merge_requests = $this->client->merge_request()->list($repository_id, array_merge([
            'source_branch' => $source,
            'target_branch' => $target,
        ], $extra));
        if (count($merge_requests) == 0) {
            return null;
        }

        return $merge_requests[0];
    }

    public function get_student_submission_merge_request(int $repository_id): ?stdClass {
        return $this->get_merge_request($repository_id, Resources::defaultBranch(), Resources::baseBranch());
    }

    public function get_teacher_submission_merge_request(int $template_id, int $group_id): ?stdClass {
        return $this->get_merge_request($template_id, Resources::defaultBranch(), Resources::defaultBranch(), [
            'labels' => Resources::submissionMergeRequestLabel($group_id),
        ]);
    }

    public function get_solution_merge_request(int $repository_id): ?stdClass {
        return $this->get_merge_request($repository_id, Resources::solutionBranch(), Resources::baseBranch());
    }

    public function get_latest_test_result(int $repository_id): ?stdClass {
        try {
            return $this->client->pipeline()->latest($repository_id, [
                'ref' => Resources::defaultBranch(),
            ]);
        }catch (RuntimeException $e) {
            return null;
        }
    }

    public function get_solution_branch(int $repository_id): ?stdClass {
        return $this->client->branch()->get($repository_id, Resources::solutionBranch());
    }

    public function create_template_webhook(int $template_id, string $secret): stdClass {
        return $this->client->webhook()->create(
            $template_id,
            (new url('/mod/gitlab/webhook.php'))->out(false),
            [
                'name' => get_string('webhook_name', 'mod_gitlab'),
                'signing_token' => 'whsec_' . $secret,
                'issues_events' => true,
                'push_events' => true,
                'branch_filter_strategy' => 'regex',
                'push_events_branch_filter' => '^' . Resources::defaultBranch() . '$',
                'enable_ssl_verification' => false,
            ],
        );
    }

    public function add_webhook_custom_header(int $module_id, int $hook_id, int $repository_id) {
        return $this->client->webhook()->set_custom_header(
            $repository_id,
            $hook_id,
            Resources::webhookModuleHeader(),
            $module_id,
        );
    }

    public function create_group_webhook(int $repository_id, string $secret): stdClass {
        return $this->client->webhook()->create(
            $repository_id,
            (new url('/mod/gitlab/webhook.php'))->out(false),
            [
                'name' => get_string('webhook_name', 'mod_gitlab'),
                'signing_token' => 'whsec_' . $secret,
                'merge_requests_events' => true,
                'enable_ssl_verification' => false,
            ],
        );
    }

    public function get_update_group_repository_merge_request(int $repository_id): ?stdClass {
        return $this->get_merge_request($repository_id, Resources::defaultBranch(), Resources::defaultBranch(), [
            'labels' => Resources::updateGroupRepositoryMergeRequestLabel(),
            'state' => 'opened',
        ]);
    }

    public function create_update_group_repository_merge_request(int $template_id, int $repository_id) {
        $this->client->merge_request()->create(
            $template_id,
            Resources::defaultBranch(),
            Resources::defaultBranch(),
            get_string('update_group_repository_merge_request_title', 'mod_gitlab'),
            [
                'target_project_id' => $repository_id,
                'labels' => Resources::updateGroupRepositoryMergeRequestLabel(),
            ],
        );
    }

    public function create_template_protected_files_ci(int $template_id) {
        global $PAGE;

        $this->client->file()->create(
            $template_id,
            '.gitlab-ci.yml',
            $PAGE->get_renderer('core')->render_from_template('mod_gitlab/gitlab-ci', []),
            Resources::defaultBranch(),
            get_string('commit_create_ci_file', 'mod_gitlab'),
        );

        $this->client->file()->create(
            $template_id,
            '.gitlab/protected-files',
            $PAGE->get_renderer('core')->render_from_template('mod_gitlab/protected-files', []),
            Resources::defaultBranch(),
            get_string('commit_create_protected_files', 'mod_gitlab'),
        );
    }
}