document.addEventListener('DOMContentLoaded', function () {
    const icons = document.querySelectorAll('.acfcb-icon');
    const input = document.getElementById('block_icon');
    const searchInput = document.getElementById('acfcb-icon-search');

    // Icon selection
    icons.forEach(icon => {
        icon.addEventListener('click', () => {
            icons.forEach(i => i.classList.remove('selected'));
            icon.classList.add('selected');
            input.value = icon.dataset.icon;
        });
    });

    // Live search
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.toLowerCase();

            icons.forEach(icon => {
                const iconName = icon.dataset.icon.toLowerCase();
                icon.style.display = iconName.includes(term) ? 'inline-block' : 'none';
            });
        });
    }
});
