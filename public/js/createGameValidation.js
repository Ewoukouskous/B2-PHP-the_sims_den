(function () {
    const form = document.querySelector('form[data-add-game-form]');
    if (!form) {
        return;
    }

    const maxPerFile = 10 * 1024 * 1024;
    const maxTotal = 50 * 1024 * 1024;
    const allowedExtensions = new Set(['png', 'jpg', 'jpeg', 'webp']);

    const titlePicInput = form.querySelector('input[name="gameTitlePic"]');
    const heroPicInput = form.querySelector('input[name="gameHeroPic"]');
    const galleryInput = form.querySelector('input[name="gamePictures[]"]');

    function isMeaningfulFile(file) {
        return !!file && typeof file.name === 'string' && file.name.trim() !== '' && file.size > 0;
    }

    function extensionOf(fileName) {
        const dotIndex = fileName.lastIndexOf('.');
        if (dotIndex === -1) {
            return '';
        }
        return fileName.slice(dotIndex + 1).toLowerCase();
    }

    function validateExtension(file, label, errors) {
        const extension = extensionOf(file.name);
        if (!allowedExtensions.has(extension)) {
            errors.push(label + ' : format non autorise (' + file.name + ').');
        }
    }

    function isValidSinglePhoto(file) {
        return isMeaningfulFile(file)
            && allowedExtensions.has(extensionOf(file.name))
            && file.size <= maxPerFile;
    }

    function isValidGalleryPhoto(file) {
        return isMeaningfulFile(file)
            && allowedExtensions.has(extensionOf(file.name))
            && file.size <= maxPerFile;
    }

    function getLabelTextNode(input) {
        if (!input) {
            return null;
        }

        const label = input.closest('label');
        if (!label) {
            return null;
        }

        return label.querySelector('span');
    }

    function setUploadVisualState(input, isValid, successText) {
        if (!input) {
            return;
        }

        const label = input.closest('label');
        const textNode = getLabelTextNode(input);
        if (!label || !textNode) {
            return;
        }

        if (!textNode.dataset.baseText) {
            textNode.dataset.baseText = textNode.textContent.trim();
        }

        if (isValid) {
            label.style.borderColor = '#33b842';
            textNode.textContent = textNode.dataset.baseText + ' - ' + successText;
        } else {
            label.style.borderColor = '#3769a9';
            textNode.textContent = textNode.dataset.baseText;
        }
    }

    function updateUploadStates() {
        const titlePic = titlePicInput && titlePicInput.files ? titlePicInput.files[0] : null;
        const heroPic = heroPicInput && heroPicInput.files ? heroPicInput.files[0] : null;
        const galleryFilesRaw = galleryInput && galleryInput.files ? Array.from(galleryInput.files) : [];
        const galleryFiles = galleryFilesRaw.filter(isMeaningfulFile);

        setUploadVisualState(titlePicInput, isValidSinglePhoto(titlePic), 'importee');
        setUploadVisualState(heroPicInput, isValidSinglePhoto(heroPic), 'importee');

        let galleryOk = galleryFiles.length > 0 && galleryFiles.length <= 6;
        galleryFiles.forEach(function (file) {
            if (!isValidGalleryPhoto(file)) {
                galleryOk = false;
            }
        });

        const galleryText = galleryFiles.length > 0
            ? galleryFiles.length + ' photo(s) importee(s)'
            : 'importee';
        setUploadVisualState(galleryInput, galleryOk, galleryText);
    }

    if (titlePicInput) {
        titlePicInput.addEventListener('change', updateUploadStates);
    }
    if (heroPicInput) {
        heroPicInput.addEventListener('change', updateUploadStates);
    }
    if (galleryInput) {
        galleryInput.addEventListener('change', updateUploadStates);
    }

    updateUploadStates();

    form.addEventListener('submit', function (event) {
        const errors = [];

        const titlePic = titlePicInput && titlePicInput.files ? titlePicInput.files[0] : null;
        const heroPic = heroPicInput && heroPicInput.files ? heroPicInput.files[0] : null;
        const galleryFilesRaw = galleryInput && galleryInput.files ? Array.from(galleryInput.files) : [];

        const galleryFiles = galleryFilesRaw.filter(isMeaningfulFile);

        if (!isMeaningfulFile(titlePic)) {
            errors.push('Photo secondaire obligatoire (gameTitlePic).');
        }

        if (!isMeaningfulFile(heroPic)) {
            errors.push('Photo principale obligatoire (gameHeroPic).');
        }

        if (galleryFiles.length > 6) {
            errors.push('gamePictures accepte au maximum 6 images.');
        }

        const filesToCheck = [];
        if (isMeaningfulFile(titlePic)) {
            filesToCheck.push({ file: titlePic, label: 'Photo secondaire' });
        }
        if (isMeaningfulFile(heroPic)) {
            filesToCheck.push({ file: heroPic, label: 'Photo principale' });
        }

        galleryFiles.forEach(function (file, index) {
            filesToCheck.push({ file: file, label: 'Image galerie #' + (index + 1) });
        });

        let totalSize = 0;
        filesToCheck.forEach(function (entry) {
            const file = entry.file;
            const label = entry.label;

            if (file.size > maxPerFile) {
                errors.push(label + ' depasse 10MB.');
            }

            validateExtension(file, label, errors);
            totalSize += file.size;
        });

        if (totalSize > maxTotal) {
            errors.push('La taille totale des fichiers depasse 50MB.');
        }

        updateUploadStates();

        if (errors.length > 0) {
            event.preventDefault();
            alert(errors.join('\n'));
        }
    });
})();
