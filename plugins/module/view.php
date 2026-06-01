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

use core\output\html_writer;
use core\url;
use mod_gitlab\http\Gitlab;
use mod_gitlab\http\RuntimeException;
use mod_gitlab\local\Helper;
use mod_gitlab\local\Group;

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

function error(string $message) {
    echo html_writer::div(
        $message,
        'alert alert-danger'
    );
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/gitlab/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$token = Helper::get_course_gitlab_token($moduleinstance->course);
$client = new Gitlab($token);

switch ($action) {
case 'createrepository':
    try {
        $client->project()->create(
            $moduleinstance->name . "_" . $USER->username . "_" . bin2hex(random_bytes(8)),
            $moduleinstance->group_id
        );
    } catch (RuntimeException $e) {
        error(sprintf('failed to create repository: %s', $e->getMessage()));
        return;
    }

    redirect(
        new url('/mod/gitlab/view.php', ['id' => $cm->id]),
        'Repository created!'
    );
    break;
case 'joingroup':
    if (!Group::join_group($cm->id, 1, $USER->id)) {
        error('unable to join group');
        return;
    }

    redirect(
        new url('/mod/gitlab/view.php', ['id' => $cm->id]),
        'Joined group'
    );
    break;
case 'leavegroup':
    if (!Group::leave_group($cm->id, $USER->id)) {
        error('unable to leave group');
        return;
    }

    redirect(
        new url('/mod/gitlab/view.php', ['id' => $cm->id]),
        'Left group'
    );
    break;
case 'creategroup':
    $group = Group::create_group($cm->id, "test", $USER->id);
    if (!$group) {
        error('unable to create group');
        return;
    }
    Group::join_group($cm->id, $group, $USER->id);

    redirect(
        new url('/mod/gitlab/view.php', ['id' => $cm->id]),
        'Created group'
    );
    break;
}

function list_repositories(Gitlab $client, int $group_id, int $module_id) {
    echo html_writer::link(
        new url('/mod/gitlab/view.php', [
            'id' => $module_id,
            'action' => 'createrepository',
        ]),
        'Create GitLab Repository',
        ['class' => 'btn btn-primary']
    );    

    try {
        $repositories = $client->group()->projects($group_id);

        echo html_writer::start_tag('ul');

        foreach ($repositories as $repository) {
            echo html_writer::tag(
                'li',
                html_writer::link(
                    $repository->web_url,
                    format_string($repository->name),
                    ['target' => '_blank']
                )
            );
        }

        echo html_writer::end_tag('ul');
    } catch (RuntimeException $e) {
        echo html_writer::div(
            sprintf('failed to list repositories: %s', $e->getMessage()),
            'alert alert-danger'
        );
    }
}

function list_groups(int $module_id) {
    global $USER, $OUTPUT;

    echo $OUTPUT->render_from_template('mod_gitlab/groups', [
        'groups' => array_map(function($group) use ($module_id) {
            $members = trim($group->members, '{}');
            $members = $members !== '' ? explode(',', $members) : [];
        
            $group->can_join_group = true; // TODO
            $group->join_group_url = new url('/mod/gitlab/view.php', [
                'id' => $module_id,
                'action' => 'joingroup',
                'group_id' => $group->id,
            ]);
            $group->name = 'Group of ' . implode(', ', $members);
            $group->member_count = count($members);
            return $group;
        }, Group::get_groups($module_id)),
        'has_group' => Group::has_group($module_id, $USER->id),
        'leave_group_url' => new url('/mod/gitlab/view.php', [
            'id' => $module_id,
            'action' => 'leavegroup',
        ]),
        'create_group_url' => new url('/mod/gitlab/view.php', [
            'id' => $module_id,
            'action' => 'creategroup',
        ]),
        'max_member' => 2, // TODO
    ]);
}

echo $OUTPUT->header();

list_repositories($client, $moduleinstance->group_id, $cm->id);
list_groups($cm->id);

echo $OUTPUT->footer();
