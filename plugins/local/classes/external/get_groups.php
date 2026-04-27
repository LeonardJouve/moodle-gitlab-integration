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
 * Plugin strings are defined here.
 *
 * @package     local_gitlab
 * @category    string
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gitlab\external;

defined('MOODLE_INTERNAL') || die();

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;

class get_groups extends external_api {
    public static function execute_parameters() {
        return new external_function_parameters([
            'token' => new external_value(PARAM_TEXT, 'Token')
        ]);
    }

    public static function execute($token) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'token' => $token
        ]);

        // TODO: replace with real API call using token
        return [
            ['value' => 'grp1', 'label' => 'Group 1'],
            ['value' => 'grp2', 'label' => 'Group 2'],
        ];
    }

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'value' => new external_value(PARAM_TEXT, 'value'),
                'label' => new external_value(PARAM_TEXT, 'label')
            ])
        );
    }
}