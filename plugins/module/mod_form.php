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

use mod_gitlab\local\Helper;
use mod_gitlab\http\Gitlab;
use mod_gitlab\http\RuntimeException;

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
        global $CFG, $DB, $PAGE;

        $mform = $this->_form;

        $token = Helper::get_course_gitlab_token($this->get_course()->id);
        if ($token === null) {
            $mform->addElement(
                'html',
                \html_writer::div(
                    get_string('form_no_token_err', 'mod_gitlab'),
                    'alert alert-danger'
                )
            );
            $this->standard_coursemodule_elements();
            
            return;
        }
        
        $groups = [];
        try {
            $client = new Gitlab($token);
            $groups = array_column($client->group()->list(['owned' => true]), 'name', 'id');
        } catch (RuntimeException $e) {
            $mform->addElement(
                'html',
                \html_writer::div(
                    get_string('form_invalid_token_err', 'mod_gitlab'),
                    'alert alert-danger'
                )
            );
            $this->standard_coursemodule_elements();

            return;
        }

        if (count($groups) === 0) {
            $mform->addElement(
                'html',
                \html_writer::div(
                    get_string('form_no_groups_err', 'mod_gitlab'),
                    'alert alert-danger'
                )
            );
            $this->standard_coursemodule_elements();

            return;
        }

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

        $mform->addElement(
            'select',
            'parent_group',
            get_string('form_parent_group', 'mod_gitlab'),
            $groups,
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

        $existants = [];
        if ($this->get_current()->reviewers) {
            $reviewers = json_decode($this->get_current()->reviewers, true);
            list($in_sql, $params) = $DB->get_in_or_equal($reviewers, SQL_PARAMS_NAMED, '', true, NULL);
            $users = $DB->get_records_sql("
                SELECT u.id, u.firstname, u.lastname
                FROM {user} u
                WHERE u.id $in_sql
            ", $params);
            foreach ($users as $user) {
                $existants[$user->id] = $PAGE->get_renderer('core')->render_from_template('mod_gitlab/reviewer_selector', $user);
            }
        }
        $options = array(
            'ajax' => 'mod_gitlab/reviewer_selector',
            'multiple' => true,
            'courseid' => $this->get_course()->id,
        );
        $mform->addElement('autocomplete', 'reviewer', get_string('form_reviewer', 'mod_gitlab'), $existants, $options);
        $mform->addElement(
            'static',
            'test',
            'Reviewer',
            json_encode($existants),
        );
        $mform->addElement(
            'date_time_selector',
            'due_date',
            get_string('form_due_date', 'mod_gitlab')
        );
        $mform->setType('due_date', PARAM_INT);
        $mform->addRule('due_date', null, 'required', null, 'client');
        $mform->setDefault('due_date', time() + 7 * DAYSECS);
        $mform->addHelpButton('due_date', 'form_due_date', 'mod_gitlab');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Parent group
        if (empty($data['parent_group'])) {
            $errors['parent_group'] = get_string('required');
        }
        
        // Group size
        if (empty($data['group_size']) || (int)$data['group_size'] < 1) {
            $errors['group_size'] = get_string('form_group_size_negative_err', 'mod_gitlab');
        }

        // Due date
        if (empty($data['due_date']) || $data['due_date'] < time()) {
            $errors['due_date'] = get_string('form_due_date_past_err', 'mod_gitlab');
        }

        $token = Helper::get_course_gitlab_token($this->get_course()->id);

        // Token
        if ($token === null) {
            $errors['parent_group'] = get_string('form_no_token_err', 'mod_gitlab');

            return $errors;
        }

        $client = new Gitlab($token);

        try {
            // Parent group
            if (!empty($data['parent_group'])) {
                $groups = array_column($client->group()->list(['owned' => true]), 'name', 'id');
                if (!array_key_exists($data['parent_group'], $groups)) {
                    $errors['parent_group'] = get_string('form_invalid_parent_group_err', 'mod_gitlab');
                }
            }
            
            // Reviewers
            if (!empty($data['reviewer'])) {
                // foreach ($data['reviewer'] as $index => $reviewer) {
                // }
            }
        } catch (RuntimeException $e) {
            $errors['parent_group'] = get_string('form_invalid_token_err', 'mod_gitlab');
        }

        return $errors;
    }
}
