---
theme: default
title: Integrating GitLab with Moodle
author: Jouve Léonard — HEIG-VD
transition: slide-left
colorSchema: dark
---

# Integrating GitLab with Moodle

<div class="mt-8 text-xl opacity-80">
Bachelor Thesis — HEIG-VD
</div>

<div class="mt-2 text-lg">
Jouve Léonard
</div>

<div class="mt-20 text-sm opacity-50">
Improving developer workflows in computer science education
</div>

---

# The Problem

<div class="text-lg opacity-85 max-w-4xl">
Moodle and GitLab are both heavily used in computer science education, but operate independently.  
This separation creates fragmented workflows for teachers and students.
</div>

<div class="grid grid-cols-2 gap-6 mt-10">

<div class="p-5 rounded-2xl border border-purple-700 bg-purple-500/10">

### Teachers

- Create repositories manually
- Configure GitLab permissions by hand
- Manage multiple platforms

</div>

<div class="p-5 rounded-2xl border border-purple-700 bg-purple-500/10">

### Students

- Inconsistent workflows between courses
- Switch between LMS and VCS
- Grades, deadlines, repositories in different places

</div>

</div>

<div class="mt-8 p-5 rounded-2xl border border-red-800 bg-red-500/10 text-red-200">

Current solutions are either **proprietary**, **GitHub-focused**, or **poorly integrated with Moodle**.

</div>

---

# Proposed Solution

<div class="text-lg opacity-85 max-w-4xl">
A native Moodle plugin that integrates directly with GitLab through a dedicated PHP API client
</div>

<div class="mt-8 flex justify-center">
<img src="./architecture.svg" alt="architecture" class="rounded-xl shadow-xl w-[75%]" />
</div>

<div class="grid grid-cols-3 gap-5 mt-10">

<div class="p-5 rounded-2xl border border-purple-700 bg-purple-500/10">

### Automation

Create and manage GitLab resources from Moodle

</div>

<div class="p-5 rounded-2xl border border-purple-700 bg-purple-500/10">

### Unified Experience

Centralized assignments, repositories, grades, and feedback

</div>

<div class="p-5 rounded-2xl border border-purple-700 bg-purple-500/10">

### Extensible Design

Modular architecture allowing future support for other VCS providers

</div>

</div>

---

# State of the Art

<div class="text-lg opacity-85 mb-8">
No open-source solution currently provides a complete Moodle-native integration with GitLab
</div>

<div class="rounded-2xl overflow-hidden border border-white/10">

<style>
th, td {
  text-align: center;
  vertical-align: middle;
}
</style>

| Solution | Open Source | Moodle Native | GitLab Support |
|---|---|---|---|
| GitHub Classroom | ❌ | ❌ | ❌ |
| Zohoflow | ❌ | ❌ | ✅ |
| Classmoji | ✅ | ❌ | ❌ |
| Canvas Gitlab Integration | ✅ | ❌ | ✅ |
| **This Project** | ✅ | ✅ | ✅ |

</div>

<div class="mt-5 text-sm opacity-60">
The project aims to fill this gap with a native fully integrated and extensible open-source solution
</div>

---

# Proof of Concept

<div class="text-lg opacity-85 mb-8">
A Moodle module plugin to demonstrate the integration idea.
</div>

<div class="grid grid-cols-2 gap-8">

<div class="p-5 rounded-2xl border border-purple-700 bg-purple-500/10">

### Features

- Add a GitLab activity module
- Set a GitLab API token per course
- Automatically create groups / repositories for students
- Access repositories from Moodle
- Display created repositories list

</div>

<div class="p-5 rounded-2xl border border-purple-700 bg-purple-500/10">

### Infrastructure as Code

- **Terraform** for infrastructure provisioning
- **Ansible** for automated configuration
- **Authentik** for authentication
- **GitHub Actions** for continuous deployment

</div>

</div>

---

# Project Progress

<div class="grid grid-cols-2 gap-8 mt-4">

<div class="p-5 rounded-2xl border border-green-700 bg-green-500/10">

### Completed

- Project specifications and planning
- State-of-the-art research
- Moodle proof-of-concept module plugin
- Cloud infrastructure
- GitLab REST API PHP client
- Intermediate thesis report

</div>

<div class="p-5 rounded-2xl border border-blue-700 bg-blue-500/10">

### Next Steps

- Teacher dashboard
- Student group selection / overview
- Advanced GitLab automation (webhooks, merge requests)

</div>

</div>

<div class="mt-10 p-5 rounded-2xl border border-white/10 bg-white/5 text-center text-lg">

The technical foundation is complete.  
The project is now entering its main implementation phase.

</div>

---

<div class="h-full flex items-center justify-center">

<h1>Demonstration</h1>

</div>