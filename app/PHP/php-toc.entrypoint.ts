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

// Dark mode toggle
(function () {
    const toggle = document.getElementById('dark-mode-toggle');
    const html = document.documentElement;

    function applyTheme(dark: boolean) {
        html.classList.toggle('dark', dark);
        localStorage.setItem('theme', dark ? 'dark' : 'light');
    }

    toggle?.addEventListener('click', () => applyTheme(!html.classList.contains('dark')));
})();
