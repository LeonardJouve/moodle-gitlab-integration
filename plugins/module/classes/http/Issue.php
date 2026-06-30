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
 * Group Gitlab client
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Issue {
    private Gitlab $client;

    public function __construct(Gitlab $client) {
        $this->client = $client;
    }

    public function list(int $project_id, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/issues", $params);
    }

    public function get(int $id, array $params = []) {
        return $this->client->get("/issues/" . $id, $params);
    }

    public function create(int $project_id, string $title, string $description = '', array $extra = []) {
        $data = array_merge([
            'title' => $title,
            'description' => $description,
        ], $extra);

        return $this->client->post("/projects/" . $project_id . "/issues", $data);
    }

    public function note(int $project_id, int $issue_id, string $body, array $extra = []) {
        $data = array_merge(['body' => $body], $extra);

        return $this->client->post("/projects/" . $project_id . "/issues/" . $issue_id . "/notes", $data);
    }

    public function update(int $project_id, int $issue_iid, array $data) {
        return $this->client->put("/projects/" . $project_id . "/issues/" . $issue_iid, $data);
    }
}
