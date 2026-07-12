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

namespace mod_gitlab\local\form;

use html_writer;
use moodleform;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/formslib.php');

class mod_gitlab_confirm_delete_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $name = $this->_customdata->name;

        $mform->addElement(
            'static',
            'confirmation',
            '',
            html_writer::div(
                get_string('modal_delete_group_help', 'mod_gitlab', ['name' => $name]),
                'alert alert-warning'
            ),
        );

        $mform->addElement(
            'text',
            'confirmationname',
            get_string('modal_delete_group_field', 'mod_gitlab')
        );
        $mform->setType('confirmationname', PARAM_TEXT);
    }

    /**
     * Validate the submitted form data.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $expected = $this->_customdata->name;

        if ($data['confirmationname'] !== $expected) {
            $errors['confirmationname'] = get_string('modal_delete_group_mismatch', 'mod_gitlab');
        }

        return $errors;
    }
}
