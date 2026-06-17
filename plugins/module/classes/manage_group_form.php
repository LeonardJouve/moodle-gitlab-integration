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

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/formslib.php');

class mod_gitlab_manage_group_form extends moodleform {
    public function definition() {
        global $DB, $PAGE;
    
        $mform = $this->_form;
        $context = $this->_customdata->context;
        $coursecontext = $context->get_course_context();

        $users = $DB->get_records_sql("
            SELECT DISTINCT u.id, u.firstname, u.lastname
            FROM {user} u
            JOIN {gitlab_group_members} gm ON gm.user_id = u.id
            WHERE gm.group_id = :group_id
        ", [
            'group_id' => $this->_customdata->groupid,
        ]);

        $existants = [];
        foreach ($users as $user) {
            $existants[$user->id] = $PAGE->get_renderer('core')->render_from_template('mod_gitlab/form_user_selector', $user);
        }
        $options = array(
            'ajax' => 'mod_gitlab/form_user_selector',
            'multiple' => true,
            'courseid' => $coursecontext->instanceid,
            'groupid' => $this->_customdata->groupid,
        );
        $mform->addElement('autocomplete', 'userlist', 'TODO', $existants, $options);
        $mform->setDefault('userlist', array_keys($existants));
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
        return [];
    }
}
