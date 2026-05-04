#import "@preview/basic-report:0.4.0": *

#show: it => basic-report(
  doc-category: "Bachelor thesis",
  doc-title: "Integrating GitLab with Moodle",
  author: "Jouve Léonard",
  affiliation: "HEIG-VD",
  logo: image("images/Heig.svg"),
  language: "en",
  compact-mode: false,
  it
)

= Introduction

In higher education, digital platforms play a central role in courses organization, learning materials distribution, and academic activities management. Learning Management Systems (LMS) such as Moodle are widely adopted to structure the educational experience for both teachers and students. At the same time, in computer science and related fields, source code management platforms like GitLab have become essential tools for distributing assignments, enabling collaboration, and evaluating practical work.

However, these two types of platforms, while complementary, operate independently. This separation leads to fragmented workflows: teachers must manually manage repositories, student groups, and assessment processes on GitLab, while maintaining course-related information on Moodle. For students, this results in scattered resources and information across multiple platforms, which can lead to confusion and reduces overall learning experience.

== Motivation

The lack of integration between LMS platforms and Git repository hosting services introduces several challenges. Teachers often face repetitive and time-consuming tasks such as creating repositories for each student or group, managing access permissions, tracking contributions, and consolidating evaluation data. These manual processes are not only inefficient but also prone to errors, especially in courses with a large number of students.

Students, on the other hand, must navigate between multiple platforms to access assignments, submit their work, collaborate, and receive feedback. This fragmentation can create confusion and reduce engagement, as important information such as deadlines, grades, and project updates is not centralized.

Existing solutions partially address these issues but remain limited. Some are proprietary and lack flexibility, while others are tightly coupled to specific platforms such as GitHub. Moreover, they are often not deeply integrated into LMS environments, requiring additional tools or workflows that increase complexity rather than reduce it. This situation highlights the need for an open, flexible, and LMS-native solution that seamlessly integrates Git-based workflows into the educational ecosystem.

#pagebreak()

== Solution

To address these challenges, this project proposes the development of an extensible integration between Moodle and GitLab. The solution consists of a custom Moodle plugin that communicates with the GitLab API through a dedicated client implemented in PHP. This integration will enable direct interaction with GitLab features from within the Moodle interface.

The integration aims automate workflows involved in programming assignments. This includes the creation and management of repositories, automatic setup of student groups, synchronization of project data, and integration of feedback and grading mechanisms. By embedding these functionalities into Moodle, both teachers and students benefit from a unified platform where all relevant information and actions are accessible.

From an architectural perspective, the solution is designed to be modular and extensible. While initially focused on GitLab, the system will be structured in a way that allows future support for other Git repository hosting services. This ensures long-term adaptability and encourages community contributions.

= Platforms

== Moodle

#underline[Moodle] @moodle is an open-source Learning Management System (LMS) widely used by universities and educational institutions to organize courses, distribute learning materials, and manage academic activities. It provides a modular structure based on courses, activities, and plugins, allowing institutions to adapt the platform to their needs.

Moodle most valuable strengths are:
- Course organization
- Assignment submission and grading
- Extensive plugin ecosystem

#figure(
  image("images/moodle_course.png"),
  caption: [Moodle course]
)

In many institutions, Moodle serves as the central hub for teaching activities, including document sharing, assessment, and student tracking. For example, platforms like Cyberlearn (used in HES-SO) are built on top of Moodle and extend its functionality for institutional needs.

Moodle is one of the most widely adopted LMS platforms globally, used by thousands of universities and schools. Its open-source nature has contributed to a large ecosystem of plugins and integrations. However, its extensibility often requires custom development for advanced workflows.

The Moodle core is written in PHP @php. Thus our Moodle plugin code must also be written in PHP.

A well-known equivalent alternative is #underline[Canvas] @canvas.

== GitLab

#underline[GitLab] @gitlab is an open source Git repository management platform that provides tools for source code and project management and continuous integration/continuous deployment.

The most used features are:

- Git repository source code hosting and management
- Merge requests and code review workflows
- Issue tracking and project management
- CI/CD pipelines for automated testing and deployment
- Extensibility through integration via Webhooks and REST/GraphQL APIs

#figure(
  image("images/gitlab_repo.png"),
  caption: [GitLab repository]
)

GitLab is widely used in both industry and education due to its full DevOps lifecycle integration and strong automation capabilities. In academic contexts, it is commonly used for programming assignments, enabling students to collaborate and submit code while allowing teachers to review and evaluate contributions efficiently.

GitHub is probably the most popular Git hosting platform.

== Moodle academy
#underline[Moodle academy] is the academy platform of Moodle.
It is used as the learning hub for the Moodle community.
Several online courses and trainings are available for free.
This will be our main resource with the Moodle documentation for creating the plugin.

#pagebreak()

= State of the art <state_of_the_art>

There are 2 faces of this project:
- GitLab interactions through REST API
- Moodle plugin creation for the UI

We must link both to have a working integration.

== GitLab Client

On the GitLab API side, there a multiple existing GitLab Client solutions.
As our Moodle plugin will be written in PHP #underline[GitLab PHP API Client] @gitlab_php_api_client seems to be the best suited implementation.
It is a well known PHP GitLab client with about 1k stars and 450 forks on GitLab.
It implements the full GitLab API v4 specification and is open source.
There are no relevant issues open in the repository.
This will serve as the main base for our GitLab Client implementation.
Some other notable implementations would be:
- #underline[GitLab Tools] @gitlab_tools
- #underline[GitLab Haskell] @gitlab_haskell
- #underline[Classmoji] @classmoji

Moodle plugins are also meant to be self contained. It should not depend on any external package. This is the main reason while we are going to implement our own GitLab client by taking inspiration on existent ones.

== Moodle Plugin

For the Moodle plugin, there are no widely adopted full-featured integration between Moodle and any Git repository hosting platform.
I listed some existing plugins to overview:
- #underline[Webhooks] @plugin_webhooks adds Webhook features to the Moodle system. This could be used as inspiration for an integration with GitLab Webhooks.
- #underline[Board] @plugin_board adds a Kanban Board module. This could be used as inspiration for creating custom modules.
- #underline[CourseToCal] @plugin_coursetocal allows students to see courses in the Moodle calendar. This could be used as inspiration to add events to the calendar.
- #underline[GitHubRepo] @plugin_githubrepo allows GitHub ZIP archives download. This could be used as an entry point for an integration with a Git repository hosting platform.

#pagebreak()

== Integrations

There are some existing Git repository hosting platform integrations with LMS.

=== Zohoflow
#underline[Zohoflow] @zohoflow is a closed source and premium platform.
It can interact with GitLab Merge Requests, Issues and Commits.
It can also interact with Canvas LMS Courses, Events and Enrollments.
The problem is its closed source state and only allow simple and non-customable interactions between the 2 platforms.

=== Classmoji
#underline[Classmoji] @classmoji is an open source LMS for teaching computer science.
It is not widely adopted yet but is created with GitHub integration in the core.
It claims to integrate GitHub Classroom with the LMS directly and add some additional features.
This will be used as an example of integration between a LMS and Git repository hosting platform.

=== Canvas GitLab Integration
#underline[Canvas GitLab Integration] @canvas_gitLab_integration is a research project aiming to enrich learning processes.
It is a project in 3 parts:
- GitLab API
- Moodle API
- Moodle - GitLab integration API
All 3 projects are open source and written in Haskell.
There is also a report analyzing how this would improve the learning process.
This work will be used for its analytical point of view on the subject and help with the structure of an integration between 2 modules.

=== GitHub Classroom
#underline[GitHub Classroom] is an assignment management platform integrated with GitHub.
It brings automation to the teachers grading assessments on code projects.
This solution is widely used but only for GitHub which is closed source.
Another drawback is that it is not directly integrated with the school LMS platform.
It adds another layer of complexity between LMS and Git hosting platform.
The workflows teachers will be able to automate with our plugin will be heavily inspired by it.

Existing solutions either lack deep LMS integration, are proprietary, or are limited to GitHub.
There is currently no open-source, Moodle-native integration with GitLab supporting automated assignment workflows, which motivates this project. The goal of this project is to automate Git-based assignment workflows and centralize repository-related information directly within Moodle.

= Specifications <spec>

== Objectives

The goal of this project is to have an extensible integration between GitLab and Moodle.
It would include an API automating all mentioned issues accessible from a Moodle integration plugin and thus improve teachers and students workflows.

To achieve this, this project aims to:
- Review the state of the art and identify the features and functionalities the project will include based on possibilities and limitations within the GitLab and Moodle APIs (for example: group management, project creation, feedbacks and grading)
- Evaluate and identify most suitable architecture for the implementation:
- The API should support multiple Git repository hosting services
- The codebase should be modular, open to modifications and improvements
- Implement, develop and deploy the integration between Moodle and GitLab by following the DevOps principles
- Validate and test (optionally with representative users)

== Deliverables

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

== Expected result

This project should provide an integration between Moodle and GitLab and potentially any other Git repository hosting service.

The setup of the plugin should be easy and intuitive.

It should help teachers as well as students with their organization, documents / informations centralization and automate / simplify tedious workflows.

It should be released as open source to allow others to access, use, and build upon the work.

= Features

The integration will be made with a Moodle plugin.
The plugin will add an [activity module](https://docs.moodle.org/501/en/Activities) named `GitLab`.

#figure(
 image("images/module.svg"),
  caption: [GitLab module on Moodle course page]
)

== Teachers

Teachers will be able to create such module and set different properties such as:
- name
- description
- instructions
- due date
- group size
- reviewer

Once defined, the teacher have access to a GitLab repository used as a template for students repositories.

Each students group will have its own GitLab repository.
From Moodle, teachers will be able to:
- see a list of students groups
- manage groups
- access each group repository
- see last CI jobs results
- see individual participation insights
- download source code
- see wether the group has already been graded

#figure(
 image("images/teacher_dashboard.svg"),
  caption: [Teacher dashboard scaffold]
)

== Students

On the other side, students will be able to:
- view groups list
- join group
- create group

#figure(
  image("images/student_groups.svg"),
  caption: [Student groups list scaffold]
)

Once a student has joined a group, he should be able to:
- access his repository
- see practical work instructions under the GitLab issues
- see the grade / feedbacks on GitLab MR and on Moodle
- receive a notification after grading
- view the practical work due date in the Moodle calendar

#figure(
  image("images/student_group.svg"),
  caption: [Student group scaffold]
)

== Automation

Once a `GitLab` module is created, a GitLab group as well as a GitLab repository will automatically be created.

The group will contain all repositories related to the practical work.

The repository will be used as a template for students repositories and will only be accessible by teachers and reviewers.

Teachers and reviewers will be able to commit the initial project to the `main` branch and the solution to the `solution` branch.

Once a modification is made to the template repository, a merge request is created to update all the existent student repositories.

For each student group creation, a repository is created with correct visibility and assignments.

A merge request is created and will be used for grading.

An issue is created as well with the practical work instructions.

Grades and feedbacks are visible by students on Moodle after being published in the correction merge request by reviewers.

After the practical work due date, the template `solution` branch is published with a merge request to all students.

#figure(
  image("images/gitlab_workflow.svg"),
  caption: [GitLab practical work structure]
)

== Other

Some custom integrations could be done with internal HES-SO tools `Gaps` and `Eval` but are to be determined.

Another possible integration would be with a grade assessments platform such as `Gradescope` or `ANS`, but it means an integration with a proprietary platform which could diverge based on teachers preferences.

== List

Graded features list with priorities from *1* to *4*:
- *1* = highest priority
- *4* = lowest priority

=== GitLab Group
- GitLab template | *1*
    - commits to the template trigger MR to all repositories => webhook | *1*
    - solution branch => MR to all repo after due date | *2*
- Sub repo (based on template) per team (correct visibility) | *1*
- Create issue (instructions) + merge request | *1*

=== Teacher Dashboard
- Create new project | *1*
    - name
    - description
    - instructions
    - due date
    - group size
    - assign reviewer
- Group list | *1*
- Direct access to repo | *1*
- CI jobs results | *1*
- Commits participations / lines | *2*
- Download source button | *2*
- Status (graded / non-graded) | *1*

=== Student Dashboard
- Join group => init group repo | *1*
    - Automatically add project due date to Moodle calendar | *1*
- Link to repo | *1*
- Grade / feedbacks (from MR) | *1*
    - Notification after grading | *2*

=== Support multiple Git hosting platform
- GitLab | *1*
- GitHub | *3*
- Must use interfaces to allow other platforms in the future | *1*

=== Eval
- Activity module | *4*
- Link | *4*
- Grade | *4*
- Solution / feedback after evaluation (link) | *4*

=== Gaps
- Include course calendar to Moodle calendar | *3*
- Retrieve unit description from Gaps | *3*

= Progression

So far, the bachelor thesis has started for about 3 months, with approximately 8 hours per week dedicated to it.
The same workload will continue until mid-June. After that, I will work on the project full-time for about 2 months before the final presentation.

These first months were mainly focused on establishing the requirements, the planning and the feasible features for the project.
The upcoming time will focus more on the implementation and testing of the integration.

My progression so far has made me work on the following:
- defining the #underline[specifications] @spec for the project
- setting a #underline[planning] @appendix_planning of the targeted advancement and due dates
- research and evaluation of existing (partial) solutions / alternatives and writing a current #underline[state of the art] @state_of_the_art
- familiarizing with the language (PHP) / the APIs (Moodle through the #underline[Moodle Academy] @moodle_academy platform among others and GitLab)
- creating a simple #underline[docker deployment] @moodle_docker of Moodle available publicly on GitHub
- developing a POC to demonstrate a simple integration of GitLab with Moodle
- developing a GitLab REST API client in PHP inspired by existing alternatives like #underline[GitLab PHP API Client]@gitlab_php_api_client
- writing the intermediary thesis report detailing my current advancement.


== Proof Of Concept

As a #underline[POC] @poc of integrating GitLab with Moodle, i decided to create a Moodle module plugin. A module plugin adds a new type of resource a teacher can add to a course.

#figure(
  image("images/poc_add_module.png"),
  caption: [Modal for adding resources has a new *GitLab* type of module]
)

While editing this resource, the teacher must define a GitLab API token.
This will be used to perform API calls and create / configure the GitLab resources like repositories.

Once created, the students can open the module created and view a button which triggers the creating of a GitLab repository. They can also see a list of existing repositories in the GitLab group with a direct link to them.

#figure(
  image("images/poc_module.png"),
  caption: [GitLab module view]
)

#figure(
  image("images/poc_gitlab_repo.png"),
  caption: [Resulting repository created on the selected GitLab group]
)

I added #underline[Continuous Deployment] @cd to provide a full featured reproducible development and testing environment.

I created an Infrastructure as Code using #underline[Terraform] @terraform allowing to create an AWS EC2 instance.

To setup the instance, I added Configuration as Code with #underline[Ansible] @ansible playbooks. It runs a Moodle instance and places it behind the #underline[Authentik] @authentik authenticity provider. It uses the #underline[Traefik] @traefik reverse proxy to manage communication between all applications and the ingress.

I finally added a GitHub action to automate the updates after each push on the main branch of the project.

== Workflow

The development workflow is based on a structured Git feature branch strategy to ensure isolated and traceable changes. Each feature is implemented in a separate Git branch, allowing for review before integration into the main codebase.
The task management is handled through a #underline[Kanban board] @kanban. Items are organized by priority and annotated with their dependencies. This ensures critical tasks are addressed first and visibility over blocking / blocked tasks.

= Continuation

This intermediary report summarizes the current progress of the project. The project has been successfully set up, and a consistent workflow has been established.

The project is progressing as expected at this stage. The next phase will focus on the implementation of the solution.

The continuation of the work is guided by the #underline[Kanban board] @appendix_board and the #underline[Gantt chart] @appendix_planning.

The next feature to be implemented is the teacher’s course and module configuration pages.

#pagebreak()

= Appendices

== Planning <appendix_planning>

See #underline[gantt.xlsx]

== Weekly meeting reports <appendix_reports>

See #underline[planning.pdf]

== Source code <appendix_code>
#underline[GitHub Repository] @code

== Kanban Board <appendix_board>
#underline[Kanban Board] @kanban


#bibliography("ref.yaml")