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

$string['button_create_repository'] = 'Create GitLab Repository';
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
