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

use core\task\manager;
use mod_gitlab\local\Helper;
use mod_gitlab\http\Gitlab;
use mod_gitlab\local\Bridge;
use mod_gitlab\local\FinalizeGroupCreationTask;
use mod_gitlab\local\SubmissionSoonTask;
use mod_gitlab\local\SubmissionTask;

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

        $bridge = new Bridge($client);
        $result = $bridge->create_module($moduleinstance);

        $transaction->allow_commit();

        return $result->module_id;
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

    $moduleinstance->reviewers = json_encode($moduleinstance->reviewer ?: [], JSON_UNESCAPED_UNICODE);
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

    $moduleinstance = $DB->get_record('gitlab', ['id' => $id]);
    if (!$moduleinstance) {
        return false;
    }

    // delete adhoc tasks
    $classnames = [
        FinalizeGroupCreationTask::class,
        SubmissionSoonTask::class,
        SubmissionTask::class,
    ];
    foreach ($classnames as $classname) {
        $tasks = manager::get_adhoc_tasks($classname);
        foreach ($tasks as $task) {
            throw new Exception('task data ' . $task->get_custom_data_as_string());
            $customdata = $task->get_custom_data();
            if (!isset($customdata->module_id) || $customdata->module_id != $id) {
                continue;
            }

            throw new Exception('delete task ' . $task->id);

            manager::delete_adhoc_task($task->id);
        }
    }

    // delete calendar events
    $event_ids = $DB->get_fieldset('event', 'id', [
        'modulename' => 'gitlab',
        'instance'   => $id,
    ], IGNORE_MISSING);

    foreach ($event_ids as $event_id) {
        $event = calendar_event::load($event_id);
        $event->delete();
    }

    // delete members
    $group_ids = $DB->get_fieldset(
        'gitlab_groups',
        'id',
        ['module_id' => $id],
    );

    list($in_sql, $params) = $DB->get_in_or_equal($group_ids, SQL_PARAMS_NAMED, '', true, NULL);

    $DB->delete_records_select(
        'gitlab_group_members',
        "group_id $in_sql",
        $params,
    );

    // delete groups
    $DB->delete_records('gitlab_groups', ['module_id' => $id]);

    // delete module
    $DB->delete_records('gitlab', ['id' => $id]);

    return true;
}


/**
 * Serve the manual enrol users form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function mod_gitlab_output_fragment_manage_group_form($args) {
    $args = (object) $args;
    $context = $args->context;
    $o = '';

    require_capability('mod/gitlab:addinstance', $context);

    $mform = new mod_gitlab_manage_group_form(null, $args);

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}
