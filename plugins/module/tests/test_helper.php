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
 * Helper test class
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class TestHelper {
    public static function mock_module(): stdClass {
        $module = new stdClass();
        $module->course = 1;
        $module->name = '';
        $module->intro = '';
        $module->introformat = FORMAT_HTML;
        $module->group_id = 1;
        $module->parent_group = 1;
        $module->group_size = 1;
        $module->due_date = time();
        $module->reviewers = json_encode([]);
        $module->template_id = 1;
        $module->webhook_secret = '';

        return $module;
    }
}