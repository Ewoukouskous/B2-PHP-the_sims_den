document.addEventListener('DOMContentLoaded', () => {
    
    // Preview logic for Hero and Title images
    const setupSingleImagePreview = (inputId, placeholderId, previewContainerId, previewImageId, nameId) => {
        const input = document.getElementById(inputId);
        const placeholder = document.getElementById(placeholderId);
        const previewContainer = document.getElementById(previewContainerId);
        const previewImage = document.getElementById(previewImageId);
        const nameText = document.getElementById(nameId);
        
        if (!input) return;

        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    nameText.textContent = file.name;
                    placeholder.classList.add('hidden');
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            } else {
                previewImage.src = '';
                nameText.textContent = '';
                placeholder.classList.remove('hidden');
                previewContainer.classList.add('hidden');
            }
        });
    };

    setupSingleImagePreview('gameHeroPic', 'heroPlaceholder', 'heroPreviewContainer', 'heroPreview', 'heroName');
    setupSingleImagePreview('gameTitlePic', 'titlePlaceholder', 'titlePreviewContainer', 'titlePreview', 'titleName');

    // Preview logic for multiple Gallery Images
    const galleryInput = document.getElementById('gamePictures');
    const galleryPlaceholder = document.getElementById('galleryPlaceholder');
    const galleryPreviewContainer = document.getElementById('galleryPreviewContainer');
    const galleryError = document.getElementById('galleryError');
    let accumulatedFiles = [];

    if (galleryInput) {
        galleryInput.addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            
            // Accumulate new files while respecting the limit of 6
            let filesToAdd = newFiles.slice(0, 6 - accumulatedFiles.length);
            accumulatedFiles = accumulatedFiles.concat(filesToAdd);
            
            if (accumulatedFiles.length + (newFiles.length - filesToAdd.length) > 6) {
                galleryError.textContent = `Erreur: Le maximum est de 6 images. Seules les premières ont été conservées.`;
                galleryError.classList.remove('hidden');
            } else {
                galleryError.classList.add('hidden');
            }

            // Sync the input state with accumulated files using DataTransfer
            const dt = new DataTransfer();
            accumulatedFiles.forEach(f => dt.items.add(f));
            galleryInput.files = dt.files;

            if (accumulatedFiles.length > 0) {
                galleryPlaceholder.classList.add('hidden');
                galleryPreviewContainer.classList.remove('hidden');
                galleryPreviewContainer.innerHTML = ''; // Clear prev

                accumulatedFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = "relative flex flex-col items-center flex-shrink-0 w-16 h-20 p-1 bg-[#F0EEE9] rounded shadow-sm group";
                        div.innerHTML = `
                            <button type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition shadow" data-remove-idx="${index}">✕</button>
                            <img src="${e.target.result}" class="w-full h-12 object-contain rounded" alt="galerie">
                            <span class="text-[8px] text-[#3769a9] truncate w-full mt-1 text-center font-semibold" title="${file.name}">${file.name}</span>
                        `;
                        // Remove image event listener
                        div.querySelector('button').addEventListener('click', (ev) => {
                            ev.preventDefault();
                            accumulatedFiles.splice(index, 1);
                            // Trigger a synthetic change event to re-render
                            const dtRemove = new DataTransfer();
                            accumulatedFiles.forEach(f => dtRemove.items.add(f));
                            galleryInput.files = dtRemove.files;
                            galleryInput.dispatchEvent(new Event('change'));
                        });
                        galleryPreviewContainer.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });
            } else {
                galleryPlaceholder.classList.remove('hidden');
                galleryPreviewContainer.classList.add('hidden');
                galleryPreviewContainer.innerHTML = '';
            }
        });
    }

    // Basic Validation before submit
    const form = document.getElementById('addGameForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const priceInput = document.getElementById('gamePrice').value;
            const heroInput = document.getElementById('gameHeroPic').files.length;
            const titleInput = document.getElementById('gameTitlePic').files.length;

            if (parseFloat(priceInput) < 0) {
                alert("Le prix ne peut pas être négatif.");
                e.preventDefault();
                return;
            }

            if (heroInput === 0 || titleInput === 0) {
                alert("Les images principales et secondaires sont obligatoires.");
                e.preventDefault();
                return;
            }

            // Could add GameType and PegiAge radio checking if required HTML5 fails
            const pegiAgeChecked = document.querySelector('input[name="gamePegiAge"]:checked');
            const gameTypeChecked = document.querySelector('input[name="gameType"]:checked');

            if (!pegiAgeChecked) {
                alert("Veuillez sélectionner un âge PEGI.");
                e.preventDefault();
                return;
            }

            if (!gameTypeChecked) {
                alert("Veuillez sélectionner un type de jeu.");
                e.preventDefault();
                return;
            }
        });
    }
});
