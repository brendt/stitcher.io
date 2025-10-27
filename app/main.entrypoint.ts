function toggleSearch() {
    const searchPopup = document.querySelector('#search-popup');
    const search = document.querySelector('#search');

    if (searchPopup.classList.contains('hidden')) {
        searchPopup.classList.remove('hidden');
        search.focus();
        search.select();
    } else {
        searchPopup.classList.add('hidden');
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key !== 'k') {
        return;
    }
    
    if (! e.metaKey && ! e.ctrlKey) {
        return;
    }
    
    e.preventDefault();
    e.stopPropagation();

    toggleSearch();
});

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') {
        return;
    }

    const searchPopup = document.querySelector('#search-popup');

    searchPopup.classList.add('hidden');
});

document.addEventListener('DOMContentLoaded', () => {
    const searchTrigger = document.querySelector('#search-trigger');

    searchTrigger.addEventListener('click', () => {
        toggleSearch();
    })

    const search = document.querySelector('#search');

    search.addEventListener('keydown', function (e) {
        if (! [13, 38, 40].includes(e.keyCode)) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();
        
        const results = document.querySelectorAll('.search-result');

        let selectedIndex = null;

        for (let i = 0; i < results.length; i++) {
            if (results[i].attributes.getNamedItem('data-selected')) {
                selectedIndex = i;
                break;
            }
        }

        if (e.keyCode === 13) {
            if (selectedIndex === null) {
                return;
            }

            window.location.href = results[selectedIndex].getAttribute('href');
            
            return;
        }

        let nextIndex = selectedIndex;

        if (selectedIndex === null) {
            nextIndex = 0;
        } else if (e.keyCode === 40) {
            nextIndex = selectedIndex + 1;
        } else if (e.keyCode === 38) {
            nextIndex = selectedIndex - 1;
        }

        if (nextIndex < 0) {
            nextIndex = results.length - 1;
        } else if (nextIndex >= results.length) {
            nextIndex = 0;
        }

        if (selectedIndex !== null) {
            results[selectedIndex].removeAttribute('data-selected');
        }
        
        results[nextIndex].setAttribute('data-selected', '');
    });

    document.getElementById('toggle-theme').addEventListener('click', () => {
        toggleDarkMode();
    });

    const searchPopup = document.querySelector('#search-popup');
    searchPopup.addEventListener('click', function (e) {
        if (e.target === searchPopup) {
            e.stopPropagation()
            e.preventDefault();
            toggleSearch();
        }
    });

});
