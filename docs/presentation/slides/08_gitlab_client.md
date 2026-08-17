# GitLab Client

<div class="text-lg opacity-85">Abstraction layer between Moodle and GitLab REST API</div>

<div class="mt-8 flex items-center gap-3 text-center">
  <div class="flex-1 p-4 rounded-2xl border border-orange-700 bg-orange-500/10">
    <div class="text-sm opacity-60">Moodle</div>
    Activity Plugin
  </div>
  <div class="text-2xl opacity-40">&rarr;</div>
  <div class="flex-1 p-4 rounded-2xl border border-green-700 bg-green-500/10">
    <div class="text-sm opacity-60">Entry Point</div>
    GitLab Client
  </div>
  <div class="text-2xl opacity-40">&rarr;</div>
  <div class="flex-[1.4] p-4 rounded-2xl border border-purple-700 bg-purple-500/10">
    <div class="text-sm opacity-60">Resource Clients</div>
    Groups, Projects, Users, Webhooks...
  </div>
  <div class="text-2xl opacity-40">&rarr;</div>
  <div class="flex-1 p-4 rounded-2xl border border-blue-700 bg-blue-500/10">
    <div class="text-sm opacity-60">GitLab</div>
    REST API
  </div>
</div>

<div class="grid grid-cols-[1.2fr_1fr] gap-5 mt-6">
  <div class="p-5 rounded-2xl border border-white/15 bg-white/5 font-mono text-sm leading-8 flex flex-col justify-center">
    <div><span class="opacity-50">$client = new <span class="text-orange-400">GitLabClient</span>($token);</span></div>
    <div><span class="opacity-50">$client-&gt;</span><span class="text-green-400">group()</span><span class="opacity-50">-&gt;create(...);</span></div>
    <div><span class="opacity-50">$client-&gt;</span><span class="text-blue-400">project()</span><span class="opacity-50">-&gt;fork(...);</span></div>
  </div>
  <div class="p-5 rounded-2xl border border-purple-700 bg-purple-500/10">
    <ul class="mt-2">
      <li>Provides high-level operations instead of raw HTTP requests</li>
      <li>Inspired by the GitLab PHP API Client library</li>
      <li>Resource clients mirror GitLab's API structure</li>
    </ul>
  </div>
</div>
