# GitLab Module #

Learn more about Moodle module plugins in the official [documentation](https://moodledev.io/docs/5.0/apis/plugintypes/mod).

## Purpose

This plugin adds a new Moodle module type called _GitLab_.

This module is the central component of the integration.

Creating the module requires a GitLab token defined at the course level, as well as a GitLab username for each user in order to access the module.

Once created, the module provides teachers and listed reviewers with access to a GitLab template repository.

Students and teachers can create groups, and students can join existing groups.

Each group has its own repository, which is a fork of the template repository.

After the module due date, the template’s solution branch is automatically submitted to each group repository as a merge request.

Changes made in group repositories are also propagated back to the template repository via merge requests.

See the features [documentation](../../docs/features.md) for a more detailed list of available features.

## Code
```
├── amd
│   ├── build
│   └── src
├── classes
│   ├── external
│   ├── http
│   └── local
├── db
├── lang
├── pix
├── templates
├── index.php
├── lib.php
├── mod_form.php
├── version.php
├── view.php
└── webhook.php
```

- `amd/build` – AMD Javascript modules minified
- `amd/src` – AMD Javascript modules source
- `classes/external` – External services exposed
- `classes/http` – GitLab HTTP Client
- `classes/local` – Other plugin internal classes
- `db` – Define database schemas, services, notifications, access, migrations
- `lang` – Language strings
- `pix` – Assets
- `templates` – Mustache templates
- `index.php` – List all instances of a module
- `lib.php` – Handles module creation / update / deletion
- `mod_form.php` – Module creation / edition form
- `view.php` – Module view page
- `webhook.php` – Webhook page
- `version.php` – Plugin metadata (version, dependencies, compatibility)

## Database schemas

# Database schema

The plugin stores its data in three main tables. The following tables contain the configuration of Moodle activity instances, GitLab resources, and group memberships.

## `gitlab`

Stores GitLab activity module instances.

| Field            | Description                                                  |
| ---------------- | ------------------------------------------------------------ |
| `id`             | Unique identifier of the activity instance.                  |
| `course`         | ID of the Moodle course containing the activity.             |
| `name`           | Name of the activity instance.                               |
| `timecreated`    | Timestamp when the activity instance was created.            |
| `timemodified`   | Timestamp when the activity instance was last modified.      |
| `intro`          | Activity description.                                        |
| `introformat`    | Format of the activity description.                          |
| `group_id`       | GitLab group ID created or linked to the activity instance.  |
| `parent_group`   | GitLab parent group ID where resources are created.          |
| `group_size`     | Maximum number of members allowed per group.                 |
| `due_date`       | Submission deadline timestamp.                               |
| `reviewers`      | JSON-encoded list of GitLab usernames assigned as reviewers. |
| `template_id`    | GitLab template repository ID associated with the activity.  |
| `webhook_secret` | Encrypted secret used to verify GitLab webhook payloads.     |

## `gitlab_groups`

Stores GitLab groups associated with an activity instance.

| Field           | Description                                     |
| --------------- | ----------------------------------------------- |
| `id`            | Unique identifier of the group record.          |
| `module_id`     | ID of the associated GitLab activity instance.  |
| `repository_id` | GitLab repository ID associated with the group. |

## `gitlab_group_members`

Stores the relationship between GitLab groups and Moodle users.

| Field      | Description                                   |
| ---------- | --------------------------------------------- |
| `id`       | Unique identifier of the membership record.   |
| `group_id` | ID of the associated GitLab group.            |
| `user_id`  | ID of the Moodle user belonging to the group. |


## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/gitlab

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2026 Léonard Jouve leonard.jouve@gmail.com

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
