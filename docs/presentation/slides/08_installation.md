# Installation & Maintenance Footprint

<div class="grid grid-cols-3 gap-5 mt-7">
  <div class="p-5 rounded-2xl border border-purple-700 bg-purple-500/10">
    <div class="text-sm uppercase tracking-wide opacity-60">Installation</div>
    <div class="mt-2 text-xl font-semibold">2 Moodle plugins</div>
    <ul class="mt-3 pl-4 text-sm leading-relaxed opacity-80 list-disc">
      <li>Upload plugins ZIP from Moodle administration</li>
      <div>or</div>
      <li>Copy plugins folders to <code>mod/gitlab</code> and <code>customfield/field/gitlab</code></li>
    </ul>
  </div>

  <div class="p-5 rounded-2xl border border-green-700 bg-green-500/10 flex flex-col justify-center text-center">
    <div class="text-6xl font-semibold">0</div>
    <div class="mt-2 text-xl font-semibold">External dependencies</div>
    <div class="mt-2 text-sm opacity-70">Only Moodle's native APIs</div>
  </div>

  <div class="p-5 rounded-2xl border border-blue-700 bg-blue-500/10">
    <div class="text-sm uppercase tracking-wide opacity-60">Maintenance</div>
    <div class="mt-3 space-y-2 text-sm">
      <div><strong>3 tables</strong> · activity, groups, memberships</div>
      <div><strong>2 custom fields</strong> · GitLab token, GitLab username</div>
      <div><strong>1 public endpoint</strong> · signed webhook, no Moodle authentication</div>
    </div>
  </div>
</div>

<div class="mt-8 text-sm uppercase tracking-wide opacity-60">Access control based on existing Moodle roles</div>

<div class="grid grid-cols-3 gap-5 mt-3 text-sm">
  <div class="p-4 rounded-2xl border border-purple-700 bg-purple-500/10">
    <strong>Create activity</strong>
    <div class="mt-2 opacity-75">Manager · Editing teacher</div>
  </div>
  <div class="p-4 rounded-2xl border border-blue-700 bg-blue-500/10">
    <strong>Manage activity</strong>
    <div class="mt-2 opacity-75">Manager · Editing teacher · Teacher</div>
  </div>
  <div class="p-4 rounded-2xl border border-green-700 bg-green-500/10">
    <strong>Join groups</strong>
    <div class="mt-2 opacity-75">Student</div>
  </div>
</div>
