## Context

Moodle is a LMS (Learning Management System) widely used and adopted as a e-learning platform.

Cyberlearn is the official HES-SO deployment of Moodle. It is used to store course documents, create student work groups and serve as a calendar for due assignments.

GitLab is an open source Git repository hosting service.
In computer science, teachers widely use it to distribute assignments, review student work, and evaluate participation. It also helps students to manage code sources and helps with collaboration.

For teachers, individually managing each repository, student group, and assessment workflows can quickly become time consuming and repetitive with a large amount of students.

There is currently no way to integrate a Git repository hosting service like GitLab to Moodle.
Having GitLab functionalities directly accessible from the main e-learning platform would greatly simplify, improve workflows and user experience for teachers and students.
As a teachers, this would help with the setup of repositories for students, managing groups and giving feedbacks from a unique plateform.
As a student, this would centralize access to documents, informations such as due dates, grades and group creation.

Altough Moodle does not provide native GitLab integration, it allows extensibility through custom plugin creation. Such plugin can include custom UI and interact with Moodle through a REST API as well as a PHP API.
GitLab also exposes a REST API and a GraphQL API for external usage.

## Objectives

The goal of this project is to have an extansible integration between GitLab and Moodle.
It would include an API automating all mentionned issues accessible from a Moodle integration plugin and thus improve teachers and students workflows.

To achieve this, this project aims to:
- Review the state of the art and identify the features and functionalities the project will include based on possibilities and limitations within the GitLab and Moodle APIs (for example: group management, project creation, feedbacks and grading)
- Evaluate and identify most suitable architecture for the implementation:
    - The API should support multiple Git repository hosting services
    - The codebase should be modular, open to modifications and improvements
- Implement, develop and deploy the integration between Moodle and Gitlab by following the DevOps principles
- Validate and test (optionally with representative users)

## Deliverables

By the end of this work, we aim to have the following deliverables:

- Work report
    - Specifications document
    - Gantt chart
    - State of the art
- Plugin code published as open source 
    - Usage documentation
    - Code tests
    - System architectural design
- Demonstration of a working prototype

## Expected result

This project should provide an integration between Moodle and Gitlab and potentially any other Git repository hosting service.

The setup of the plugin should be easy and intuitive.

It should help teachers as well as students with their organisation, documents / informations centralization and automate / simplify tedious workflows.

It should be released as open source to allow others to access, use, and build upon the work.

## Sources

- https://moodle.org
- https://cyberlearn.hes-so.ch
- https://about.gitlab.com
