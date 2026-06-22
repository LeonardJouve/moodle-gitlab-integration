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

    public function __construct(Gitlab $client, string $token) {
        $this->client = $client;
        $this->token = $token;
    }
    
    private function add_reviewers_as_maintainers(int $repository_id, array $reviewers) {
        foreach ($reviewers as $reviewer) {
            $username = Helper::get_user_gitlab_username($reviewer);

            if ($username == null) {
                continue;
            }

            $this->client->member()->add($repository_id, $username, Bridge::$maintainer_access_level);
        }
    }

    public function create_module(stdClass $moduleinstance) {
        $group = $this->client->group()->create($moduleinstance->name, $moduleinstance->parent_group);

        $template = $this->client->project()->create($moduleinstance->name . "_template", $group->id);

        // solution branch
        $this->client->branch()->create($template->id, Resources::solutionBranch(), $template->default_branch);
        
        // instructions
        $this->client->issue()->create($template->id, Resources::instructionIssue(), get_string('instructions_issue_help', 'mod_gitlab'));
        
        // reviewers
        $this->add_reviewers_as_maintainers($template->id, $moduleinstance->reviewer ?? []);

        return (object)[
            'group_id' => $group->id,
            'template_id' => $template->id,
        ];
    }

    public function create_group(int $module_id, stdClass $moduleinstance) {
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

        $task = new finalize_group_creation_task();
        $task->set_custom_data((object)[
            'repository_id' => $repository->id,
            'token' => $this->token,
            'reviewers' => json_decode($moduleinstance->reviewers, true) ?? [],
        ]);
        manager::queue_adhoc_task($task);

        // while ($repository->import_status !== 'finished') {
        //     $repository = $this->client->project()->get($repository->id);
        //     sleep(2);
        // }

        // base branch
        // TODO lock
        // $base = $this->client->branch()->create($repository->id, Resources::baseBranch(), $repository->default_branch);

        // // submission merge request
        // $this->client->merge_request()->create($repository->id, $repository->default_branch, $base->name, get_string('submission_merge_request_title', 'mod_gitlab'));

        // // reviewers
        // $this->add_reviewers_as_maintainers($repository->id, json_decode($moduleinstance->reviewers, true) ?? []);
        
        // TODO
        // instructions issue
        // permissions

        $group_id = Group::create_group($module_id, $repository->id);

        return (object)[
            'group_id' => $group_id,
        ];
    }

    public function finalize_create_group(stdClass $repository, array $reviewers) {
        // base branch
        // TODO lock
        $base = $this->client->branch()->create($repository->id, Resources::baseBranch(), $repository->default_branch);

        // submission merge request
        $this->client->merge_request()->create($repository->id, $repository->default_branch, $base->name, get_string('submission_merge_request_title', 'mod_gitlab'));

        // reviewers
        $this->add_reviewers_as_maintainers($repository->id, $reviewers);
    }
}