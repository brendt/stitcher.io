{{-- Step 1 --}}
<div class="php-home-step">
    <div class="php-home-step-label">Step 1 — Install</div>

    {{--OS Tabs--}}
    <div class="php-home-os-tab-list" role="tablist">
        <button class="php-home-os-tab active" data-os="mac" onclick="switchOs('mac')">macOS</button>
        <button class="php-home-os-tab" data-os="windows" onclick="switchOs('windows')">Windows</button>
        <button class="php-home-os-tab" data-os="linux" onclick="switchOs('linux')">Linux</button>
        <button class="php-home-os-tab" data-os="docker" onclick="switchOs('docker')">Docker</button>
    </div>

    <div data-os-panel="mac">
        <div class="php-home-install-block">
            <code>brew install php</code>
            <button class="php-home-copy-btn" onclick="copyCmd(this, 'brew install php')">copy</button>
        </div>
        <p class="text-sm text-(--ui-text-muted) mt-3">
            Don't have Homebrew yet? Install it at
            <a class="underline" href="https://brew.sh" target="_blank">brew.sh</a> — it's a one-liner.
        </p>
    </div>

    <div data-os-panel="windows" class="hidden">
        <div class="php-home-install-block">
            <code>winget install PHP.PHP</code>
            <button class="php-home-copy-btn" onclick="copyCmd(this, 'winget install PHP.PHP')">copy</button>
        </div>
        <p class="text-sm text-(--ui-text-muted) mt-3">
            winget comes built into Windows 10 and 11. Prefer a GUI? Try
            <a class="underline" href="https://laragon.org" target="_blank">Laragon</a> — a zero-config local environment.
        </p>
    </div>

    <div data-os-panel="linux" class="hidden">
        <div class="php-home-install-block">
            <code>sudo apt install php</code>
            <button class="php-home-copy-btn" onclick="copyCmd(this, 'sudo apt install php')">copy</button>
        </div>
        <p class="text-sm text-(--ui-text-muted) mt-3">
            For other PHP versions, add the
            <a class="underline" href="https://launchpad.net/~ondrej/+archive/ubuntu/php" target="_blank">ondrej/php</a> repository first.
        </p>
    </div>

    <div data-os-panel="docker" class="hidden">
        <div class="php-home-install-block">
            <code>docker run --rm -it php:8.4-cli php -a</code>
            <button class="php-home-copy-btn" onclick="copyCmd(this, 'docker run --rm -it php:8.4-cli php -a')">copy</button>
        </div>
        <p class="text-sm text-(--ui-text-muted) mt-3">
            Drops you straight into an interactive PHP shell — no install needed.
            Swap <code>php:8.4-cli</code> for any version on
            <a class="underline" href="https://hub.docker.com/_/php/tags" target="_blank">Docker Hub</a>.
        </p>
    </div>
</div>

{{-- Step 2 --}}
<div class="php-home-step">
    <div class="php-home-step-label">Step 2 — Verify</div>
    <div class="php-home-install-block">
        <code>php --version</code>
        <button class="php-home-copy-btn" onclick="copyCmd(this, 'php --version')">copy</button>
    </div>
    <p class="text-sm text-(--ui-text-muted) mt-3">
        You should see something like <code>PHP 8.4.x (cli)</code>. If you do — you're all set.
    </p>
</div>

<script>
    function switchOs(os) {
        document.querySelectorAll('[data-os-panel]').forEach(p => p.classList.add('hidden'));
        document.querySelector('[data-os-panel="' + os + '"]').classList.remove('hidden');
        document.querySelectorAll('.php-home-os-tab').forEach(t => t.classList.remove('active'));
        document.querySelector('[data-os="' + os + '"]').classList.add('active');
    }

    function copyCmd(btn, text) {
        navigator.clipboard.writeText(text).then(() => {
            btn.textContent = 'copied!';
            setTimeout(() => btn.textContent = 'copy', 1800);
        });
    }
</script>