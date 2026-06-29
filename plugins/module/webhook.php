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

require('../../config.php');

$HTTP_INTERNAL_SERVER_ERROR = 500;
$HTTP_BAD_REQUEST = 400;
$HTTP_METHOD_NOT_ALLOWED = 405;
$HTTP_FORBIDDEN = 403;
$HTTP_OK = 200;
$WEBHOOK_VALID_TIME_WINDOW = 5 * 60;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code($HTTP_METHOD_NOT_ALLOWED);
    exit;
}

// $key = random_bytes(32);
// $secret = 'whsec_' . base64_encode($key);
$secret = 'whsec_AxAOgruB2D4EJL4jrFOnRIJAHPSvt6WJ4fmgFIhOSM0=';
$key = base64_decode(substr($secret, 6), true);
if ($key === false || strlen($key) !== 32) {
    http_response_code($HTTP_INTERNAL_SERVER_ERROR);
    exit;
}

$webhook_id = $_SERVER['HTTP_WEBHOOK_ID'] ?? '';
$webhook_timestamp = $_SERVER['HTTP_WEBHOOK_TIMESTAMP'] ?? '';
$webhook_signature = $_SERVER['HTTP_WEBHOOK_SIGNATURE'] ?? '';

if (!$webhook_id || !$webhook_timestamp || !$webhook_signature) {
    http_response_code($HTTP_BAD_REQUEST);
    exit;
}

if (abs(time() - (int)$webhook_timestamp) > $WEBHOOK_VALID_TIME_WINDOW) {
    http_response_code($HTTP_FORBIDDEN);
    exit;
}

$body = file_get_contents('php://input');
$message = $webhook_id . '.' . $webhook_timestamp . '.' . $body;

$digest = hash_hmac('sha256', $message, $key, true);
$expected = 'v1,' . base64_encode($digest);

$signatures = explode(' ', $webhook_signature);

$valid = false;
foreach ($signatures as $signature) {
    if (hash_equals($expected, $signature)) {
        $valid = true;
        break;
    }
}

if (!$valid) {
    http_response_code($HTTP_FORBIDDEN);
    exit;
}

http_response_code($HTTP_OK);
echo $message;