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
use mod_gitlab\local\Bridge;
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
require_capability('mod/gitlab:view', $modulecontext);
$is_teacher = has_capability('mod/gitlab:addinstance', $modulecontext);

$PAGE->set_url('/mod/gitlab/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$token = Helper::get_course_gitlab_token($moduleinstance->course);
$client = new Gitlab($token);
$bridge = new Bridge($client, $token);

// TODO handle perm
switch ($action) {
case 'createrepository':
    try {
        $client->project()->create(
            $moduleinstance->name . "_" . $USER->username . "_" . bin2hex(random_bytes(8)),
            $moduleinstance->group_id
        );
    } catch (RuntimeException $e) {
        error(get_string('message_error_create_repository', 'mod_gitlab', ['message' => $e->getMessage()]));
        return;
    }

    redirect(
        new url('/mod/gitlab/view.php', ['id' => $cm->id]),
        get_string('message_repository_created', 'mod_gitlab'),
    );
    break;
case 'joingroup':
    $group_id = optional_param('group_id', '', PARAM_INT);
    if (!$group_id || !$bridge->join_group($cm->id, $group_id, $USER->id, $moduleinstance)) {
        error(get_string('message_error_join_group', 'mod_gitlab'));
        return;
    }

    redirect(
        new url('/mod/gitlab/view.php', ['id' => $cm->id]),
        get_string('message_joined_group', 'mod_gitlab'),
    );
    break;
case 'creategroup':
    try {
        $result = $bridge->create_group($cm->id, $moduleinstance);
        $group_id = $result->group_id;
        if (!$group_id) {
            error(get_string('message_error_create_group', 'mod_gitlab'));
            return;
        }

        if (!$is_teacher) {
            $bridge->join_group($cm->id, $group_id, $USER->id, $moduleinstance);
        }

        redirect(
            new url('/mod/gitlab/view.php', ['id' => $cm->id]),
            get_string('message_created_group', 'mod_gitlab'),
        );
    } catch (RuntimeException $e) {
        error(get_string('message_error_create_repository', 'mod_gitlab', ['message' => $e->getMessage()]));
        return;
    }
    break;
}

function list_repositories(Gitlab $client, int $group_id, int $module_id) {
    echo html_writer::link(
        new url('/mod/gitlab/view.php', [
            'id' => $module_id,
            'action' => 'createrepository',
        ]),
        get_string('button_create_repository', 'mod_gitlab'),
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
        error(get_string('message_error_list_repositories', 'mod_gitlab', ['message' => $e->getMessage()]));
    }
}

function parse_group_members(stdClass $group) {
    $members = trim($group->members, '{}');
    return $members !== '' ? explode(',', $members) : [];
}

function list_student_groups(int $module_id, int $max_member) {
    global $OUTPUT;

    echo $OUTPUT->render_from_template('mod_gitlab/student_groups', [
        'groups' => array_map(function($group) use ($module_id, $max_member) {
            $members = parse_group_members($group);
        
            $group->member_count = count($members);
            $group->can_join_group = $group->member_count < $max_member;
            $group->join_group_url = (new url('/mod/gitlab/view.php', [
                'id' => $module_id,
                'action' => 'joingroup',
                'group_id' => $group->id,
            ]))->out(false);
            $group->name = get_string('message_group_name', 'mod_gitlab', ['members' => implode(', ', $members)]);
            return $group;
        }, Group::get_groups($module_id)),
        'create_group_url' => (new url('/mod/gitlab/view.php', [
            'id' => $module_id,
            'action' => 'creategroup',
        ]))->out(false),
        'max_member' => $max_member,
    ]);
}

function list_teacher_groups(Gitlab $client, int $module_id, int $max_member, int $due_date, int $context_id) {
    global $OUTPUT;
    
    echo $OUTPUT->render_from_template('mod_gitlab/teacher_groups', [
        'groups' => array_map(function($group) use ($client, $module_id, $due_date) {
            $group->members = parse_group_members($group);
            $group->member_count = count($group->members);
            $group->name = get_string('message_group_name', 'mod_gitlab', ['members' => implode(', ', $group->members)]);
            
            try {
                $repository = $client->project()->get($group->repository_id);
            } catch (RuntimeException $e) {
                error(get_string('message_error_get_repository', 'mod_gitlab', ['message' => $e->getMessage()]));
                return $group;
            }
            
            $group->delete_url = (new url('/mod/gitlab/view.php', [
                'id' => $module_id,
                'action' => 'deletegroup',
                'group_id' => $group->id,
            ]))->out(false);
            $group->repository_url = $repository->web_url;
            $group->download_latest_url = $client->project()->archive($group->repository_id);
            $group->ssh_url = $repository->ssh_url_to_repo;
            $group->https_url = $repository->http_url_to_repo;

            $last_in_time_commit = $client->commit()->get_last_until($group->repository_id, $due_date);
            if ($last_in_time_commit == null) {
                // TODO improve
                return $group;
            }
            $group->checkout_due_date = 'git checkout ' . $last_in_time_commit->id;
            $group->download_due_date_url = $client->project()->archive($group->repository_id, '.zip', [
                'sha' => $last_in_time_commit->id,
            ]);

            $last_commit = $client->commit()->get_last($group->repository_id);
            if ($last_commit == null) {
                // TODO improve
                return $group;
            }
            $time = strtotime($last_commit->committed_date);
            $group->delay = format_time($time - $due_date);
            $group->is_delayed = ($time - $due_date) > 0;
            
            // TODO
            $group->feedback_url = 'TODO_feedback';
            $group->test_url = 'TODO_test';
            $group->is_graded = false;
            $group->last_test_pass = true;
            
            return $group;
        }, Group::get_groups($module_id)),
        'max_member' => $max_member,
        'context_id' => $context_id,
        'create_group_url' => (new url('/mod/gitlab/view.php', [
            'id' => $module_id,
            'action' => 'creategroup',
        ]))->out(false),
    ]);
}

function template(Gitlab $client, int $template_id, int $due_date, array $reviewer_ids) {
    global $OUTPUT, $DB;
    
    try {
        $template = $client->project()->get($template_id);
    } catch (RuntimeException $e) {
        error(get_string('message_error_get_template', 'mod_gitlab', ['message' => $e->getMessage()]));
        return;
    }

    list($in_sql, $params) = $DB->get_in_or_equal($reviewer_ids, SQL_PARAMS_NAMED, '', true, NULL);
    $reviewers = $DB->get_fieldset_sql("
        SELECT u.username
        FROM {user} u
        WHERE u.id $in_sql
    ", $params);

    echo $OUTPUT->render_from_template('mod_gitlab/teacher_template', [
        'name' => $template->name,
        'repository_url' => $template->web_url,
        'reviewers' => $reviewers,
        'due_date' => userdate($due_date, get_string('strftimedaydatetime', 'langconfig')),
        // TODO
        'test_url' => 'TODO_test',
        'solution_url' => 'TODO_solution',
        'instruction_url' => 'TODO_instruction',
    ]);
}

function student_group(Gitlab $client, int $module_id, int $user_id, int $max_member, int $due_date) {
    global $OUTPUT;

    $group = Group::group_with_members($module_id, $user_id);
    $members = parse_group_members($group);

    try {
        $repository = $client->project()->get($group->repository_id);
    } catch (RuntimeException $e) {
        error(get_string('message_error_get_repository', 'mod_gitlab', ['message' => $e->getMessage()]));
        return;
    }

    $feedback_url = 'TODO_feedback';
    $test_url = 'TODO_test';

    $is_graded = false;
    $last_test_pass = true;
    $last_commit = $client->commit()->get_last($group->repository_id);

    $time = strtotime($last_commit->committed_date ?? '') ?: 0;
    $delay = format_time($time - $due_date);
    $is_delayed = ($time - $due_date) > 0;

    $has_ended = (time() - $due_date) > 0;
    $has_solution = $has_ended && true;
    $solution_url = 'TODO_solution';

    echo $OUTPUT->render_from_template('mod_gitlab/student_group', [
        'id' => $group->id,
        'name' => get_string('message_group_name', 'mod_gitlab', ['members' => implode(', ', $members)]),
        'max_member' => $max_member,
        'member_count' => count($members),
        'members' => $members,
        'due_date' => userdate($due_date, get_string('strftimedaydatetime', 'langconfig')),
        'repository_url' => $repository->web_url,
        'is_graded' => $is_graded,
        'feedback_url' => $feedback_url,
        'last_test_pass' => $last_test_pass,
        'test_url' => $test_url,
        'is_delayed' => $is_delayed,
        'delay' => $delay,
        'download_url' => $client->project()->archive($group->repository_id),
        'https_url' => $repository->http_url_to_repo,
        'ssh_url' => $repository->ssh_url_to_repo,
        'has_solution' => $has_solution,
        'solution_url' => $solution_url,
    ]);
}

echo $OUTPUT->header();

if ($is_teacher) {
    list_repositories($client, $moduleinstance->group_id, $cm->id);
    template($client, $moduleinstance->template_id, $moduleinstance->due_date, json_decode($moduleinstance->reviewers, true) ?: []);
    list_teacher_groups($client, $cm->id, $moduleinstance->group_size, $moduleinstance->due_date, $modulecontext->id);
} else {
    $has_group = Group::has_group($cm->id, $USER->id);

    if ($has_group) {
        student_group($client, $cm->id, $USER->id, $moduleinstance->group_size, $moduleinstance->due_date);
    } else {
        list_student_groups($cm->id, $moduleinstance->group_size);
    }
}

echo $OUTPUT->footer();
