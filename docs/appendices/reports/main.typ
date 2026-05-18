#import "@preview/basic-report:0.4.0": *

#show: it => basic-report(
  doc-category: "GitLab integration with Moodle appendix",
  doc-title: "Meeting Reports",
  author: "Jouve Léonard",
  affiliation: "HEIG-VD",
  logo: image("images/Heig.svg"),
  language: "en",
  compact-mode: false,
  it
)

= Weekly meeting reports

== 20.02.2026
Discussed:
- specifications review
- write the bachelor thesis in english
- weekly reports
    - what has been discussed
    - what should be done for the next time

Planning:
- correct specifications reviews
- Gantt chart
- ask Pierre Donini weather the thesis can be done in english
- host GitLab / Moodle instance

== 27.02.2026
Discussed:
- the thesis will be written in english
- possible integration with Eval / Gaps
- Gantt chart review
- no need for a self-hosted GitLab instance for now 
- possible Kubernetes deployment
- store documents on GitHub

Planning:
- state of the art
- choose best suited technologies

== 06.03.2026
Discussed:
- use Moodle version 4.5
- focus on creating a plugin for Moodle not Cyberlearn
- Crunch => 1 week break
- the integration should avoid as much as possible being based on other plugins

Planning:
- Moodle migration from version 5.1 to 4.5
- list useful features on existing solutions
- list features our solution should implement with priorities

== 13.03.2026
CRUNCH

== 20.03.2026
Discussed:
- possible integration with grading assessments tools (example: Gradescope, ANS, Eval)
- misunderstand, stay on latest stable Moodle version 5.1
- comparison with GitHub classroom
  - issue: cannot reuse groups / repos for consecutive assignments

Planning:
- Kanban board
- detail features in a document
- detail state of the art in a document

== 27.03.2026
Discussed:
- workflow for documents review
- state of the art
- list of features our plugin should permit

Planning:
- POC

== 03.04.2026
Cancelled

== 10.04.2026
Holidays

== 17.04.2026
Discussed:
- POC presentation
- IaC / CaC presentation

Planning:
- GitLab PHP REST API Client

== 24.04.2026
Discussed:
- GitLab Client
- limitation: GitLab API only permits sub-group creation
  - will require each teacher to create a group manually on the GitLab platform

Planning:
- intermediary report

== 01.05.2026
Cancelled