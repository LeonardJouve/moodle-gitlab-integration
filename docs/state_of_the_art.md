# State of the art

The project is an integration between Moodle and GitLab.
Thus there are 2 faces:
- GitLab interactions through REST API
- Moodle plugin creation for the UI

We must link both to have a working integration.

### GitLab Client

On the GitLab API side, there a multiple existing GitLab Client solutions.
As our Moodle plugin will be written in PHP [GitLab PHP API Client](https://github.com/GitLabPHP/Client) seems to be the best suited implementation.
It is a well known PHP GitLab client with about 950 stars and 450 forks on GitLab.
It implements the full GitLab api v4 specification and is open source.
There are no relevant issues open in the repository.
This will be the main base for our GitLab Client implementation.
Some other notable implementations would be:
- https://gitlab.fhnw.ch/gitlab-tools/gitlab-tools
- https://gitlab.com/robstewart57/gitlab-haskell
- https://github.com/classmoji/classmoji

### Moodle Plugin

For the Moodle plugin, there are no widely adopted full-featured integration between Moodle and any Git repository hosting platform.
I listed some existing plugins to overview:
- [Webhooks](https://moodle.org/plugins/local_webhooks) adds WebHook features to the Moodle system. This could be used as inspiration for an integration with GitLab WebHooks.
- [Board](https://moodle.org/plugins/mod_board) adds a Kanban Board module. This could be used as inspiration for creating custom modules.
- [Course to calendar](https://moodle.org/plugins/local_coursetocal) allows students to see courses in the Moodle calendar. This could be used as inspiration to add events to the calendar.
- [Github](https://moodle.org/plugins/repository_github) allows Github ZIP archives download. This could be used as an entry point for an integration with a Git repository hosting platform.

### Integration

There are some existing Git repository hosting platform integrations with LMS.

#### Zohoflow
[Zohoflow](https://www.zohoflow.com/en-ca/apps/canvas-lms/integrations/gitlab) is a closed source and premium platform.
It can interact with GitLab Merge Requests, Issues and Commits.
It can also interact with Canvas LMS Courses, Events and Enrollments.
The problem is its closed source state and only allow simple and non-customizable interactions between the 2 platforms.

#### Classmoji
[Classmoji](https://github.com/classmoji/classmoji) is an open source LMS for teaching computer science.
It is not widely adopted yet but is created with Github integration in the core.
It claims to integrate Github Classroom with the LMS directly and add some additional features.
This will be used as an example of integration between a LMS and Git repository hosting platform.

#### Canvas GitLab Integration
[Canvas GitLab Integration](https://figshare.com/articles/code/Canvas-GitLab_Integration_Framework_as_presented_in_b_Integrating_Canvas_and_GitLab_to_Enrich_Learning_Processes_b_/24916716) is a research project aiming to enrich learning processes.
It is a project in 3 parts:
- GitLab API
- Moodle API
- Moodle - GitLab integration API
All 3 projects are open source and written in Haskell.
There is also a report analyzing how this would improve the learning process.
This work will be used for its analytical point of view on the subject and help with the structure of an integration between 2 modules.

### Github Classroom
[Github Classroom](https://classroom.github.com/) is an assignment management platform integrated with Github.
It brings automation to the teachers grading assessments on code projects.
This solution is widely used but only for Github which is closed source.
Another drawback is that it is not directly integrated with the school LMS platform.
It adds another layer of complexity between LMS and Git hosting platform.
The workflows teachers will be able to automate with our plugin will be heavily inspired by it.

### Moodle academy
[Moodle academy](https://moodle.academy/) is the academy platform of Moodle.
It is used as the learning hub for the Moodle community.
Several online courses and trainings are available for free.
This will be our main resource with the Moodle documentation for creating the plugin.

Existing solutions either lack deep LMS integration, are proprietary, or are limited to GitHub.
There is currently no open-source, Moodle-native integration with GitLab supporting automated assignment workflows, which motivates this project. The goal of this project is to automate Git-based assignment workflows and centralize repository-related information directly within Moodle.