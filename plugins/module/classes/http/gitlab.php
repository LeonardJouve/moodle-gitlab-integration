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
class gitlab {
    private const BASE_URL = 'https://gitlab.com';
    private \curl $curl;
    private string $token;

    public function __construct(string $token) {
        $this->curl = new \curl();
        $this->token = $token;
    }

    private function post(string $endpoint, $data) {
        $options = [
            'CURLOPT_HTTPHEADER' => [
                'PRIVATE-TOKEN: ' . $this->token,
                'Accept: application/json',
            ],
        ];

        return @json_decode($this->curl->post(gitlab::BASE_URL . $endpoint, $data, $options));
    }

    public function createGroup(string $name) {
        $data = [
            'name' => $name,
            'path' => strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-')),
        ];
    
        return $this->post("/groups", $data);
    }

    public function createRepository(string $name, int $group_id) {
        $data = [
            'name' => $name,
            'namespace_id' => $group_id,
        ];

        return $this->post("/projects", $data);
    }
}
