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

# Development

## Contributions

Contributions to this project are welcome.

You should first fork the repository. All changes should then be submitted via pull requests.

Bug reports and feature requests can be submitted via the issue tracker.

## Infrastructure

This repository includes a simple Terraform configuration to provision an AWS EC2 instance capable of hosting a Moodle instance.

```bash
cd terraform
terraform init
terraform apply
```

## Installation

You can choose between two installation options. The manual installation only sets up Moodle, while the Ansible-based installation also configures Moodle behind [Authentik](https://goauthentik.io/) as an authentication provider.

### Manual

A simplified Moodle development environment is available via my [fork](https://github.com/LeonardJouve/moodle-docker) of [moodle-docker](https://github.com/moodlehq/moodle-docker).

#### Clone repositories

```bash
git clone https://github.com/LeonardJouve/moodle-docker.git
cd moodle-docker
git clone https://github.com/LeonardJouve/moodle-gitlab-integration.git
```

#### Docker override configuration

Create a file named `docker-compose.override.yml` in the `moodle-docker` directory:

```yml
services:
  webserver:
    volumes:
      - "./moodle-gitlab-integration/plugins/module:/var/www/html/public/mod/gitlab"
      - "./moodle-gitlab-integration/plugins/customfield:/var/www/html/public/customfield/field/gitlab"
    environment:
      MOODLE_DOCKER_WEB_PORT: ""
      DB_USER: "<db_user>"
      DB_NAME: "<db_name>"
      DB_PASSWORD: "<db_password>"
      DB_PORT: <db_port>
      WEB_HOST: "<moodle_external_host>"
      WEB_PORT: <web_port>
```

#### Start the stack

Run the following command to start the environment:
```bash
docker compose -f docker-compose.yml -f docker-compose.override.yml up -d
```

### Ansible

> [!WARNING]
> These playbooks are still work in progress.
> Some manual configuration is still required, as described below.

![IaC](./iac.svg)

This repository includes Ansible playbooks to provision a server with SSH access and a Moodle instance.

First modify `ansible/inventory.ini` with your host and URL configuration.

Then update `ansible/group_vars/all.yaml` with your host and url

For DNS, you can use a service such as [DuckDNS](https://www.duckdns.org/), which provides an easy-to-setup and free dynamic DNS solution.

Finally modifiy `ansible/playbooks/res/moodle-authentik.yaml` with your host and URL settings.

You can now run Ansible playbooks:

```
cd ansible
ansible-playbook -i inventory.ini playbooks/base.yaml
ansible-playbook -i inventory.ini playbooks/traefik.yaml
ansible-playbook -i inventory.ini playbooks/authentik.yaml
ansible-playbook -i inventory.ini playbooks/moodle.yaml
```

After deployment, complete the Authentik setup by visiting:
```
http://<authentik_external_host>/if/flow/initial-setup/`
```

If the authentik blueprint apply failed, rerun the playbook after creating your Authentik account:
```
ansible-playbook -i inventory.ini playbooks/authentik.yaml
```

You may also need to:
- Remove the automatically created moodle outpost
- Expose the Moodle application using the default outpost
- Enable the Local Docker connection integration on the selected outpost

Your app should now be running and accessible at:
```
http://<moodle_external_host>
```

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

# Features

# Code

Each plugin documents its own code structure in its respective `README.md` file:

- [**customfield**](../plugins/customfield)
- [**module**](../plugins/module)