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

    if (galleryInput) {
        galleryInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            if (files.length > 6) {
                galleryError.textContent = `Erreur: Vous avez sélectionné ${files.length} fichiers. Le maximum est 6.`;
                galleryError.classList.remove('hidden');
                galleryPreviewContainer.innerHTML = '';
                galleryPlaceholder.classList.remove('hidden');
                galleryPreviewContainer.classList.add('hidden');
                this.value = ''; // Reset
                return;
            } else {
                galleryError.classList.add('hidden');
            }

            if (files.length > 0) {
                galleryPlaceholder.classList.add('hidden');
                galleryPreviewContainer.classList.remove('hidden');
                galleryPreviewContainer.innerHTML = ''; // Clear prev

                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = "flex flex-col items-center flex-shrink-0 w-16 h-20 p-1 bg-[#F0EEE9] rounded shadow-sm";
                        div.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-12 object-contain rounded" alt="galerie">
                            <span class="text-[8px] text-[#3769a9] truncate w-full mt-1 text-center font-semibold" title="${file.name}">${file.name}</span>
                        `;
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
