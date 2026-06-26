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
 * @package     customfield_gitlab
 * @category    string
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_gitlab;

use core\encryption;

class data_controller extends \core_customfield\data_controller {
    /**
     * Return the name of the field where the information is stored
     * @return string
     */
    public function datafield(): string {
        return 'value';
    }

    /**
     * Add fields for editing a checkbox field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        $field = $this->get_field();
        $label = $field->get_formatted_name();
        $isrequired = $field->get_configdata_property('required');

        $elementname = $this->get_form_element_name();

        $mform->addElement(
            'passwordunmask',
            $elementname,
            $label
        );
        $mform->setType($elementname, PARAM_RAW);
        $mform->setDefault($elementname, '');
        if ($isrequired) {
            $mform->addRule($elementname, null, 'required', null, 'client');
        }
    }

    /**
     * Returns the default value as it would be stored in the database (not in human-readable format).
     *
     * @return mixed
     */
    public function get_default_value() {
        return '';
    }

    /**
     * Returns value in a human-readable format
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        return '••••••••';
    }

    /**
     * Saves the data coming from form
     *
     * @param \stdClass $datanew data coming from the form
     */
    public function instance_form_save(\stdClass $datanew) {
        $elementname = $this->get_form_element_name();
        if (!property_exists($datanew, $elementname)) {
            return;
        }

        $datafield = $this->datafield();
        $value = encryption::encrypt($datanew->{$elementname});

        $this->data->set($datafield, $value);
        $this->data->set('value', $value);

        // Set component, area and itemid from the handler.
        $category = $this->field->get_category();
        $this->data->set_many([
            'component' => $category->get_original_component(),
            'area' => $category->get_original_area(),
            'itemid' => $category->get_original_itemid(),
        ]);
        $this->save();
    }

    public function get_value() {
        $value = parent::get_value();
        return encryption::decrypt($value);
    }
}