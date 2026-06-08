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
 * Library of interface functions and constants.
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_gitlab\local\Helper;
use mod_gitlab\http\Gitlab;

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function gitlab_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_gitlab into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_gitlab_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function gitlab_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $transaction = $DB->start_delegated_transaction();

    try {
        $token = Helper::get_course_gitlab_token($moduleinstance->course);
        $client = new Gitlab($token);

        $group = $client->group()->create($moduleinstance->name, $moduleinstance->parent_group);

        $reviewers = array_values(array_filter(
            array_map('trim', $moduleinstance->reviewer ?? []),
            function ($v) {
                return $v !== '';
            }
        ));

        $template = $client->project()->create($moduleinstance->name . "_template", $group->id);

        $moduleinstance->reviewers = json_encode($reviewers ?: [], JSON_UNESCAPED_UNICODE);
        $moduleinstance->timecreated = time();
        $moduleinstance->group_id = $group->id;
        $moduleinstance->template_id = $template->id;

        $id = $DB->insert_record('gitlab', $moduleinstance);

        $transaction->allow_commit();

        return $id;
    } catch (\Exception $e) {
        $transaction->rollback($e);
        throw $e;
    }
}

/**
 * Updates an instance of the mod_gitlab in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_gitlab_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function gitlab_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $reviewers = array_values(array_filter(
        array_map('trim', $moduleinstance->reviewer ?? []),
        function ($v) {
            return $v !== '';
        }
    ));
    
    $moduleinstance->reviewers = json_encode($reviewers ?: [], JSON_UNESCAPED_UNICODE);
    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('gitlab', $moduleinstance);
}

/**
 * Removes an instance of the mod_gitlab from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function gitlab_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('gitlab', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $DB->delete_records('gitlab', ['id' => $id]);

    return true;
}
