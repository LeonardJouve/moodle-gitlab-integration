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
 * Tests for Webhook class
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_gitlab\local;

use advanced_testcase;
use core\encryption;
use mod_gitlab\TestHelper;

/**
 * Unit tests for Webhook class
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class webhook_test extends advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Test generate_key creates a valid base64 encoded 32-byte key
     */
    public function test_generate_key_creates_valid_key(): void {
        $key = Webhook::generate_key();

        // Verify it's valid base64
        $decoded = base64_decode($key, true);
        $this->assertNotFalse($decoded);

        // Verify it's 32 bytes
        $this->assertEquals(32, strlen($decoded));
    }

    /**
     * Test generate_key produces different values each time
     */
    public function test_generate_key_produces_different_values(): void {
        $key1 = Webhook::generate_key();
        $key2 = Webhook::generate_key();

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test get_module_key returns null when module doesn't exist
     */
    public function test_get_module_key_returns_null_when_not_exists(): void {
        $key = Webhook::get_module_key(99999);
        $this->assertNull($key);
    }

    /**
     * Test get_module_key returns null when webhook_secret is empty
     */
    public function test_get_module_key_returns_null_when_empty(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $module = TestHelper::mock_module();
        $module->course = $course->id;
        $module->name = 'Test Module';
        $module->webhook_secret = '';
        $moduleid = $DB->insert_record('gitlab', $module);

        $key = Webhook::get_module_key($moduleid);
        $this->assertNull($key);
    }

    /**
     * Test get_module_key returns decrypted key when it exists
     */
    public function test_get_module_key_returns_decrypted_key(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();

        $secretkey = Webhook::generate_key();

        $module = TestHelper::mock_module();
        $module->course = $course->id;
        $module->name = 'Test Module';
        $module->webhook_secret = encryption::encrypt($secretkey);
        $moduleid = $DB->insert_record('gitlab', $module);

        $key = Webhook::get_module_key($moduleid);

        $this->assertNotNull($key);
        $this->assertEquals(32, strlen($key));
    }

    /**
     * Test get_signature produces valid signature
     */
    public function test_get_signature_produces_valid_signature(): void {
        $webhook_id = 'webhook-123';
        $webhook_timestamp = 1234567890;
        $body = '{"key": "value"}';
        $key = base64_decode(Webhook::generate_key(), true);

        $signature = Webhook::get_signature($webhook_id, $webhook_timestamp, $body, $key);

        // Verify signature starts with v1,
        $this->assertStringStartsWith('v1,', $signature);

        // Verify signature can be base64 decoded
        $parts = explode(',', $signature);
        $this->assertEquals(2, count($parts));
        $this->assertNotFalse(base64_decode($parts[1], true));
    }

    /**
     * Test get_signature produces deterministic output
     */
    public function test_get_signature_deterministic(): void {
        $webhook_id = 'webhook-123';
        $webhook_timestamp = 1234567890;
        $body = '{"key": "value"}';
        $key = base64_decode(Webhook::generate_key(), true);

        $signature1 = Webhook::get_signature($webhook_id, $webhook_timestamp, $body, $key);
        $signature2 = Webhook::get_signature($webhook_id, $webhook_timestamp, $body, $key);

        $this->assertEquals($signature1, $signature2);
    }

    /**
     * Test get_signature produces different signatures for different inputs
     */
    public function test_get_signature_different_for_different_inputs(): void {
        $webhook_id = 'webhook-123';
        $webhook_timestamp = 1234567890;
        $body = '{"key": "value"}';
        $key = base64_decode(Webhook::generate_key(), true);

        $signature1 = Webhook::get_signature($webhook_id, $webhook_timestamp, $body, $key);
        $signature2 = Webhook::get_signature($webhook_id, $webhook_timestamp + 1, $body, $key);
        $signature3 = Webhook::get_signature($webhook_id, $webhook_timestamp, $body . 'x', $key);

        $this->assertNotEquals($signature1, $signature2);
        $this->assertNotEquals($signature1, $signature3);
    }

    /**
     * Test is_valid returns true for valid signature
     */
    public function test_is_valid_returns_true_for_valid_signature(): void {
        $webhook_id = 'webhook-123';
        $webhook_timestamp = time();
        $body = '{"key": "value"}';
        $key = base64_decode(Webhook::generate_key(), true);

        $signature = Webhook::get_signature($webhook_id, $webhook_timestamp, $body, $key);

        $result = Webhook::is_valid($webhook_id, $webhook_timestamp, $signature, $body, $key);
        $this->assertTrue($result);
    }

    /**
     * Test is_valid returns false for invalid signature
     */
    public function test_is_valid_returns_false_for_invalid_signature(): void {
        $webhook_id = 'webhook-123';
        $webhook_timestamp = time();
        $body = '{"key": "value"}';
        $key = base64_decode(Webhook::generate_key(), true);

        $signature = 'v1,invalid_signature_data';

        $result = Webhook::is_valid($webhook_id, $webhook_timestamp, $signature, $body, $key);
        $this->assertFalse($result);
    }

    /**
     * Test is_valid returns false for expired signature
     */
    public function test_is_valid_returns_false_for_expired_signature(): void {
        $webhook_id = 'webhook-123';
        $old_timestamp = time() - (6 * 60); // 6 minutes ago
        $body = '{"key": "value"}';
        $key = base64_decode(Webhook::generate_key(), true);

        $signature = Webhook::get_signature($webhook_id, $old_timestamp, $body, $key);

        $result = Webhook::is_valid($webhook_id, $old_timestamp, $signature, $body, $key);
        $this->assertFalse($result);
    }

    /**
     * Test is_valid returns false when body is tampered
     */
    public function test_is_valid_returns_false_for_tampered_body(): void {
        $webhook_id = 'webhook-123';
        $webhook_timestamp = time();
        $body = '{"key": "value"}';
        $key = base64_decode(Webhook::generate_key(), true);

        $signature = Webhook::get_signature($webhook_id, $webhook_timestamp, $body, $key);

        $tampered_body = '{"key": "hacked"}';
        $result = Webhook::is_valid($webhook_id, $webhook_timestamp, $signature, $tampered_body, $key);
        $this->assertFalse($result);
    }

    /**
     * Test is_valid accepts multiple space-separated signatures
     */
    public function test_is_valid_accepts_multiple_signatures(): void {
        $webhook_id = 'webhook-123';
        $webhook_timestamp = time();
        $body = '{"key": "value"}';
        $key = base64_decode(Webhook::generate_key(), true);

        $valid_signature = Webhook::get_signature($webhook_id, $webhook_timestamp, $body, $key);
        $invalid_signature = 'v1,invalid';

        $multiple_signatures = $invalid_signature . ' ' . $valid_signature;

        $result = Webhook::is_valid($webhook_id, $webhook_timestamp, $multiple_signatures, $body, $key);
        $this->assertTrue($result);
    }

    /**
     * Test get_content returns decoded JSON object
     */
    public function test_get_content_returns_decoded_json(): void {
        $json_body = '{"action": "push", "project": {"id": 123, "name": "test"}}';

        $result = Webhook::get_content($json_body);

        $this->assertIsObject($result);
        $this->assertEquals('push', $result->action);
        $this->assertEquals(123, $result->project->id);
        $this->assertEquals('test', $result->project->name);
    }

    /**
     * Test get_content returns null for invalid JSON
     */
    public function test_get_content_returns_null_for_invalid_json(): void {
        $invalid_json = 'not valid json {]';

        $result = Webhook::get_content($invalid_json);

        $this->assertNull($result);
    }

    /**
     * Test get_content returns null for empty string
     */
    public function test_get_content_returns_null_for_empty_string(): void {
        $result = Webhook::get_content('');

        $this->assertNull($result);
    }
}
