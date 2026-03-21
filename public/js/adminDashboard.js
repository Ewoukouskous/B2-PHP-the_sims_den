(function () {
    const modal = document.getElementById('deleteModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const modalDeleteTitle = document.getElementById('modalDeleteTitle');
    const modalDeleteQuestion = document.getElementById('modalDeleteQuestion');
    const modalTargetName = document.getElementById('modalTargetName');

    const deleteGameButtons = document.querySelectorAll('.admin-delete-btn');
    const deleteUserButtons = document.querySelectorAll('.admin-delete-user-btn');

    let currentDeleteRequest = null;

    function openDeleteModal(config) {
        currentDeleteRequest = config;
        modalDeleteTitle.textContent = config.title;
        modalDeleteQuestion.textContent = config.question;
        modalTargetName.textContent = config.name;
        modal.classList.remove('hidden');
    }

    function closeDeleteModal() {
        modal.classList.add('hidden');
        currentDeleteRequest = null;
    }

    deleteGameButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            openDeleteModal({
                action: 'deleteGame',
                idField: 'gameId',
                idValue: btn.getAttribute('data-game-id'),
                title: 'EXIT GAME',
                question: 'Etes-vous sur de vouloir supprimer le jeu ?',
                name: btn.getAttribute('data-game-name') || ''
            });
        });
    });

    deleteUserButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            openDeleteModal({
                action: 'deleteUser',
                idField: 'userId',
                idValue: btn.getAttribute('data-user-id'),
                title: 'EXIT USER',
                question: 'Etes-vous sur de vouloir expulser cet utilisateur ?',
                name: btn.getAttribute('data-user-name') || ''
            });
        });
    });

    cancelBtn.addEventListener('click', closeDeleteModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeDeleteModal();
        }
    });

    confirmDeleteBtn.addEventListener('click', function () {
        if (currentDeleteRequest === null || !currentDeleteRequest.idValue) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = currentDeleteRequest.action;

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = currentDeleteRequest.idField;
        idInput.value = currentDeleteRequest.idValue;

        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    });
})();
