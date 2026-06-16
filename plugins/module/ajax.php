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
 * Ajax
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $PAGE, $DB;

define('AJAX_SCRIPT', true);

require('../../config.php');

$context_id = required_param('id', PARAM_INT);
$group_id = required_param('groupid', PARAM_INT);
$action = required_param('action', PARAM_ALPHANUMEXT);

$PAGE->set_url(new moodle_url('/gitlab/ajax.php', array('id'=>$group_id, 'action'=>$action)));

$module_id = $DB->get_record('gitlab_groups', ['id' => $group_id], '*', MUST_EXIST)->module_id;

$moduleinstance = $DB->get_record('gitlab', ['id' => $module_id], '*', MUST_EXIST);
$modulecontext = context_module::instance($module_id);

require_capability('mod/gitlab:addinstance', $modulecontext);
require_sesskey();

switch ($action) {
    case 'test':
        $outcome = new stdClass();
        $outcome->success = true;
        $outcome->response = new stdClass();
        $outcome->error = '';
        $outcome->count = 0;
        echo json_encode($outcome);
        break;
    default:
        throw new moodle_exception('unknownajaxaction', 'mod_gitlab');
}
