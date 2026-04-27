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
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This function adds Gitlab token and group affectation plugin UI into the course edit form.
 *
 * @param MoodleQuickForm $mform
 * @param object $course
 * @return void
 */
function local_gitlab_extend_course_edit_form(MoodleQuickForm $mform, $course) {
    $mform->addElement('text', 'mytextfield', 'My Text Field');
    $mform->setType('mytextfield', PARAM_TEXT);

    $mform->addElement('select', 'mydropdown', 'My Dropdown', []);
}