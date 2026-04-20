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
 * Prints an instance of mod_gitlab.
 *
 * @package     mod_gitlab
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\html_writer;
use core\url;
use mod_gitlab\http\Gitlab;
use mod_gitlab\http\RuntimeException;

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$g = optional_param('g', 0, PARAM_INT);

$action = optional_param('action', '', PARAM_ALPHA);

if ($id) {
    $cm = get_coursemodule_from_id('gitlab', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('gitlab', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('gitlab', ['id' => $g], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('gitlab', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/gitlab/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$client = new Gitlab($moduleinstance->token);

if ($action === 'createrepository') {
    try {
        $client->project()->create(
            $moduleinstance->name . "_" . $USER->username . "_" . bin2hex(random_bytes(8)),
            $moduleinstance->group_id
        );
        // $moduleinstance->group_id = $client->group()->create($moduleinstance->name)->id;
    } catch (RuntimeException $e) {
        echo html_writer::div(
            sprintf('failed to create repository: %s', $e->getMessage()),
            'alert alert-danger'
        );
    }

    redirect(
        new url('/mod/gitlab/view.php', ['id' => $cm->id]),
        'Repository created!'
    );
}

echo $OUTPUT->header();

$url = new url('/mod/gitlab/view.php', [
    'id' => $cm->id,
    'action' => 'createrepository'
]);

echo html_writer::link(
    $url,
    'Create GitLab Repository',
    ['class' => 'btn btn-primary']
);

try {
    $repositories = $client->project()->list($moduleinstance->group_id);
} catch (RuntimeException $e) {
    echo html_writer::div(
        sprintf('failed to list repositories: %s', $e->getMessage()),
        'alert alert-danger'
    );
}

echo html_writer::start_tag('ul');

foreach ($repositories as $repository) {
    echo html_writer::tag(
        'li',
        html_writer::link(
            $repository->web_url,
            format_string($repository->name),
            ['target' => '_blank']
        )
    );
}

echo html_writer::end_tag('ul');

echo $OUTPUT->footer();
