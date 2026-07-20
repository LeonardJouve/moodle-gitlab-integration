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

namespace mod_gitlab\local;

use core\url;
use html_writer;
use mod_gitlab\http\Gitlab;
use mod_gitlab\http\RuntimeException;
use mod_gitlab\local\bridge\Group;
use mod_gitlab\local\bridge\Resources;
use stdClass;

class Template {
    private static function get_group_name(array $members) {
        return count($members) > 0 ?
            get_string('message_group_name', 'mod_gitlab', ['members' => implode(', ', $members)]) :
            get_string('message_empty_group_name', 'mod_gitlab');
    }

    public static function error(string $message) {
        echo html_writer::div(
            $message,
            'alert alert-danger'
        );
    }

    public static function student_group(Gitlab $client, Resources $resources, int $instance_id, int $user_id, int $max_member, int $due_date) {
        global $OUTPUT;
        
        $group = Group::group_with_members($instance_id, $user_id);
        
        $data = new stdClass();
        $data->members = $group->members;
        $data->id = $group->id;
        $data->name = Template::get_group_name($group->members);
        $data->max_member = $max_member;
        $data->member_count = count($group->members);
        $data->due_date = userdate($due_date, get_string('strftimedaydatetime', 'langconfig'));

        try {
            $repository = $client->project()->get($group->repository_id);
        } catch (RuntimeException $e) {
            Template::error(get_string('message_error_get_repository', 'mod_gitlab', ['message' => $e->getMessage()]));
            return;
        }

        $data->repository_url = $repository->web_url;
        $data->download_url = $client->project()->archive($group->repository_id);
        $data->https_url = $repository->http_url_to_repo;
        $data->ssh_url = $repository->ssh_url_to_repo;

        $data->is_graded = false;
        $submission_merge_request = $resources->get_student_submission_merge_request($repository->id);
        if ($submission_merge_request != null) {
            $data->is_graded = $submission_merge_request->state == 'closed';
            $data->feedback_url = $submission_merge_request->web_url;
        }

        $data->last_test_pass = false;
        $last_test_result = $resources->get_latest_test_result($repository->id);
        $data->has_test_result = $last_test_result != null;
        if ($data->has_test_result) {
            $data->test_url = $last_test_result->web_url;
            $data->last_test_pass = $last_test_result->status == 'success';
        }

        $last_commit = $client->commit()->get_last($group->repository_id);

        $time = strtotime($last_commit->committed_date ?? '') ?: 0;
        $data->is_delayed = ($time - $due_date) > 0;
        if ($data->is_delayed) {
            $data->delay = format_time($time - $due_date);
        }

        $data->has_solution = (time() - $due_date) > 0;

        $solution_merge_request = $resources->get_solution_merge_request($repository->id);
        if ($solution_merge_request != null) {
            $data->solution_url = $solution_merge_request->web_url;
        }

        echo $OUTPUT->render_from_template('mod_gitlab/student_group', $data);
    }

    public static function template(Gitlab $client, Resources $resources, int $module_id, int $template_id, int $due_date, array $reviewer_ids) {
        global $OUTPUT, $DB;

        $data = new stdClass();
        $data->module_id = $module_id;
        $data->due_date = userdate($due_date, get_string('strftimedaydatetime', 'langconfig'));
        
        list($in_sql, $params) = $DB->get_in_or_equal($reviewer_ids, SQL_PARAMS_NAMED, '', true, NULL);
        $data->reviewers = $DB->get_fieldset_sql("
            SELECT u.username
            FROM {user} u
            WHERE u.id $in_sql
        ", $params);

        try {
            $template = $client->project()->get($template_id);
        } catch (RuntimeException $e) {
            Template::error(get_string('message_error_get_template', 'mod_gitlab', ['message' => $e->getMessage()]));
            return;
        }

        $data->name = $template->name;
        $data->repository_url = $template->web_url;
        $data->download_url = $client->project()->archive($template_id);
        $data->https_url = $template->http_url_to_repo;
        $data->ssh_url = $template->ssh_url_to_repo;

        $solution_branch = $resources->get_solution_branch($template_id);
        if ($solution_branch != null) {
            $data->solution_url = $solution_branch->web_url;
        }
        
        $instruction_issue = $resources->get_instructions_issue($template_id);
        if ($instruction_issue != null) {
            $data->instruction_url = $instruction_issue->web_url;
        }

        echo $OUTPUT->render_from_template('mod_gitlab/teacher_template', $data);
    }

    public static function list_teacher_groups(Gitlab $client, Resources $resources, int $module_id, int $template_id, int $max_member, int $due_date, int $context_id) {
        global $OUTPUT;

        $data = new stdClass();
        $data->max_member = $max_member;
        $data->context_id = $context_id;
        $data->create_group_url = (new url('/mod/gitlab/view.php', [
            'g' => $module_id,
            'action' => 'creategroup',
        ]))->out(false);
        $data->groups = array_map(function($group) use ($client, $resources, $module_id, $template_id, $due_date) {
            $group->member_count = count($group->members);
            $group->name = Template::get_group_name($group->members);
            
            try {
                $repository = $client->project()->get($group->repository_id);
            } catch (RuntimeException $e) {
                Template::error(get_string('message_error_get_repository', 'mod_gitlab', ['message' => $e->getMessage()]));
                return $group;
            }
            
            $group->delete_url = (new url('/mod/gitlab/view.php', [
                'g' => $module_id,
                'action' => 'deletegroup',
                'group_id' => $group->id,
            ]))->out(false);
            $group->repository_url = $repository->web_url;
            $group->download_latest_url = $client->project()->archive($group->repository_id);
            $group->ssh_url = $repository->ssh_url_to_repo;
            $group->https_url = $repository->http_url_to_repo;

            $last_in_time_commit = $client->commit()->get_last_until($group->repository_id, $due_date);
            if ($last_in_time_commit != null) {
                $group->checkout_due_date = 'git checkout ' . $last_in_time_commit->id;
                $group->download_due_date_url = $client->project()->archive($group->repository_id, '.zip', [
                    'sha' => $last_in_time_commit->id,
                ]);
            }

            $last_commit = $client->commit()->get_last($group->repository_id);
            if ($last_commit != null) {
                $time = strtotime($last_commit->committed_date);
                $group->is_delayed = ($time - $due_date) > 0;
                if ($group->is_delayed) {
                    $group->delay = format_time($time - $due_date);
                }
            }
            
            $submission_merge_request = $resources->get_student_submission_merge_request($group->repository_id);
            if ($submission_merge_request != null) {
                $group->feedback_url = $submission_merge_request->web_url;
                $group->is_graded = $submission_merge_request->state == 'closed';
            }

            $last_test_result = $resources->get_latest_test_result($group->repository_id);

            $group->has_test_result = $last_test_result != null;
            if ($group->has_test_result) {
                $group->test_url = $last_test_result->web_url;
                $group->last_test_pass = $last_test_result->status == 'success';
            }
            
            $submission_merge_request = $resources->get_teacher_submission_merge_request($template_id, $group->id);
            $group->has_submission_merge_request = $submission_merge_request != null;
            if ($group->has_submission_merge_request) {
                $mr_id = $submission_merge_request->iid;
                $group->fetch_merge_request = "git fetch origin merge-requests/$mr_id/head:mr-$mr_id";
                $group->checkout_merge_request = "git checkout mr-$mr_id";
            }

            return $group;
        }, Group::get_groups($module_id));
        
        echo $OUTPUT->render_from_template('mod_gitlab/teacher_groups', $data);
    }

    public static function list_student_groups(int $module_id, int $max_member) {
        global $OUTPUT;

        $data = new stdClass();
        $data->create_group_url = (new url('/mod/gitlab/view.php', [
            'g' => $module_id,
            'action' => 'creategroup',
        ]))->out(false);
        $data->max_member = $max_member;
        $data->groups = array_map(function($group) use ($module_id, $max_member) {
            $group->member_count = count($group->members);
            $group->can_join_group = $group->member_count < $max_member;
            $group->join_group_url = (new url('/mod/gitlab/view.php', [
                'g' => $module_id,
                'action' => 'joingroup',
                'group_id' => $group->id,
            ]))->out(false);
            $group->name = Template::get_group_name($group->members);

            return $group;
        }, Group::get_groups($module_id));

        echo $OUTPUT->render_from_template('mod_gitlab/student_groups', $data);
    }
}
