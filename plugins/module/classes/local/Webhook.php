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

use core\encryption;
use core\url;
use core_user;
use mod_gitlab\http\Gitlab;
use stdClass;

class Webhook {
    private static int $WEBHOOK_VALID_TIME_WINDOW = 5 * 60; 

    public static function get_module_key(int $module_id): ?string {
        global $DB;

        $encrypted_secret = $DB->get_field('gitlab', 'webhook_secret', ['id' => $module_id], IGNORE_MISSING);
        if (!$encrypted_secret) {
            return null;
        }

        $secret = encryption::decrypt($encrypted_secret);
        $key = base64_decode($secret, true);
        if ($key === false || strlen($key) !== 32) {
            return null;
        }

        return $key;
    }

    public static function generate_key() {
        return base64_encode(random_bytes(32));
    }

    public static function get_signature(string $webhook_id, int $webhook_timestamp, string $body, string $key): string {
        $data = $webhook_id . '.' . $webhook_timestamp . '.' . $body;
        $digest = hash_hmac('sha256', $data, $key, true);

        return 'v1,' . base64_encode($digest);
    }

    public static function is_valid(string $webhook_id, int $webhook_timestamp, string $webhook_signature, string $body, string $key): bool {
        $expected = Webhook::get_signature($webhook_id, $webhook_timestamp, $body, $key);

        $valid = false;
        $signatures = explode(' ', $webhook_signature);
        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            return false;
        }

        if (abs(time() - $webhook_timestamp) > Webhook::$WEBHOOK_VALID_TIME_WINDOW) {
            return false;
        }

        return true;
    }

    public static function get_content(string $body): ?stdClass {
        return json_decode($body);
    }

    public static function handle_push_event(stdClass $event): bool {
        global $DB;

        $moduleinstance = $DB->get_record('gitlab', ['template_id' => $event->project_id], '*');

        $token = Helper::get_course_gitlab_token($moduleinstance->course);
        if ($token == null) {
            return false;
        }

        $client = new Gitlab($token);
        $resources = new Resources($client);

        $groups = Group::get_groups($moduleinstance->id);
        foreach ($groups as $group) {
            $update_merge_request = $resources->get_update_group_repository_merge_request($group->repository_id);

            // create merge request only if it does not already exists
            if ($update_merge_request == null) {
                $resources->create_update_group_repository_merge_request($moduleinstance->template_id, $group->repository_id);
            }
        }

        return true;
    }

    public static function handle_issue_event(stdClass $event): bool {
        global $DB;

        $moduleinstance = $DB->get_record('gitlab', ['template_id' => $event->project->id], '*');

        $token = Helper::get_course_gitlab_token($moduleinstance->course);
        if ($token == null) {
            return false;
        }

        $client = new Gitlab($token);
        $resources = new Resources($client);

        $template_issue = $resources->get_instructions_issue($moduleinstance->template_id);
        if ($template_issue == null || $template_issue->id != $event->object_attributes->id) {
            return false;
        }

        $groups = Group::get_groups($moduleinstance->id);
        foreach ($groups as $group) {
            $issue = $resources->get_instructions_issue($group->repository_id);
            if ($issue == null) {
                continue;
            }

            $resources->update_instructions_issue(
                $group->repository_id,
                $issue->iid,
                $event->object_attributes->description,
            );
        }

        return true;
    }

    public static function handle_merge_request_event(stdClass $event): bool {
        global $DB;

        $group = $DB->get_record('gitlab_groups', ['repository_id' => $event->project->id], '*');
        $moduleinstance = $DB->get_record('gitlab', ['id' => $group->module_id], '*');

        $token = Helper::get_course_gitlab_token($moduleinstance->course);
        if ($token == null) {
            return false;
        }

        $client = new Gitlab($token);
        $resources = new Resources($client);

        $merge_request = $resources->get_student_submission_merge_request($group->repository_id);
        if ($merge_request->id !== $event->object_attributes->id || $event->object_attributes->action !== 'close') {
            return false;
        }

        $members = $DB->get_fieldset('gitlab_group_members', 'user_id', ['group_id' => $group->id]);
        $course = $DB->get_field('course', 'fullname', ['id' => $moduleinstance->course], MUST_EXIST);

        $subject = get_string('notification_graded_title', 'mod_gitlab', [
            'name' => $moduleinstance->name,
        ]);
        $content = get_string('notification_graded_description', 'mod_gitlab', [
            'name' => $moduleinstance->name,
            'course' => $course,
        ]);

        foreach ($members as $user_id) {
            $user = $DB->get_record('user', ['id' => $user_id], '*');
    
            $message = new \core\message\message();
            $message->component = 'mod_gitlab';
            $message->name = 'graded';
            $message->userfrom = core_user::get_noreply_user();
            $message->userto = $user;
            $message->subject = $subject;
            $message->fullmessage = $content;
            $message->fullmessageformat = FORMAT_HTML;
            $message->fullmessagehtml = $content;
            $message->smallmessage = $content;
            $message->notification = 1;
            $message->contexturl = (new url('/mod/gitlab/view.php', ['g' => $moduleinstance->id]))->out(false);
            $message->contexturlname = get_string('notification_module_view', 'mod_gitlab');
    
            message_send($message);
        }

        return true;
    }
}
