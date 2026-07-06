# Introduction

This project is an exten­sible integration between Moodle and GitLab through Moodle plugins and the GitLab REST API.

Both platforms are widely used in Computer Science education but operate indepen­dently, resulting in fragmented workflows for both students and teachers.

The integration consists of two Moodle plugins that work together:
- module
- customfield

These plugins enable direct interaction with GitLab features from within Moodle while automating repetitive tasks.

The result is a unified platform where users can access relevant information and perform common GitLab related actions without leaving Moodle.

The project targets **Moodle version 5**.

# Installation

Both plugins are located in the [plugins](../plugins/) directory.

To install them, copy each plugin to the appropriate Moodle directory and rename each directory to `gitlab`:

- [**customfield**](../plugins/customfield) → `public/customfield/field`.
- [**module**](../plugins/module) → `public/mod`.

Once both plugins have been copied to their respective locations, log in to your Moodle site as an admin and go to _Site administration_ → _Notifications_ to complete the installation.

Alternatively, you can run

```
php admin/cli/upgrade.php
```

to complete the installation from the command line.

# Collaborate

# Configuration

## Notifications

To enable student web notifications for assignment deadlines, configure _Notification of GitLab submissions_.

This will send:

- One notification 1 day before the assignment due date
- One notification on the due date

You can enable this under:

_Site administration_ → _Messaging_ → _Notification settings_ → _gitlab_

## Webhooks

The module plugin exposes a webhook endpoint that must be publicly accessible without authentication, as it is called directly by GitLab.

```
https://<moodle-host>/mod/gitlab/webhook.php
```

The received data is signed by GitLab, and the signature is verified by the Moodle plugin. This ensures that the payload authenticity and integrity.

If any form of authentication blocks access to the endpoint, GitLab will not be able to trigger webhooks.

## Timezone

Assignment due dates are interpreted according to the Moodle instance timezone.

You can configure this under:

_Site administration_ → _Location_ → _Location settings_ → _Default timezone_

# Code

Each plugin documents its own code structure in its respective `README.md` file:

- [**customfield**](../plugins/customfield)
- [**module**](../plugins/module)