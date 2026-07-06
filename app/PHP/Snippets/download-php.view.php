<div class="download-php">
    <div class="download-php-tabs" role="tablist">
        <button role="tab" data-tab="mac"     aria-selected="true">Mac</button>
        <button role="tab" data-tab="linux"   aria-selected="false">Linux</button>
        <button role="tab" data-tab="windows" aria-selected="false">Windows</button>
    </div>

    <div data-panel="mac">
        <pre><code><span class="hl-type">~</span> <span class="hl-keyword">brew</span> install php</code></pre>
    </div>

    <div data-panel="linux" hidden>
        <pre><code><span class="hl-type">~</span> <span class="hl-keyword">sudo apt</span> install php php-cli php-common</code></pre>
    </div>

    <div data-panel="windows" hidden>
        <pre><code><span class="hl-type">$</span> <span class="hl-keyword">winget</span> install PHP.PHP</code></pre>
    </div>
</div>

<script>
(function () {
    const container = document.currentScript.previousElementSibling;
    const buttons = container.querySelectorAll('[role="tab"]');
    const panels = container.querySelectorAll('[data-panel]');

    function activate(tab) {
        buttons.forEach(btn => btn.setAttribute('aria-selected', btn.dataset.tab === tab));
        panels.forEach(panel => panel.hidden = panel.dataset.panel !== tab);
    }

    buttons.forEach(btn => btn.addEventListener('click', () => activate(btn.dataset.tab)));

    const p = navigator.userAgentData?.platform ?? navigator.platform ?? '';
    if (/win/i.test(p)) activate('windows');
    else if (/linux/i.test(p)) activate('linux');
})();
</script>
