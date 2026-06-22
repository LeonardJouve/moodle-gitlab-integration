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

use mod_gitlab\http\Gitlab;
use stdClass;

class Bridge {
    private Gitlab $client;
    private static int $maintainer_access_level = 40;

    public function __construct(Gitlab $client) {
        $this->client = $client;
    }

    public function create_module(stdClass $moduleinstance) {
        $group = $this->client->group()->create($moduleinstance->name, $moduleinstance->parent_group);

        $template = $this->client->project()->create($moduleinstance->name . "_template", $group->id);

        // solution branch
        $this->client->branch()->create($template->id, Resources::solutionBranch(), $template->default_branch);
        
        // instructions
        $this->client->issue()->create($template->id, Resources::instructionIssue());
        
        // reviewers
        foreach ($moduleinstance->reviewers as $reviewer) {
            $username = Helper::get_user_gitlab_username($reviewer);

            if ($username == null) {
                continue;
            }

            $this->client->member()->add($template->id, $username, Bridge::$maintainer_access_level);
        }

        return (object)[
            'group_id' => $group->id,
            'template_id' => $template->id,
        ];
    }

    public function create_group(int $module_id, stdClass $moduleinstance) {
        $repository = $this->client->project()->create(
            $moduleinstance->name . "_" . bin2hex(random_bytes(8)),
            $moduleinstance->group_id,
        );
        
        // TODO
        // create from template
        // pr
        // issue
        // reviewers
        // permissions

        Group::create_group($module_id, $repository->id);
    }
}