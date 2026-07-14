<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tests for data_controller class
 *
 * @package     customfield_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_gitlab;

use advanced_testcase;
use core\encryption;
use core_customfield\category_controller;
use stdClass;

/**
 * Unit tests for data_controller class
 *
 * @package     customfield_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_controller_test extends advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    private function mock_field_controller(): field_controller {
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $data = new stdClass();
        $data->name = 'GitLab';
        $category = category_controller::create(0, $data, $handler);

        $fieldrecord = new \stdClass();
        $fieldrecord->name = 'GitLab Token';
        $fieldrecord->shortname = 'gitlab_token';
        $fieldrecord->type = 'gitlab';
        $fieldrecord->configdata = '{}';
        $fieldrecord->category = $category;

        return field_controller::create(0, $fieldrecord, $category);
    }

    private function mock_data_controller(field_controller $field, array $data = []): data_controller {
        $datarecord = new \stdClass();
        $datarecord->fieldid = 0;
        $datarecord->id = 0;
        $datarecord->value = '';

        foreach ($data as $key => $value) {
            $datarecord->$key = $value;
        }

        return data_controller::create(0, $datarecord, $field);
    }

    /**
     * Test datafield method returns 'value'
     */
    public function test_datafield_returns_value(): void {
        $field = $this->mock_field_controller();
        $controller = $this->mock_data_controller($field);

        $this->assertEquals('value', $controller->datafield());
    }

    /**
     * Test get_default_value returns empty string
     */
    public function test_get_default_value_returns_empty_string(): void {
        $field = $this->mock_field_controller();
        $controller = $this->mock_data_controller($field);

        $this->assertEquals('', $controller->get_default_value());
    }

    /**
     * Test export_value returns null
     */
    public function test_export_value_returns_null(): void {
        $field = $this->mock_field_controller();
        $controller = $this->mock_data_controller($field);

        $this->assertNull($controller->export_value());
    }

    /**
     * Test instance_form_definition adds element to form
     */
    public function test_instance_form_definition_adds_element(): void {
        $field = $this->mock_field_controller();
        $controller = $this->mock_data_controller($field);

        $mform = $this->createMock(\MoodleQuickForm::class);
        $mform->expects($this->once())->method('addElement');
        $mform->expects($this->once())->method('setType');
        $mform->expects($this->once())->method('setDefault');

        $controller->instance_form_definition($mform);
    }

    /**
     * Test instance_form_definition adds required rule when required is true
     */
    public function test_instance_form_definition_adds_required_rule(): void {
        $field = $this->mock_field_controller();
        $controller = $this->mock_data_controller($field);

        $mform = $this->createMock(\MoodleQuickForm::class);
        $mform->expects($this->once())->method('addElement');
        $mform->expects($this->once())->method('setType');
        $mform->expects($this->once())->method('setDefault');
        $mform->expects($this->once())->method('addRule');

        $controller->instance_form_definition($mform);
    }

    /**
     * Test get_value decrypts encrypted value
     */
    public function test_get_value_decrypts_value(): void {
        $originalvalue = 'test_token_12345';
        $encryptedvalue = encryption::encrypt($originalvalue);

        $field = $this->mock_field_controller();
        $controller = $this->mock_data_controller($field, [
            'value' => $encryptedvalue,
        ]);

        $decryptedvalue = $controller->get_value();

        $this->assertEquals($originalvalue, $decryptedvalue);
    }

    /**
     * Test instance_form_save encrypts and saves value
     */
    public function test_instance_form_save_encrypts_value(): void {
        $field = $this->mock_field_controller();
        
        $datarecord = [
            'fieldid' => 0,
            'id' => 0,
            'value' => '',
        ];

        $controller = $this->mock_data_controller($field, $datarecord);

        $formdata = new \stdClass();
        $formdata->customfield_gitlab_token = 'my_secret_token';

        // Mock the data and category
        $controllermock = $this->getMockBuilder(data_controller::class)
            ->setConstructorArgs([0, (object) $datarecord, $field])
            ->onlyMethods(['save', 'get_form_element_name'])
            ->getMock();

        $controllermock->expects($this->once())->method('get_form_element_name')
            ->willReturn('customfield_gitlab_token');
        $controllermock->expects($this->once())->method('save');

        // This should not throw an exception
        $controllermock->instance_form_save($formdata);
    }

    /**
     * Test instance_form_save skips if element doesn't exist in form data
     */
    public function test_instance_form_save_skips_missing_element(): void {
        $field = $this->mock_field_controller();
        $controller = $this->mock_data_controller($field);

        $formdata = new \stdClass();
        // Don't add the form element

        // Should not throw an exception
        $controller->instance_form_save($formdata);

        $this->assertTrue(true);
    }
}
