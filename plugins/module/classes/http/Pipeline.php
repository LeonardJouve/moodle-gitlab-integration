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
class Pipeline {
    private Gitlab $client;

    public function __construct(Gitlab $client) {
        $this->client = $client;
    }

    public function latest(int $project_id, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/pipelines/latest", $params);
    }

    public function get(int $project_id, int $pipeline_id, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/pipelines/" . $pipeline_id, $params);
    }

    public function list(int $project_id, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/pipelines", $params);
    }

    public function test_report(int $project_id, int $pipeline_id, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/pipelines/" . $pipeline_id . "/test_report", $params);
    }

    public function test_report_summary(int $project_id, int $pipeline_id, array $params = []) {
        return $this->client->get("/projects/" . $project_id . "/pipelines/" . $pipeline_id . "/test_report_summary", $params);
    }
}
