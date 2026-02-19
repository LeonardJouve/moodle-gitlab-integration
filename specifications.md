## Subjet
Cyberlearn is the official e-learning platform of HES-SO based on Moodle.
As a teacher in computer science, there is no integrated way to interact with a Git repository hosting service from the Cyberlearn web application.
Beeing able to manage code sources from the main HES-SO application would greatly simplify and improve workflows for teachers and students.
Having such integration would help teachers setup repositories for students, manage groups, give feedbacks from a unique plateform.
As a student, this would centralize documents, informations such as due dates, grades on a single platform.

Renku is an open source platform that aims to simplify collaboration and help with reproducibility for data science projects. Currently, teachers need to manually manage groups, test, grade, analyse students participation based on commits. This can be time consuming and repetitive for big classes.

Gitlab is an open source Git repository hosting service.
It is widely used in school environments to manage code sources and help with collaboration.

The goal of this project would be to allow interactions between these services.
It would include an API automating all mentionned issues.
The API could then be used to develop Renku and Moodle integration plugin and thus improve teachers and students workflows.

## Objectives
The final integration should allow:
    - project creation
    - group management
    - students participation analysis
    - project due date addition to the calendar
    - grade and feedbacks visualisation
    - bidirectional synchronization of course documents with a repository

Additional requirements:
- The API should support multiple Git repository hosting services
- The codebase should be modular, open to modifications and improvements
- The development should follow the DevOps culture as much as possible

## Developped knowledge
- modular API design and conception
- advanced usage of repository hosting service APIs
- Moodle plugin developpement
- Project management
- DevOps principles application

## Expected result
This project should provide an integration for any Git repository hosting service with Moodle.

The setup of the plugin should be easy and intuitive.

It should help teachers as well as students with their organisation, documents / informations centralization and automate / simplify tedious workflows.

## Sources
- https://moodle.org
- https://cyberlearn.hes-so.ch
- https://renkulab.io
- https://about.gitlab.com