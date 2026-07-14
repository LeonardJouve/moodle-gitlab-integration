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
 * Tests for Action class
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_gitlab\local;

use advanced_testcase;
use PHPUnit\Framework\MockObject\MockObject;
use TestHelper;

/**
 * Unit tests for Action class
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action_test extends advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Create a mock Bridge object
     *
     * @return MockObject
     */
    private function create_mock_bridge(): MockObject {
        return $this->createMock(\mod_gitlab\local\bridge\Bridge::class);
    }

    /**
     * Create a module instance object
     *
     * @param int $courseid
     * @return \stdClass
     */
    private function create_module_instance(int $courseid): \stdClass {
        global $DB;

        $module = TestHelper::mock_module();
        $module->course = $courseid;
        $module->name = 'Test Module';
        $module->group_size = 3;
        $module->due_date = time() + (7 * 24 * 60 * 60);
        $module->id = $DB->insert_record('gitlab', $module);

        return $module;
    }

    /**
     * Test join_group with invalid group_id returns false
     */
    public function test_join_group_with_invalid_group_id(): void {
        $_GET['group_id'] = '';

        $course = $this->getDataGenerator()->create_course();
        $module = $this->create_module_instance($course->id);

        $bridge = $this->create_mock_bridge();

        ob_start();
        $result = Action::join_group($bridge, $module);
        ob_get_clean();

        $this->assertFalse($result);
    }

    /**
     * Test join_group fails when bridge returns false
     */
    public function test_join_group_fails_when_bridge_returns_false(): void {
        $_GET['group_id'] = 123;

        $course = $this->getDataGenerator()->create_course();
        $module = $this->create_module_instance($course->id);

        $bridge = $this->create_mock_bridge();
        $bridge->method('join_group')->willReturn(false);

        ob_start();
        $result = Action::join_group($bridge, $module);
        ob_get_clean();

        $this->assertFalse($result);
    }

    /**
     * Test join_group succeeds when bridge returns true
     */
    public function test_join_group_succeeds_when_bridge_returns_true(): void {
        $_GET['group_id'] = 123;

        $course = $this->getDataGenerator()->create_course();
        $module = $this->create_module_instance($course->id);

        $bridge = $this->create_mock_bridge();
        $bridge->method('join_group')->willReturn(true);

        $this->expectException(\moodle_exception::class);

        Action::join_group($bridge, $module);
    }

    /**
     * Test create_group with successful creation
     */
    public function test_create_group_succeeds_with_group_id(): void {
        $course = $this->getDataGenerator()->create_course();
        $module = $this->create_module_instance($course->id);

        $bridge = $this->create_mock_bridge();
        $result = new \stdClass();
        $result->group_id = 456;
        $bridge->method('create_group')->willReturn($result);

        $this->expectException(\moodle_exception::class);

        Action::create_group($bridge, $module, true);
    }

    /**
     * Test create_group without join doesn't call join_group
     */
    public function test_create_group_without_join_does_not_join(): void {
        $course = $this->getDataGenerator()->create_course();
        $module = $this->create_module_instance($course->id);

        $bridge = $this->createMock(\mod_gitlab\local\bridge\Bridge::class);
        $result = new \stdClass();
        $result->group_id = 456;
        $bridge->method('create_group')->willReturn($result);
        $bridge->expects($this->never())->method('join_group');

        $this->expectException(\moodle_exception::class);

        Action::create_group($bridge, $module, false);
    }

    /**
     * Test create_group with join calls join_group
     */
    public function test_create_group_with_join_calls_join(): void {
        $course = $this->getDataGenerator()->create_course();
        $module = $this->create_module_instance($course->id);

        $bridge = $this->createMock(\mod_gitlab\local\bridge\Bridge::class);
        $result = new \stdClass();
        $result->group_id = 456;
        $bridge->method('create_group')->willReturn($result);
        $bridge->expects($this->once())->method('join_group');

        // Catch the redirect
        $this->expectException(\moodle_exception::class);

        Action::create_group($bridge, $module, true);
    }

    /**
     * Test create_group fails when create_group returns null group_id
     */
    public function test_create_group_fails_with_null_group_id(): void {
        $course = $this->getDataGenerator()->create_course();
        $module = $this->create_module_instance($course->id);

        $bridge = $this->create_mock_bridge();
        $result = new \stdClass();
        $result->group_id = null;
        $bridge->method('create_group')->willReturn($result);

        ob_start();
        $response = Action::create_group($bridge, $module, true);
        ob_get_clean();

        $this->assertFalse($response);
    }

    /**
     * Test create_group catches RuntimeException
     */
    public function test_create_group_catches_runtime_exception(): void {
        $course = $this->getDataGenerator()->create_course();
        $module = $this->create_module_instance($course->id);

        $bridge = $this->create_mock_bridge();
        $exception = new \mod_gitlab\http\RuntimeException('API Error');
        $bridge->method('create_group')->willThrowException($exception);

        ob_start();
        $response = Action::create_group($bridge, $module, true);
        ob_get_clean();

        $this->assertFalse($response);
    }

    /**
     * Test that global USER context is used in join_group
     */
    public function test_join_group_uses_global_user(): void {
        global $USER;

        $_GET['group_id'] = 123;

        $course = $this->getDataGenerator()->create_course();
        $module = $this->create_module_instance($course->id);

        $bridge = $this->createMock(\mod_gitlab\local\bridge\Bridge::class);
        $bridge->expects($this->once())->method('join_group')
            ->with(123, $USER->id, $module)
            ->willReturn(true);

        // Catch the redirect
        $this->expectException(\moodle_exception::class);

        Action::join_group($bridge, $module);
    }
}
