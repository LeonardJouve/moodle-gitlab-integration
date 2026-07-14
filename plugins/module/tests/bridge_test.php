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
 * Tests for Bridge class
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_gitlab\local\bridge;

use advanced_testcase;
use PHPUnit\Framework\MockObject\MockObject;
use mod_gitlab\TestHelper;

/**
 * Unit tests for Bridge class
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bridge_test extends advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Create a mock Gitlab client
     *
     * @return MockObject
     */
    private function create_mock_gitlab_client(): MockObject {
        return $this->createMock(\mod_gitlab\http\Gitlab::class);
    }

    /**
     * Test Bridge constructor initializes client and resources
     */
    public function test_bridge_constructor_initializes_client(): void {
        $client = $this->create_mock_gitlab_client();

        $bridge = new Bridge($client);

        // Verify Bridge was created successfully
        $this->assertInstanceOf(Bridge::class, $bridge);
    }

    /**
     * Test join_group returns false when Group::join_group fails
     */
    public function test_join_group_returns_false_when_join_fails(): void {
        global $DB;

        // Create test data
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        
        $module = TestHelper::mock_module();
        $module->course = $course->id;
        $module->name = 'Test Module';
        $module->group_size = 3;
        $moduleid = $DB->insert_record('gitlab', $module);

        $group_record = new \stdClass();
        $group_record->module_id = $moduleid;
        $group_record->repository_id = 123;
        $groupid = $DB->insert_record('gitlab_groups', $group_record);

        $moduleinstance = $DB->get_record('gitlab', ['id' => $moduleid]);

        $client = $this->create_mock_gitlab_client();
        $bridge = new Bridge($client);

        $result = $bridge->join_group($groupid, $user->id, $moduleinstance);

        $this->assertFalse($result);
    }

    /**
     * Test set_group_members with empty members list
     */
    public function test_set_group_members_with_empty_members(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $module = TestHelper::mock_module();
        $module->course = $course->id;
        $module->name = 'Test Module';
        $module->group_size = 3;
        $moduleid = $DB->insert_record('gitlab', $module);

        $group_record = new \stdClass();
        $group_record->module_id = $moduleid;
        $group_record->repository_id = 123;
        $groupid = $DB->insert_record('gitlab_groups', $group_record);

        $client = $this->create_mock_gitlab_client();
        $client->method('member')->willReturnSelf();
        $client->expects($this->never())->method('add');

        $bridge = new Bridge($client);

        $result = $bridge->set_group_members([], 3, $groupid);

        $this->assertIsObject($result);
    }

    /**
     * Test set_module_reviewers with empty reviewer lists
     */
    public function test_set_module_reviewers_with_no_changes(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $module = TestHelper::mock_module();
        $module->course = $course->id;
        $module->name = 'Test Module';
        $module->group_size = 3;
        $moduleid = $DB->insert_record('gitlab', $module);

        $client = $this->create_mock_gitlab_client();
        $client->method('member')->willReturnSelf();
        $client->expects($this->never())->method('remove');
        $client->expects($this->never())->method('add');

        $bridge = new Bridge($client);

        $reviewers = [1, 2, 3];
        $bridge->set_module_reviewers($moduleid, 456, $reviewers, $reviewers);
    }

    /**
     * Test create_calendar_event creates event with correct data
     */
    public function test_create_calendar_event_creates_event(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $module = TestHelper::mock_module();
        $module->course = $course->id;
        $module->name = 'Test Module';
        $module->group_size = 3;
        $module->due_date = time() + (7 * 24 * 60 * 60);
        $moduleid = $DB->insert_record('gitlab', $module);

        $moduleinstance = $DB->get_record('gitlab', ['id' => $moduleid]);

        $client = $this->create_mock_gitlab_client();
        $bridge = new Bridge($client);

        $bridge->create_calendar_event($moduleinstance);

        // Verify calendar event was created
        $events = $DB->get_records('event', ['courseid' => $course->id, 'modulename' => 'gitlab']);
        $this->assertNotEmpty($events);

        $event = reset($events);
        $this->assertEquals('gitlab-due-date', $event->eventtype);
        $this->assertEquals($module->due_date, $event->timestart);
    }

    /**
     * Test leave_group returns false when user has no gitlab account
     */
    public function test_leave_group_returns_false_when_no_gitlab_account(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        $module = TestHelper::mock_module();
        $module->course = $course->id;
        $module->name = 'Test Module';
        $module->group_size = 3;
        $moduleid = $DB->insert_record('gitlab', $module);

        $group_record = new \stdClass();
        $group_record->module_id = $moduleid;
        $group_record->repository_id = 123;
        $groupid = $DB->insert_record('gitlab_groups', $group_record);

        $member_record = new \stdClass();
        $member_record->group_id = $groupid;
        $member_record->user_id = $user->id;
        $DB->insert_record('gitlab_group_members', $member_record);

        $client = $this->create_mock_gitlab_client();
        $bridge = new Bridge($client);

        $result = $bridge->leave_group($moduleid, $user->id);

        $this->assertFalse($result);
    }

    /**
     * Test release_solution processes all groups
     */
    public function test_release_solution_processes_groups(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $module = TestHelper::mock_module();
        $module->course = $course->id;
        $module->name = 'Test Module';
        $module->group_size = 3;
        $moduleid = $DB->insert_record('gitlab', $module);

        // Create multiple groups
        for ($i = 0; $i < 3; $i++) {
            $group_record = new \stdClass();
            $group_record->module_id = $moduleid;
            $group_record->repository_id = 100 + $i;
            $DB->insert_record('gitlab_groups', $group_record);
        }

        $client = $this->createMock(\mod_gitlab\http\Gitlab::class);
        $merge_request_client = $this->createMock(\mod_gitlab\http\MergeRequest::class);
        $merge_request_client->expects($this->exactly(3))->method('create');
        $client->method('merge_request')->willReturn($merge_request_client);

        $bridge = new Bridge($client);
        $bridge->release_solution($moduleid, 456);
    }
}
