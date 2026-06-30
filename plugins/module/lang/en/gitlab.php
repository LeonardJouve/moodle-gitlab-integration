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
 * Plugin strings are defined here.
 *
 * @package     mod_gitlab
 * @category    string
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['template_student_group_group'] = 'Group';
$string['template_student_group_members'] = 'Members';
$string['template_student_group_repository'] = 'Repository';
$string['template_student_group_open'] = 'Open';
$string['template_student_group_feedbacks'] = 'Feedbacks';
$string['template_student_group_graded'] = 'Graded';
$string['template_student_group_not_graded'] = 'Not Graded';
$string['template_student_group_tests'] = 'Tests';
$string['template_student_group_solution'] = 'Solution';
$string['template_student_group_get_source'] = 'Get source code';
$string['template_student_group_overdue'] = 'Overdue';
$string['template_student_group_on_time'] = 'On time';
$string['template_student_group_clone_ssh'] = 'Clone with SSH';
$string['template_student_group_clone_http'] = 'Clone with HTTPS';
$string['template_student_group_download_source'] = 'Download source code';
$string['template_student_group_leave_group'] = 'Leave group';
$string['template_student_group_leave'] = 'Leave';
$string['template_student_groups_groups'] = 'Groups';
$string['template_student_groups_join'] = 'Join';
$string['template_student_groups_create'] = 'Create';
$string['template_teacher_group_members'] = 'Members';
$string['template_teacher_group_edit'] = 'Edit';
$string['template_teacher_group_repository'] = 'Repository';
$string['template_teacher_group_open'] = 'Open';
$string['template_teacher_group_feedbacks'] = 'Feedbacks';
$string['template_teacher_group_graded'] = 'Graded';
$string['template_teacher_group_not_graded'] = 'Not graded';
$string['template_teacher_group_tests'] = 'Tests';
$string['template_teacher_group_get_source'] = 'Get source code';
$string['template_teacher_group_overdue'] = 'Overdue';
$string['template_teacher_group_on_time'] = 'On time';
$string['template_teacher_group_clone_ssh'] = 'Clone with SSH';
$string['template_teacher_group_clone_http'] = 'Clone with HTTPS';
$string['template_teacher_group_checkout_due_date'] = 'Checkout version at due date';
$string['template_teacher_group_checkout_template'] = 'Checkout from template';
$string['template_teacher_group_download_source'] = 'Download source code';
$string['template_teacher_group_at_due_date'] = 'At due date';
$string['template_teacher_group_latest'] = 'Latest';
$string['template_teacher_group_delete_group'] = 'Delete group';
$string['template_teacher_group_delete'] = 'Delete';
$string['template_teacher_groups_groups'] = 'Groups';
$string['template_teacher_groups_create'] = 'Create';
$string['template_teacher_template_template'] = 'Template';
$string['template_teacher_template_collapse'] = 'Collapse';
$string['template_teacher_template_expand'] = 'Expand';
$string['template_teacher_template_reviewers'] = 'Reviewers';
$string['template_teacher_template_due_date'] = 'Due date';
$string['template_teacher_template_repository'] = 'Repository';
$string['template_teacher_template_open'] = 'Open';
$string['template_teacher_template_instructions'] = 'Instructions';
$string['template_teacher_template_solution'] = 'Solution';
$string['template_teacher_template_get_source'] = 'Get source code';
$string['template_teacher_template_clone_ssh'] = 'Clone with SSH';
$string['template_teacher_template_clone_http'] = 'Clone with HTTPS';
$string['template_teacher_template_download_source'] = 'Download source code';
$string['button_create_repository'] = 'Create GitLab Repository';
$string['no_gitlab_username_err'] = 'Your GitLab username is not configured. Please set it under: Profile → User details → Edit profile → GitLab → GitLab Username.';
$string['messageprovider:submission'] = 'Notification of GitLab submissions';
$string['notification_module_view'] = 'Module view';
$string['notification_submission_title'] = 'Assignment due on {$a->due_date}: {$a->name}';
$string['notification_submission_soon_description'] = 'The assignment {$a->name} in the course {$a->course} is due soon.<br>Due date : {$a->due_date}';
$string['notification_submission_now_description'] = 'The assignment {$a->name} in the course {$a->course} is now closed.';
$string['calendar_due_date_event'] = 'Assignment {$a->name} due';
$string['calendar_due_date_description'] = 'The assignment {$a->name} from the course {$a->course} must be submitted by {$a->due_date}';
$string['form_due_date'] = 'Due date';
$string['form_due_date_help'] = 'Practical work due date and time';
$string['form_group_size'] = 'Group size';
$string['form_group_size_help'] = 'Amount of students per group';
$string['form_invalid_token_err'] = 'Invalid GitLab token. Please check the token and try again.';
$string['form_invalid_parent_group_err'] = 'Invalid GitLab parent group selected.';
$string['form_group_size_negative_err'] = 'Group size must be greater than 0.';
$string['form_invalid_reviewer_err'] = 'GitLab reviewer not found.';
$string['form_name'] = 'Name';
$string['form_name_help'] = 'Name of the module';
$string['form_no_groups_err'] = 'No existing GitLab groups found. Please create one.';
$string['form_no_token_err'] = 'No GitLab token found. Please define a token in the Moodle course settings.';
$string['form_parent_group'] = 'Parent group';
$string['form_parent_group_help'] = 'GitLab parent group containing created resources';
$string['form_reviewer'] = 'GitLab reviewer';
$string['form_reviewer_help'] = 'GitLab username of a reviewer';
$string['form_reviewer_repeats_add'] = 'Add a reviewer';
$string['instructions_issue_help'] = 'Modify this to include instructions.';
$string['submission_merge_request_title'] = 'Submission';
$string['template_submission_merge_request_title'] = '"{$a->name}" submission';
$string['solution_merge_request_title'] = 'Solution';
$string['update_group_repository_merge_request_title'] = 'Update repository';
$string['webhook_name'] = 'Update group repositories webhook';
$string['gitlabfieldset'] = 'gitlab';
$string['gitlabname'] = 'gitlab';
$string['gitlabsettings'] = 'gitlab';
$string['message_repository_created'] = 'Repository created !';
$string['message_joined_group'] = 'Joined group !';
$string['message_left_group'] = 'Left group !';
$string['message_created_group'] = 'Created group !';
$string['message_deleted_group'] = 'Group deleted !';
$string['message_error_delete_group'] = 'unable to delete group';
$string['message_error_create_group'] = 'unable to create group';
$string['message_error_leave_group'] = 'unable to leave group';
$string['message_error_join_group'] = 'unable to join group';
$string['message_error_create_repository'] = 'failed to create repository: {$a->message}';
$string['message_error_get_template'] = 'failed to get template repository: {$a->message}';
$string['message_error_get_repository'] = 'failed to get group repository: {$a->message}';
$string['message_error_download'] = 'failed to download repository code: {$a->message}';
$string['message_error_list_repositories'] = 'failed to list repositories: {$a->message}';
$string['message_group_name'] = 'Group of {$a->members}';
$string['message_empty_group_name'] = 'Empty group';
$string['modal_group_members_title'] = 'Group members';
$string['modal_group_members_update'] = 'Update';
$string['modal_group_members_field'] = 'Members';
$string['modal_leave_group_title'] = 'Leave group';
$string['modal_leave_group_help'] = 'Are you sure you want to leave this group ?';
$string['modal_leave_confirm'] = 'Leave';
$string['modal_delete_group_title'] = 'Delete group';
$string['modal_delete_group_help'] = 'Are you sure you want to delete this group ?';
$string['modulename'] = 'GitLab';
$string['modulenameplural'] = 'gitlab';
$string['pluginadministration'] = 'administration';
$string['pluginname'] = 'gitlab';
$string['token_help'] = 'GitLab token';
$string['token'] = 'Token';
