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
 * The main mod_gitlab configuration form.
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_gitlab\http\Gitlab;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_gitlab_mod_form extends moodleform_mod {
    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        // TODO check null + verify validity (ping)
        $token = $this->getGitLabToken();
        $client = new Gitlab($token);

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('form_name', 'mod_gitlab'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'form_name', 'mod_gitlab');
        
        $this->standard_intro_elements();

        // TODO only owned groups
        $mform->addElement(
            'select',
            'parent_group',
            get_string('form_parent_group', 'mod_gitlab'),
            array_column($client->group()->list(), 'name', 'id'),
        );
        $mform->addRule('parent_group', null, 'required', null, 'client');
        $mform->addHelpButton('parent_group', 'form_parent_group', 'mod_gitlab');

        $mform->addElement(
            'text',
            'group_size',
            get_string('form_group_size', 'mod_gitlab'),
            [
                'type' => 'number',
                'min' => 1,
                'step' => 1
            ],
        );
        $mform->setType('group_size', PARAM_INT);
        $mform->addRule('group_size', null, 'numeric', null, 'client');
        $mform->addRule('group_size', null, 'required', null, 'client');
        $mform->addHelpButton('group_size', 'form_group_size', 'mod_gitlab');

        $this->repeat_elements(
            [$mform->createElement('text', 'reviewer', get_string('form_reviewer', 'mod_gitlab'))],
            0,
            [
                'reviewer' => ['type' => PARAM_TEXT],
            ],
            'reviewer_repeats',
            'group_name_add_fields',
            1,
            get_string('form_reviewer_repeats_add', 'mod_gitlab'),
            true,
            get_string('form_reviewer_repeats_delete', 'mod_gitlab'),
        );
        $mform->setType('reviewer', PARAM_TEXT);
        $mform->addRule('reviewer', null, 'required', null, 'client');
        $mform->addHelpButton('reviewer', 'form_reviewer', 'mod_gitlab');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    private function getGitLabToken(): string|null {
        $courseid = $this->get_course()->id;
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $customfields = $handler->get_instance_data($courseid);

        foreach ($customfields as $fielddata) {
            if ($fielddata->get_field()->get('shortname') === 'gitlab_token') {
                return $fielddata->get_value();
            }
        }

        return null;
    }
}
