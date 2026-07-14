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
 * Display information about all the mod_gitlab modules in the requested course.
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_gitlab\http;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

/**
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MergeRequest {
    private Gitlab $client;

    public function __construct(Gitlab $client) {
        $this->client = $client;
    }

    public function list(int $project_id, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/merge_requests", $params);
    }

    public function get(int $project_id, int $merge_request_id, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/merge_requests/" . $merge_request_id, $params);
    }

    public function commits(int $project_id, int $merge_request_id, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/merge_requests/" . $merge_request_id . "/commits", $params);
    }

    public function create(int $project_id, string $source_branch, string $target_branch, string $title, array $extra = []) {
        $data = array_merge([
            'source_branch' => $source_branch,
            'target_branch' => $target_branch,
            'title' => $title,
        ], $extra);

        return $this->client->post("/projects/" . $project_id . "/merge_requests", $data);
    }

    public function note(int $project_id, int $merge_request_id, string $body, array $extra) {
        $data = array_merge(['body' => $body], $extra);

        return $this->client->post("/projects/" . $project_id . "/merge_requests/" . $merge_request_id . "/notes", $data);
    }
}