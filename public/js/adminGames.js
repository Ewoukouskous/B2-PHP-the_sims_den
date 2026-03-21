(function () {
    const modal = document.getElementById('deleteModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const modalGameName = document.getElementById('modalGameName');
    const deleteButtons = document.querySelectorAll('.admin-delete-btn');
    let currentGameId = null;
    // Open modal when delete button is clicked
    deleteButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            currentGameId = btn.getAttribute('data-game-id');
            const gameName = btn.getAttribute('data-game-name');
            modalGameName.textContent = gameName;
            modal.classList.remove('hidden');
        });
    });
    // Close modal on cancel
    cancelBtn.addEventListener('click', function () {
        modal.classList.add('hidden');
        currentGameId = null;
    });
    // Close modal on outside click
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.classList.add('hidden');
            currentGameId = null;
        }
    });
    // Confirm deletion
    confirmDeleteBtn.addEventListener('click', function () {
        if (currentGameId === null) {
            return;
        }
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'deleteGame';
        const gameIdInput = document.createElement('input');
        gameIdInput.type = 'hidden';
        gameIdInput.name = 'gameId';
        gameIdInput.value = currentGameId;
        form.appendChild(actionInput);
        form.appendChild(gameIdInput);
        document.body.appendChild(form);
        form.submit();
    });
})();
