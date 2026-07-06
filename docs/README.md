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

Both plugins are located in the [plugins](plugins/) directory.

To install them, copy each plugin to the appropriate Moodle directory and rename each directory to `gitlab`:

- [**customfield**](plugins/customfield) → `public/customfield/field`.
- [**module**](plugins/module) → `public/mod`.

Once both plugins have been copied to their respective locations, navigate to your Moodle instance and complete the installation by following the Moodle plugin installation prompts.

# Collaborate

# Configuration

Web notifications should be enabled for both "Notification of GitLab submissions" under _Site administration_ -> _Messaging_ -> _Notification settings_ -> _gitlab_

Endpoint `/mod/gitlab/webhook.php` must not be protected behind any authentication layer as this will be used by GitLab webhooks.

# Code

## Customfield

## Module