# GitLab Custom Fields #

Learn more about Moodle custom field plugins in the official [documentation](https://moodledev.io/docs/5.0/apis/plugintypes/customfield).

## Purpose

This plugin adds a new custom field type for storing sensitive content.

Data are encrypted using `\core\encryption` class before being stored into the database and decrypted when read.

The plugin also defines two custom fields:
- a course-level field for storing a course-specific GitLab token
- a user-level field for storing the user's GitLab username

The GitLab token field uses the newly created custom field type to encrypt data before storing it in the database.

## Code
```
├── classes
│   ├── data_controller.php
│   └── field_controller.php
├── db
│   └── install.php
├── lang
│   └── en
│       └── customfield_gitlab.php
└── version.php
```

- `classes/data_controller.php` – Handles encryption and retrieval of custom field data
- `classes/field_controller.php` – Required by custom field plugins but currently unused
- `db/install.php` – Handles creation of custom fields during installation
- `lang/en/customfield_gitlab.php` – English language strings
- `version.php` – Plugin metadata (version, dependencies, compatibility)

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/customfield/field/gitlab

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
