window.adsEnabled = true;

const placeholders = document.querySelectorAll('.sneaky-placeholder');

for (let placeholder of placeholders) {
    placeholder.classList.remove('shown');
}
