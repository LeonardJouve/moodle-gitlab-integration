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
class Branch {
    private Gitlab $client;

    public function __construct(Gitlab $client) {
        $this->client = $client;
    }

    public function list(int $project_id, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/repository/branches", $params);
    }

    public function get(int $project_id, string $branch, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/repository/branches/" . urlencode($branch), $params);
    }

    public function create(int $project_id, string $branch, string $ref, array $extra = []) {
        $data = array_merge([
            'branch' => $branch,
            'ref'    => $ref,
        ], $extra);

        return $this->client->post("/projects/" . $project_id . "/repository/branches", $data);
    }

    public function delete(int $project_id, string $branch) {
        return $this->client->delete("/projects/" . $project_id . "/repository/branches/" . urlencode($branch));
    }

    public function protect(int $project_id, string $name, array $extra = []) {
        $data = array_merge([
            'name' => $name,
        ], $extra);

        return $this->client->post("/projects/" . $project_id . "/protected_branches", $data);
    }

    public function unprotect(int $project_id, string $name, array $data = []) {
        return $this->client->delete("/projects/" . $project_id . "/protected_branches/" . $name, $data);
    }
}
