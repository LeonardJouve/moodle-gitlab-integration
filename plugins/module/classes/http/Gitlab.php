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
 * HTTP Client for Gitlab
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Gitlab {
    private const BASE_URL = 'https://gitlab.com/api/v4';
    private \curl $curl;
    private string $token;
    private Group $group;
    private Project $project;
    private User $user;
    private Branch $branch;
    private Invitation $invitation;
    private Issue $issue;
    private MergeRequest $merge_request;
    private Pipeline $pipeline;

    public function __construct(string $token) {
        $this->curl = new \curl();
        $this->token = $token;
        $this->group = new Group($this);
        $this->project = new Project($this);
        $this->user = new User($this);
        $this->branch = new Branch($this);
        $this->invitation = new Invitation($this);
        $this->issue = new Issue($this);
        $this->merge_request = new MergeRequest($this);
        $this->pipeline = new Pipeline($this);
    }

    public function post(string $endpoint, $data) {
        $this->curl->setHeader(array_merge($this->get_headers(), ['Content-type: application/json']));

        $result = $this->curl->post(gitlab::BASE_URL . $endpoint, json_encode($data));
        $this->handle_exceptions();

        return json_decode($result);
    }

    public function get(string $endpoint) {
        $this->curl->setHeader($this->get_headers());

        $result = $this->curl->get(gitlab::BASE_URL . $endpoint);
        $this->handle_exceptions();

        return json_decode($result);
    }

    private function handle_exceptions() {
        $status = $this->curl->get_info()['http_code'];

        if ($status >= 200 && $status < 300) {
            return;
        }

        if (400 === $status || 422 === $status) {
            throw new RuntimeException("validation failed");
        }

        if (429 === $status) {
            throw new RuntimeException("limit exceeded");
        }

        throw new RuntimeException(sprintf("expection %d", $status));
    }

    private function get_headers() {
        return [
            'Accept: application/json',
            'PRIVATE-TOKEN: ' . $this->token,
        ];
    }

    public function project() {
        return $this->project;
    }

    public function group() {
        return $this->group;
    }

    public function user() {
        return $this->user;
    }

    public function branch() {
        return $this->branch;
    }

    public function invitation() {
        return $this->invitation;
    }

    public function issue() {
        return $this->issue;
    }

    public function merge_request() {
        return $this->merge_request;
    }

    public function pipeline() {
        return $this->pipeline;
    }
}
