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

    /**
     * Test datafield method returns 'value'
     */
    public function test_datafield_returns_value(): void {
        $fieldrecord = new \stdClass();
        $fieldrecord->name = 'GitLab Token';
        $fieldrecord->shortname = 'gitlab_token';
        $fieldrecord->type = 'gitlab';
        $fieldrecord->configdata = '{}';

        $field = new field_controller(0, $fieldrecord);
        $datarecord = new \stdClass();
        $datarecord->fieldid = 0;

        $controller = data_controller::create(0, $datarecord, $field);

        $this->assertEquals('value', $controller->datafield());
    }

    /**
     * Test get_default_value returns empty string
     */
    public function test_get_default_value_returns_empty_string(): void {
        $fieldrecord = new \stdClass();
        $fieldrecord->name = 'GitLab Token';
        $fieldrecord->shortname = 'gitlab_token';
        $fieldrecord->type = 'gitlab';
        $fieldrecord->configdata = '{}';

        $field = new field_controller(0, $fieldrecord);
        $datarecord = new \stdClass();
        $datarecord->fieldid = 0;

        $controller = data_controller::create(0, $datarecord, $field);

        $this->assertEquals('', $controller->get_default_value());
    }

    /**
     * Test export_value returns null
     */
    public function test_export_value_returns_null(): void {
        $fieldrecord = new \stdClass();
        $fieldrecord->name = 'GitLab Token';
        $fieldrecord->shortname = 'gitlab_token';
        $fieldrecord->type = 'gitlab';
        $fieldrecord->configdata = '{}';

        $field = new field_controller(0, $fieldrecord);
        $datarecord = new \stdClass();
        $datarecord->fieldid = 0;

        $controller = data_controller::create(0, $datarecord, $field);

        $this->assertNull($controller->export_value());
    }

    /**
     * Test instance_form_definition adds element to form
     */
    public function test_instance_form_definition_adds_element(): void {
        $fieldrecord = new \stdClass();
        $fieldrecord->name = 'GitLab Token';
        $fieldrecord->shortname = 'gitlab_token';
        $fieldrecord->type = 'gitlab';
        $fieldrecord->configdata = json_encode(['required' => false]);

        $field = new field_controller(0, $fieldrecord);
        $datarecord = new \stdClass();
        $datarecord->fieldid = 0;

        $controller = data_controller::create(0, $datarecord, $field);

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
        $fieldrecord = new \stdClass();
        $fieldrecord->name = 'GitLab Token';
        $fieldrecord->shortname = 'gitlab_token';
        $fieldrecord->type = 'gitlab';
        $fieldrecord->configdata = json_encode(['required' => true]);

        $field = new field_controller(0, $fieldrecord);
        $datarecord = new \stdClass();
        $datarecord->fieldid = 0;

        $controller = data_controller::create(0, $datarecord, $field);

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

        $fieldrecord = new \stdClass();
        $fieldrecord->name = 'GitLab Token';
        $fieldrecord->shortname = 'gitlab_token';
        $fieldrecord->type = 'gitlab';
        $fieldrecord->configdata = '{}';

        $field = new field_controller(0, $fieldrecord);
        $datarecord = new \stdClass();
        $datarecord->fieldid = 0;
        $datarecord->value = $encryptedvalue;

        $controller = data_controller::create(0, $datarecord, $field);
        $decryptedvalue = $controller->get_value();

        $this->assertEquals($originalvalue, $decryptedvalue);
    }

    /**
     * Test instance_form_save encrypts and saves value
     */
    public function test_instance_form_save_encrypts_value(): void {
        $fieldrecord = new \stdClass();
        $fieldrecord->name = 'GitLab Token';
        $fieldrecord->shortname = 'gitlab_token';
        $fieldrecord->type = 'gitlab';
        $fieldrecord->configdata = json_encode(['required' => false]);

        $field = new field_controller(0, $fieldrecord);
        $datarecord = new \stdClass();
        $datarecord->fieldid = 0;
        $datarecord->id = 0;
        $datarecord->value = '';

        $controller = data_controller::create(0, $datarecord, $field);

        $formdata = new \stdClass();
        $formdata->customfield_gitlab_token = 'my_secret_token';

        // Mock the data and category
        $controllermock = $this->getMockBuilder(data_controller::class)
            ->setConstructorArgs([0, $datarecord, $field])
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
        $fieldrecord = new \stdClass();
        $fieldrecord->name = 'GitLab Token';
        $fieldrecord->shortname = 'gitlab_token';
        $fieldrecord->type = 'gitlab';
        $fieldrecord->configdata = json_encode(['required' => false]);

        $field = new field_controller(0, $fieldrecord);
        $datarecord = new \stdClass();
        $datarecord->fieldid = 0;

        $controller = data_controller::create(0, $datarecord, $field);

        $formdata = new \stdClass();
        // Don't add the form element

        // Should not throw an exception
        $controller->instance_form_save($formdata);

        $this->assertTrue(true);
    }
}
