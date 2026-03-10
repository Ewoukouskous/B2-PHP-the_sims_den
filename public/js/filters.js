const filterPC = document.getElementById('filter1');
const filterConsole = document.getElementById('filter2');
const filterSmartphone = document.getElementById('filter3');
const gameCards = document.querySelectorAll('.game-card');

function applyFilters() {
    const pcChecked = filterPC.checked;
    const consoleChecked = filterConsole.checked;
    const smartphoneChecked = filterSmartphone.checked;

    // If no filters are checked, show all games
    if (!pcChecked && !consoleChecked && !smartphoneChecked) {
        gameCards.forEach(card => {
            card.style.display = 'block';
        });
        return;
    }

    // Filter games based on selected checkboxes
    gameCards.forEach(card => {
        const cardType = card.getAttribute('data-type');
        let shouldShow = false;

        if (pcChecked && cardType === 'pc') shouldShow = true;
        if (consoleChecked && cardType === 'console') shouldShow = true;
        if (smartphoneChecked && cardType === 'smartphone') shouldShow = true;

        card.style.display = shouldShow ? 'block' : 'none';
    });
}

filterPC.addEventListener('change', applyFilters);
filterConsole.addEventListener('change', applyFilters);
filterSmartphone.addEventListener('change', applyFilters);

