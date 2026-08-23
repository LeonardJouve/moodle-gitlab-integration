# GitLab Tokens

<div class="text-lg opacity-85">Secure persistence of sensitive values with encryption</div>

<div class="mt-10 flex items-center gap-3 text-center">
  <div class="flex-1 min-w-0 h-64 p-4 rounded-2xl border border-purple-700 bg-purple-500/10 flex flex-col justify-center">
    <div class="text-sm opacity-60">1</div>
    <strong class="mt-1">Configuration</strong>
    <div class="mt-3 text-sm leading-relaxed opacity-80">A teacher provides one GitLab token per Moodle course.</div>
  </div>
  <div class="text-2xl opacity-40">&rarr;</div>
  <div class="flex-1 min-w-0 h-64 p-4 rounded-2xl border border-green-700 bg-green-500/10 flex flex-col justify-center">
    <div class="text-sm opacity-60">2</div>
    <strong class="mt-1">Encryption</strong>
    <div class="mt-3 text-sm leading-relaxed opacity-80">Built-in Moodle encryption using <code>XSalsa20</code> and <code>Poly1305</code>.</div>
  </div>
  <div class="text-2xl opacity-40">&rarr;</div>
  <div class="flex-1 min-w-0 h-64 p-4 rounded-2xl border border-blue-700 bg-blue-500/10 flex flex-col justify-center">
    <div class="text-sm opacity-60">3</div>
    <strong class="mt-1">Encrypted Storage</strong>
    <div class="mt-3 text-sm leading-relaxed opacity-80">Only ciphertext is stored preventing token exposure from a database-only breach.</div>
  </div>
  <div class="text-2xl opacity-40">&rarr;</div>
  <div class="flex-1 min-w-0 h-64 p-4 rounded-2xl border border-orange-700 bg-orange-500/10 flex flex-col justify-center">
    <div class="text-sm opacity-60">4</div>
    <strong class="mt-1">API Use</strong>
    <div class="mt-3 text-sm leading-relaxed opacity-80">Moodle decrypts the token to authenticate GitLab API requests.</div>
  </div>
</div>
