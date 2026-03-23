(function () {
    var modal = null;
    var playtimeInput = null;
    var cancelBtn = null;
    var confirmBtn = null;
    var currentForm = null;

    function closeModal() {
        if (!modal) {
            return;
        }
        modal.classList.add('hidden');
        currentForm = null;
    }

    function ensureModal() {
        if (modal) {
            return;
        }

        modal = document.createElement('div');
        modal.id = 'favoritePlaytimeModal';
        modal.className = 'hidden fixed inset-0 flex items-center justify-center z-50 p-4';
        modal.style.backgroundColor = 'rgba(10, 20, 35, 0.16)';
        modal.style.backdropFilter = 'blur(4px)';

        var panel = document.createElement('div');
        panel.className = 'bg-white rounded-lg shadow-2xl p-8 max-w-sm w-full border-4 border-gray-300';

        var wrapper = document.createElement('div');
        wrapper.className = 'text-center space-y-6';

        var title = document.createElement('h2');
        title.className = 'text-3xl font-bold text-blue-600 tracking-wide';
        title.textContent = 'AJOUT FAVORI';

        var questionBlock = document.createElement('div');
        questionBlock.className = 'space-y-3';

        var question = document.createElement('p');
        question.className = 'text-lg font-bold text-gray-800';
        question.textContent = "Combien d'heures de jeu ?";

        playtimeInput = document.createElement('input');
        playtimeInput.type = 'number';
        playtimeInput.min = '0';
        playtimeInput.step = '1';
        playtimeInput.value = '0';
        playtimeInput.className = 'w-full rounded-full border-2 border-[#3769a9] px-4 py-2 text-center text-[#3769a9] font-bold outline-none';

        var actions = document.createElement('div');
        actions.className = 'flex items-center justify-end gap-6 pt-6';

        cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'w-16 h-16 flex items-center justify-center rounded-full bg-white border-4 border-blue-500 text-blue-500 font-bold text-3xl shadow-lg hover:bg-blue-100 transition duration-200 ease-in-out hover:scale-110';
        cancelBtn.textContent = 'X';

        confirmBtn = document.createElement('button');
        confirmBtn.type = 'button';
        confirmBtn.className = 'w-16 h-16 flex items-center justify-center rounded-full bg-white border-4 border-green-500 text-green-500 font-bold text-3xl shadow-lg hover:bg-green-100 transition duration-200 ease-in-out hover:scale-110';
        confirmBtn.textContent = 'V';

        actions.appendChild(cancelBtn);
        actions.appendChild(confirmBtn);

        questionBlock.appendChild(question);
        questionBlock.appendChild(playtimeInput);

        wrapper.appendChild(title);
        wrapper.appendChild(questionBlock);
        wrapper.appendChild(actions);

        panel.appendChild(wrapper);
        modal.appendChild(panel);
        document.body.appendChild(modal);

        cancelBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        confirmBtn.addEventListener('click', function () {
            if (!currentForm) {
                closeModal();
                return;
            }

            var playtimeValue = (playtimeInput.value || '').trim();
            if (!/^\d+$/.test(playtimeValue)) {
                window.alert('Veuillez saisir un nombre entier positif.');
                return;
            }

            var hiddenPlaytimeInput = currentForm.querySelector('input[name="playtimeHours"]');
            if (hiddenPlaytimeInput) {
                hiddenPlaytimeInput.value = playtimeValue;
            }

            var formToSubmit = currentForm;
            closeModal();
            formToSubmit.submit();
        });
    }

    function openPlaytimeModal(form) {
        ensureModal();
        currentForm = form;
        playtimeInput.value = '0';
        modal.classList.remove('hidden');
        playtimeInput.focus();
        playtimeInput.select();
    }

    window.submitFavoriteForm = function submitFavoriteForm(event, formId, shouldAskPlaytime) {
        if (event) {
            event.preventDefault();
            if (typeof event.stopPropagation === 'function') {
                event.stopPropagation();
            }
        }

        var form = document.getElementById(formId);
        if (!form) {
            return false;
        }

        if (shouldAskPlaytime) {
            openPlaytimeModal(form);
            return false;
        }

        form.submit();
        return false;
    };
})();
