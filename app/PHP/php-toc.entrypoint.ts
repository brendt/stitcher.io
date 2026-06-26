(function () {
    const links = Array.from(document.querySelectorAll('[data-toc-link]'));
    if (!links.length) return;

    const sections = links.map(link => document.getElementById(link.getAttribute('href').slice(1))).filter(Boolean);

    function activate(link) {
        links.forEach(l => {
            l.classList.remove('text-primary', 'font-bold');
            l.classList.add('text-gray-500');
        });
        link.classList.add('text-primary', 'font-bold');
        link.classList.remove('text-gray-500');
    }

    function onScroll() {
        const midpoint = window.innerHeight / 2;
        let current = null;
        sections.forEach(section => {
            if (section.getBoundingClientRect().top <= midpoint) {
                current = section;
            }
        });
        if (current) {
            const link = links.find(l => l.getAttribute('href') === '#' + current.id);
            if (link) activate(link);
        }
    }

    window.addEventListener('scroll', onScroll, {passive: true});
    onScroll();
})();