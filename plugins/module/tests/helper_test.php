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
 * Tests for Helper class
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_gitlab\local;

use advanced_testcase;

/**
 * Unit tests for Helper class
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper_test extends advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Test get_user_gitlab_username returns username when it exists
     */
    public function test_get_user_gitlab_username_returns_username(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();

        $field = new \stdClass();
        $field->shortname = 'gitlab_username';
        $field->name = 'GitLab Username';
        $field->datatype = 'text';
        $field->categoryid = 1;
        $fieldid = $DB->insert_record('user_info_field', $field);

        $data = new \stdClass();
        $data->userid = $user->id;
        $data->fieldid = $fieldid;
        $data->data = 'test_username';
        $DB->insert_record('user_info_data', $data);

        $username = Helper::get_user_gitlab_username($user->id);
        $this->assertEquals('test_username', $username);
    }

    /**
     * Test get_user_gitlab_username returns null when username doesn't exist
     */
    public function test_get_user_gitlab_username_returns_null_when_not_exists(): void {
        $user = $this->getDataGenerator()->create_user();

        $username = Helper::get_user_gitlab_username($user->id);
        $this->assertNull($username);
    }

    /**
     * Test get_user_gitlab_username returns null when username is empty
     */
    public function test_get_user_gitlab_username_returns_null_when_empty(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();

        $field = new \stdClass();
        $field->shortname = 'gitlab_username';
        $field->name = 'GitLab Username';
        $field->datatype = 'text';
        $field->categoryid = 1;
        $fieldid = $DB->insert_record('user_info_field', $field);

        $data = new \stdClass();
        $data->userid = $user->id;
        $data->fieldid = $fieldid;
        $data->data = '';
        $DB->insert_record('user_info_data', $data);

        $username = Helper::get_user_gitlab_username($user->id);
        $this->assertNull($username);
    }

    /**
     * Test get_user_gitlab_username with multiple fields returns correct one
     */
    public function test_get_user_gitlab_username_with_multiple_fields(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();

        // Create other field
        $field1 = new \stdClass();
        $field1->shortname = 'other_field';
        $field1->name = 'Other Field';
        $field1->datatype = 'text';
        $field1->categoryid = 1;
        $fieldid1 = $DB->insert_record('user_info_field', $field1);

        $data1 = new \stdClass();
        $data1->userid = $user->id;
        $data1->fieldid = $fieldid1;
        $data1->data = 'other_value';
        $DB->insert_record('user_info_data', $data1);

        // Create gitlab_username field
        $field2 = new \stdClass();
        $field2->shortname = 'gitlab_username';
        $field2->name = 'GitLab Username';
        $field2->datatype = 'text';
        $field2->categoryid = 1;
        $fieldid2 = $DB->insert_record('user_info_field', $field2);

        $data2 = new \stdClass();
        $data2->userid = $user->id;
        $data2->fieldid = $fieldid2;
        $data2->data = 'correct_gitlab_user';
        $DB->insert_record('user_info_data', $data2);

        $username = Helper::get_user_gitlab_username($user->id);
        $this->assertEquals('correct_gitlab_user', $username);
    }
}
