# Introduction
_Repository Assignment Workflow_ (RAW) is an exten­sible integration between Moodle and GitLab through Moodle plugins and the GitLab REST API.

Both platforms are widely used in Computer Science education but operate independently, which can result in fragmented workflows for both students and teachers.

The integration consists of two Moodle plugins that work together:
- module
- customfield

These plugins enable direct interaction with GitLab features from within Moodle while automating repetitive workflows.

The result is a unified platform where users can access relevant information and perform common GitLab-related actions without leaving Moodle.

The project targets **Moodle version 5.x**.

# Documentation
See the usage [documentation](docs/).

# Features

See the features [documentation](docs/features.md).

# Code

Each plugin documents its code structure in its own `README.md` file:

- [**customfield**](plugins/customfield)
- [**module**](plugins/module)
