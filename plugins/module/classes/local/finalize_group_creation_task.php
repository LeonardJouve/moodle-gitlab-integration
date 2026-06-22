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

class finalize_group_creation_task extends \core\task\adhoc_task {
    public function execute() {
        $custom_data = $this->get_custom_data();
        $repository_id = $custom_data->repository_id;
        $reviewers = $custom_data->reviewers;
        $token = $custom_data->token;
        
        $client = new Gitlab($token);
        $bridge = new Bridge($client, $token);

        $timeout = time() + 30;
        do {
            $repository = $client->project()->get($repository_id);

            if ($repository->import_status === 'finished') {
                break;
            }

            if (time() > $timeout) {
                throw new \RuntimeException("GitLab import timeout");
            }

            sleep(2);
        } while (true);

        $bridge->finalize_create_group($repository, $reviewers);
    }
}