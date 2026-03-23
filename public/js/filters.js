const filterPC = document.getElementById('filter1');
const filterConsole = document.getElementById('filter2');
const filterSmartphone = document.getElementById('filter3');
const searchInput = document.getElementById('headerSearchInput');
const gameCards = document.querySelectorAll('.game-card');

function applyFilters() {
    const pcChecked = filterPC ? filterPC.checked : false;
    const consoleChecked = filterConsole ? filterConsole.checked : false;
    const smartphoneChecked = filterSmartphone ? filterSmartphone.checked : false;
    const searchText = searchInput ? searchInput.value.toLowerCase() : '';

    // Filter games based on selected checkboxes and search text
    gameCards.forEach(card => {
        const cardType = card.getAttribute('data-type');
        // Find the game name within the card, usually in the blue text box
        const gameName = card.querySelector('div[class*="text-[#3769a9]"]')?.textContent.toLowerCase() || "";

        let typeMatch = false;
        // If no type filters are checked, all types match
        if (!pcChecked && !consoleChecked && !smartphoneChecked) {
            typeMatch = true;
        } else {
            if (pcChecked && cardType === 'pc') typeMatch = true;
            if (consoleChecked && cardType === 'console') typeMatch = true;
            if (smartphoneChecked && cardType === 'smartphone') typeMatch = true;
        }

        const nameMatch = gameName.includes(searchText);

        card.style.display = (typeMatch && nameMatch) ? 'block' : 'none';
    });
}

if (filterPC) filterPC.addEventListener('change', applyFilters);
if (filterConsole) filterConsole.addEventListener('change', applyFilters);
if (filterSmartphone) filterSmartphone.addEventListener('change', applyFilters);
if (searchInput) searchInput.addEventListener('input', applyFilters);

// Initial apply in case there's something in the search bar on load
applyFilters();

