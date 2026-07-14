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
 * Tests for Gitlab HTTP client
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_gitlab\http;

use advanced_testcase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit tests for Gitlab HTTP client
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gitlab_client_test extends advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        set_config('gitlab_host', 'https://example.com', 'mod_gitlab');
    }

    private function create_curl_mock(): MockObject {
        return $this->createMock(\curl::class);
    }

    private function inject_curl_mock(Gitlab $gitlab, MockObject $curl): void {
        $reflection = new \ReflectionClass($gitlab);
        $property = $reflection->getProperty('curl');
        $property->setAccessible(true);
        $property->setValue($gitlab, $curl);
    }

    public function test_url_builds_endpoints_and_query_parameters(): void {

    
        $this->assertEquals(http_build_query([
            'page' => 2,
            'per_page' => 50,
        ], '', '&'), 'page=2&per_page=50');

        $client = new Gitlab('secret_token');

        $url = $client->url('/projects', ['page' => 2, 'per_page' => 50]);

        $this->assertEquals('https://example.com/api/v4/projects?page=2&per_page=50', $url);
    }

    public function test_get_sets_headers_and_decodes_json_response(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader')
            ->with([
                'Accept: application/json',
                'PRIVATE-TOKEN: secret_token',
            ]);
        $curl->expects($this->once())
            ->method('get')
            ->with('https://example.com/api/v4/projects', ['archived' => true])
            ->willReturn('{"id":123,"name":"example"}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 200]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $result = $client->get('/projects', ['archived' => true]);

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertEquals(123, $result->id);
        $this->assertEquals('example', $result->name);
    }

    public function test_post_sets_headers_and_returns_decoded_json(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader')
            ->with([
                'Accept: application/json',
                'PRIVATE-TOKEN: secret_token',
                'Content-type: application/json',
            ]);
        $curl->expects($this->once())
            ->method('post')
            ->with('https://example.com/api/v4/projects', '{"name":"test"}')
            ->willReturn('{"id":321,"name":"test"}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 201]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $result = $client->post('/projects', ['name' => 'test']);

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertEquals(321, $result->id);
        $this->assertEquals('test', $result->name);
    }

    public function test_put_sets_headers_and_returns_decoded_json(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader')
            ->with([
                'Accept: application/json',
                'PRIVATE-TOKEN: secret_token',
                'Content-type: application/json',
            ]);
        $curl->expects($this->once())
            ->method('put')
            ->with('https://example.com/api/v4/projects/1', '{"name":"changed"}')
            ->willReturn('{"id":1,"name":"changed"}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 200]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $result = $client->put('/projects/1', ['name' => 'changed']);

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('changed', $result->name);
    }

    public function test_delete_builds_query_string_and_decodes_response(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader')
            ->with([
                'Accept: application/json',
                'PRIVATE-TOKEN: secret_token',
            ]);
        $curl->expects($this->once())
            ->method('delete')
            ->with('https://example.com/api/v4/projects?confirm=1')
            ->willReturn('{"status":"deleted"}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 200]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $result = $client->delete('/projects', ['confirm' => true]);

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertEquals('deleted', $result->status);
    }

    public function test_handle_exceptions_throws_validation_failed_for_422(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('post')
            ->willReturn('{}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 422]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('validation failed');

        $client->post('/projects', ['name' => 'invalid']);
    }

    public function test_handle_exceptions_throws_limit_exceeded_for_429(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('get')
            ->willReturn('{}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 429]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('limit exceeded');

        $client->get('/projects');
    }

    public function test_handle_exceptions_throws_generic_exception_for_500(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('delete')
            ->willReturn('{}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 500]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('expection 500');

        $client->delete('/projects');
    }

    public function test_group_create_posts_with_normalized_path(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('post')
            ->with('https://example.com/api/v4/groups', '{"name":"Test Group","path":"test-group","parent_id":42}')
            ->willReturn('{"id":5,"name":"Test Group"}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 201]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $group = new Group($client);
        $result = $group->create('Test Group', 42);

        $this->assertEquals(5, $result->id);
        $this->assertEquals('Test Group', $result->name);
    }

    public function test_project_archive_builds_correct_url(): void {
        $client = new Gitlab('secret_token');
        $project = new Project($client);

        $url = $project->archive(10, '.tar.gz', ['sha' => 'abc123']);

        $this->assertEquals('https://example.com/api/v4/projects/10/repository/archive.tar.gz?sha=abc123', $url);
    }

    public function test_user_get_builds_correct_endpoint(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('get')
            ->with('https://example.com/api/v4/users/7', [])
            ->willReturn('{"id":7,"username":"tester"}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 200]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $user = new User($client);
        $result = $user->get(7);

        $this->assertEquals(7, $result->id);
        $this->assertEquals('tester', $result->username);
    }

    public function test_member_add_posts_username_when_string(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('post')
            ->with('https://example.com/api/v4/projects/20/members', '{"access_level":30,"username":"john"}')
            ->willReturn('{"id":20,"username":"john"}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 201]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $member = new Member($client);
        $result = $member->add(20, 'john', 30);

        $this->assertEquals(20, $result->id);
        $this->assertEquals('john', $result->username);
    }

    public function test_branch_get_encodes_branch_name(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('get')
            ->with('https://example.com/api/v4/projects/5/repository/branches/feature%2Ftest', [])
            ->willReturn('{"name":"feature/test"}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 200]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $branch = new Branch($client);
        $result = $branch->get(5, 'feature/test');

        $this->assertEquals('feature/test', $result->name);
    }

    public function test_merge_request_create_posts_expected_data(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('post')
            ->with('https://example.com/api/v4/projects/8/merge_requests', '{"source_branch":"dev","target_branch":"main","title":"New MR"}')
            ->willReturn('{"iid":1,"title":"New MR"}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 201]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $mergeRequest = new MergeRequest($client);
        $result = $mergeRequest->create(8, 'dev', 'main', 'New MR');

        $this->assertEquals(1, $result->iid);
        $this->assertEquals('New MR', $result->title);
    }

    public function test_pipeline_latest_calls_latest_endpoint(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('get')
            ->with('https://example.com/api/v4/projects/9/pipelines/latest', ['ref' => 'main'])
            ->willReturn('{"id":55}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 200]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $pipeline = new Pipeline($client);
        $result = $pipeline->latest(9, ['ref' => 'main']);

        $this->assertEquals(55, $result->id);
    }

    public function test_commit_get_last_returns_first_commit(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('get')
            ->with('https://example.com/api/v4/projects/10/repository/commits', ['per_page' => 1])
            ->willReturn('[{"id":"abc123"},{"id":"def456"}]');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 200]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $commit = new Commit($client);
        $result = $commit->get_last(10, ['per_page' => 1]);

        $this->assertEquals('abc123', $result->id);
    }

    public function test_webhook_set_custom_header_calls_put(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('put')
            ->with('https://example.com/api/v4/projects/11/hooks/99/custom_headers/X-Token', '{"value":"secret"}')
            ->willReturn('{"id":99}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 200]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $webhook = new Webhook($client);
        $result = $webhook->set_custom_header(11, 99, 'X-Token', 'secret');

        $this->assertEquals(99, $result->id);
    }

    public function test_file_create_posts_encoded_filename(): void {
        $curl = $this->create_curl_mock();
        $curl->expects($this->once())
            ->method('setHeader');
        $curl->expects($this->once())
            ->method('post')
            ->with('https://example.com/api/v4/projects/12/repository/files/' . urlencode('README.md'), '{"branch":"main","content":"hello","commit_message":"Add file"}')
            ->willReturn('{"file_path":"README.md"}');
        $curl->expects($this->once())
            ->method('get_info')
            ->willReturn(['http_code' => 201]);

        $client = new Gitlab('secret_token');
        $this->inject_curl_mock($client, $curl);

        $file = new File($client);
        $result = $file->create(12, 'README.md', 'hello', 'main', 'Add file');

        $this->assertEquals('README.md', $result->file_path);
    }
}
