// TOC scroll spy
(function () {
    const links = Array.from(document.querySelectorAll('[data-toc-link]')) as HTMLElement[];
    if (!links.length) return;

    const sections = links
        .map(link => document.getElementById(link.getAttribute('href')!.slice(1)))
        .filter(Boolean) as HTMLElement[];

    function activate(link: HTMLElement) {
        links.forEach(l => {
            l.classList.remove('text-primary', 'font-bold');
            l.classList.add('text-gray-500', 'dark:text-gray-400');
        });
        link.classList.add('text-primary', 'font-bold');
        link.classList.remove('text-gray-500', 'dark:text-gray-400');
    }

    function onScroll() {
        const midpoint = window.innerHeight / 2;
        let current: HTMLElement | null = null;
        sections.forEach(section => {
            if (section.getBoundingClientRect().top <= midpoint) {
                current = section;
            }
        });
        if (current) {
            const link = links.find(l => l.getAttribute('href') === '#' + (current as HTMLElement).id);
            if (link) activate(link);
        }
    }

    window.addEventListener('scroll', onScroll, {passive: true});
    onScroll();
})();

// Dark mode toggle + auto (system) detection
(function () {
    const toggle = document.getElementById('dark-mode-toggle');
    const html = document.documentElement;
    const media = window.matchMedia('(prefers-color-scheme: dark)');

    function applyTheme(dark: boolean) {
        html.classList.toggle('dark', dark);
        localStorage.setItem('theme', dark ? 'dark' : 'light');
    }

    toggle?.addEventListener('click', () => applyTheme(!html.classList.contains('dark')));

    // Follow the system preference live, as long as the user hasn't picked a theme.
    // The initial load is handled by the inline script in x-php-base.
    media.addEventListener('change', event => {
        if (localStorage.theme) return;
        html.classList.toggle('dark', event.matches);
    });
})();

// Mobile chapter menu toggle
(function () {
    const toggle = document.getElementById('chapter-menu-toggle');
    const menu = document.getElementById('chapter-menu');
    if (!toggle || !menu) return;

    const openIcon = toggle.querySelector('[data-menu-open]');
    const closeIcon = toggle.querySelector('[data-menu-close]');

    function setOpen(open: boolean) {
        menu!.classList.toggle('hidden', !open);
        toggle!.setAttribute('aria-expanded', String(open));
        openIcon?.classList.toggle('hidden', open);
        openIcon?.classList.toggle('block', !open);
        closeIcon?.classList.toggle('hidden', !open);
        closeIcon?.classList.toggle('block', open);
    }

    toggle.addEventListener('click', () => setOpen(menu.classList.contains('hidden')));

    // Collapse the menu after picking a chapter
    menu.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setOpen(false)));
})();
