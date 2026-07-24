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

use mod_gitlab\local\Webhook;

require_once(__DIR__ . '/../../config.php');

$HTTP_BAD_REQUEST = 400;
$HTTP_METHOD_NOT_ALLOWED = 405;
$HTTP_FORBIDDEN = 403;
$HTTP_OK = 200;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code($HTTP_METHOD_NOT_ALLOWED);
    exit;
}

global $DB;

$module_id = $_SERVER['HTTP_MODULE_ID'] ?? '';
$webhook_id = $_SERVER['HTTP_WEBHOOK_ID'] ?? '';
$webhook_timestamp = $_SERVER['HTTP_WEBHOOK_TIMESTAMP'] ?? '';
$webhook_signature = $_SERVER['HTTP_WEBHOOK_SIGNATURE'] ?? '';
$body = file_get_contents('php://input');

if (!$module_id || !$webhook_id || !$webhook_timestamp || !$webhook_signature || !$body) {
    http_response_code($HTTP_BAD_REQUEST);
    exit;
}

global $PAGE;

$course_id = $DB->get_field('gitlab', 'course', ['id' => $module_id], MUST_EXIST);
$cm = get_coursemodule_from_instance('gitlab', $module_id, $course_id, false, MUST_EXIST);
$modulecontext = context_module::instance($cm->id);
$PAGE->set_context($modulecontext);

$key = Webhook::get_module_key($module_id);
if ($key == null) {
    http_response_code($HTTP_BAD_REQUEST);
    exit;
}

$is_valid = Webhook::is_valid($webhook_id, (int) $webhook_timestamp, $webhook_signature, $body, $key);
if (!$is_valid) {
    http_response_code($HTTP_FORBIDDEN);
    exit;
}

$content = Webhook::get_content($body);
if ($content == null) {
    http_response_code($HTTP_BAD_REQUEST);
    exit;
}

switch ($content->object_kind) {
case 'push':
    Webhook::handle_push_event($content);
    break;
case 'issue':
    Webhook::handle_issue_event($content);
    break;
case 'merge_request':
    Webhook::handle_merge_request_event($content);
    break;
}

http_response_code($HTTP_OK);
echo 'ok';