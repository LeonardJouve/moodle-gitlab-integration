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
use mod_gitlab\http\RuntimeException;
use mod_gitlab\local\bridge\Bridge;

class Action {
    public static function join_group(Bridge $bridge, mixed $moduleinstance) {
        global $USER;

        $group_id = optional_param('group_id', '', PARAM_INT);
        if (!$group_id || !$bridge->join_group($group_id, $USER->id, $moduleinstance)) {
            Template::error(get_string('message_error_join_group', 'mod_gitlab'));
            return false;
        }

        redirect(
            new url('/mod/gitlab/view.php', ['g' => $moduleinstance->id]),
            get_string('message_joined_group', 'mod_gitlab'),
        );
        
        return true;
    }

    public static function create_group(Bridge $bridge, mixed $moduleinstance, bool $join) {
        global $USER;

        try {
            $result = $bridge->create_group($moduleinstance);
            $group_id = $result->group_id;
            if (!$group_id) {
                Template::error(get_string('message_error_create_group', 'mod_gitlab'));
                return false;
            }

            if ($join) {
                $bridge->join_group($group_id, $USER->id, $moduleinstance);
            }

            redirect(
                new url('/mod/gitlab/view.php', ['g' => $moduleinstance->id]),
                get_string('message_created_group', 'mod_gitlab'),
            );
        } catch (RuntimeException $e) {
            Template::error(get_string('message_error_create_repository', 'mod_gitlab', ['message' => $e->getMessage()]));
            return false;
        }

        return true;
    }
}