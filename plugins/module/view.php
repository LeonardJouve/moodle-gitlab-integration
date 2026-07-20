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
 * Prints an instance of mod_gitlab.
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_gitlab\http\Gitlab;
use mod_gitlab\local\Action;
use mod_gitlab\local\bridge\Bridge;
use mod_gitlab\local\Helper;
use mod_gitlab\local\bridge\Group;
use mod_gitlab\local\bridge\Resources;
use mod_gitlab\local\Template;

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $DB, $PAGE, $USER, $OUTPUT;

// Course module id.
$id = optional_param('id', 0, PARAM_INT);
// Activity instance id.
$g = optional_param('g', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

if ($id) {
    $cm = get_coursemodule_from_id('gitlab', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('gitlab', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('gitlab', ['id' => $g], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('gitlab', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

/** @var \core\context $modulecontext */
$modulecontext = context_module::instance($cm->id);
require_capability('mod/gitlab:view', $modulecontext);

$reviewers = json_decode($moduleinstance->reviewers, true) ?: [];
$is_reviewer = in_array($USER->id, $reviewers);
$is_teacher = has_capability('mod/gitlab:addinstance', $modulecontext);
$is_manager = $is_reviewer || $is_teacher;

$PAGE->set_url('/mod/gitlab/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$token = Helper::get_course_gitlab_token($moduleinstance->course);
$client = new Gitlab($token);
$bridge = new Bridge($client);
$resources = new Resources($client);

$ok = true;

// TODO handle perm
switch ($action) {
case 'joingroup':
    $ok = Action::join_group($bridge, $moduleinstance);
    break;
case 'creategroup':
    $ok = Action::create_group($bridge, $moduleinstance, !$is_manager);
    break;
}

if (!$ok) {
    exit;
}

echo $OUTPUT->header();

$username = Helper::get_user_gitlab_username($USER->id);

if ($username == null) {
    Template::error(get_string('no_gitlab_username_err', 'mod_gitlab'));
} else if ($is_manager) {
    Template::template($client, $resources, $moduleinstance->id, $moduleinstance->template_id, $moduleinstance->due_date, $reviewers);
    Template::list_teacher_groups($client, $resources, $moduleinstance->id, $moduleinstance->template_id, $moduleinstance->group_size, $moduleinstance->due_date, $modulecontext->id);
} else {
    $has_group = Group::has_group($moduleinstance->id, $USER->id);

    if ($has_group) {
        Template::student_group($client, $resources, $moduleinstance->id, $USER->id, $moduleinstance->group_size, $moduleinstance->due_date);
    } else {
        Template::list_student_groups($moduleinstance->id, $moduleinstance->group_size);
    }
}

echo $OUTPUT->footer();
