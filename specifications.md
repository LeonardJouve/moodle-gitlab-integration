## Context

Cyberlearn is the official e-learning platform of HES-SO based on Moodle. It is used to store course documents, create student work groups and serve as a calendar for due assignments.

GitLab is an open source Git repository hosting service.
In compture science, teachers widely use it to distribute assignments, review student work, and evaluate participation. It also helps students to manage code sources and helps with collaboration.

For teachers, individually managing each repository, student group, and assessment workflows can quickly become time consuming and repetitive with a large amount of students.

There is currently no integrated way to interact with a Git repository hosting service from the Cyberlearn web application.
Having such integration directly from the main HES-SO application would greatly simplify and improve workflows for teachers and students.
As a teachers, this would help with the setup of repositories for students, managing groups and giving feedbacks from a unique plateform.
As a student, this would centralize documents, informations such as due dates, grades and group creation on a single platform.

Altough Moodle does not provide native GitLab integration, it allows extensibility through custom plugin creation. Such plugin can include custom UI and interact with Moodle through a REST API as well as a PHP API.
GitLab also exposes a REST API and a GraphQL API for external usage.

## Objectives

The goal of this project would be to allow interactions between GitLab or any other Git repository hosting service and Moodle.
It would include an API automating all mentionned issues accessible from a Moodle integration plugin and thus improve teachers and students workflows.

To achieve it, we must:
- Review the state of the art about existing Git repository integration with educational platforms
- Evaluate and identify most suitable technologies for the implementation
- Analyze possibilities and limitations with the GitLab and Moodle APIs
- Design the system architecture of the solution
- Develop the integration between Moodle and Gitlab
- Validate and test with representative users
- Analyze test results and improve solution accordingly

## Developped knowledge

- Modular API design and conception
- Advanced usage of repository hosting service APIs
- Moodle plugin developpement
- Project management
- DevOps principles application

## Deliverables

- Specifications document
- Gantt chart
- Usage documentation
- Plugin code
- Code tests
- Work report
- System architectural design

## Expected result

This project should provide an integration for any Git repository hosting service with Moodle.

The setup of the plugin should be easy and intuitive.

It should help teachers as well as students with their organisation, documents / informations centralization and automate / simplify tedious workflows.

## Sources

- https://moodle.org
- https://cyberlearn.hes-so.ch
- https://renkulab.io
- https://about.gitlab.com
