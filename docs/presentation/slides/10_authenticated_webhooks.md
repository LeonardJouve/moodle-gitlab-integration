# Webhooks

<div class="text-lg opacity-85">GitLab-to-Moodle synchronization through signed events</div>

<div class="mt-8 flex items-center gap-3 text-center">
  <div class="flex-1 p-4 rounded-2xl border border-orange-700 bg-orange-500/10">
    <div class="text-sm opacity-60">GitLab</div>
    Push, issue, or merge request event
  </div>
  <div class="text-2xl opacity-40">&rarr;</div>
  <div class="flex-1 p-4 rounded-2xl border border-purple-700 bg-purple-500/10">
    <div class="text-sm opacity-60">Signed POST</div>
    Cryptographic signature
  </div>
  <div class="text-2xl opacity-40">&rarr;</div>
  <div class="flex-[1.3] p-4 rounded-2xl border border-blue-700 bg-blue-500/10">
    <div class="text-sm opacity-60">Moodle Endpoint</div>
    <code>/mod/gitlab/webhook.php</code>
  </div>
  <div class="text-2xl opacity-40">&rarr;</div>
  <div class="flex-1 p-4 rounded-2xl border border-green-700 bg-green-500/10">
    <div class="text-sm opacity-60">Verified</div>
    Dispatch event
  </div>
</div>

<div class="mt-5 p-4 rounded-2xl border border-white/15 bg-white/5 text-center font-mono">
  HMAC-SHA256(id.timestamp.body, module_secret)
</div>

<div class="grid grid-cols-3 gap-5 mt-5">
  <div class="p-4 rounded-2xl border border-purple-700 bg-purple-500/10">
    Per-Activity Secret
    <div class="mt-2 text-sm opacity-80">Random 32-byte key, stored encrypted in database.</div>
  </div>
  <div class="p-4 rounded-2xl border border-green-700 bg-green-500/10">
    Integrity Check
    <div class="mt-2 text-sm opacity-80">Computed signature compared with the received signature.</div>
  </div>
  <div class="p-4 rounded-2xl border border-blue-700 bg-blue-500/10">
    Replay Protection
    <div class="mt-2 text-sm opacity-80">Timestamps older than five minutes are rejected.</div>
  </div>
</div>

<div class="mt-4 text-sm text-center opacity-60">Only verified events can update templates, repositories, or submission status.</div>
