(function () {
    // Create the elements of the toast (background, card and content) and return the "overlay" and "card" objects
    function createToastElement(title, message) {
        const overlay = document.createElement("div");
        overlay.className = "fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-[rgba(10,20,35,0.16)] backdrop-blur-sm transition-opacity duration-200 opacity-0";

        const card = document.createElement("div");
        card.className = "w-full max-w-sm rounded-lg border-4 border-gray-300 bg-white/95 p-8 shadow-2xl text-center transform transition-all duration-200 translate-y-4 opacity-0";

        const titleElement = document.createElement("h3");
        titleElement.className = "text-3xl font-bold tracking-wide text-blue-600";
        titleElement.textContent = title;

        const messageElement = document.createElement("p");
        messageElement.className = "mt-3 text-base font-bold text-gray-700";
        messageElement.textContent = message;

        card.appendChild(titleElement);
        card.appendChild(messageElement);
        overlay.appendChild(card);

        return { overlay, card };
    }
    // Do the animations (fade in and slide up) by adding/removing the classes
    function showWithTransition(overlay, card) {
        overlay.classList.remove("opacity-0");
        overlay.classList.add("opacity-100");
        card.classList.remove("translate-y-4", "opacity-0");
        card.classList.add("translate-y-0", "opacity-100");
    }

    // The function that is called when the achievement is triggered (with parameters)
    window.showAchievementToast = function ({ title, message, soundSrc, autoCloseMs = 1500 }) {
        // Get the toast elements via the function createToastElement() and add it to the boddy
        const { overlay, card } = createToastElement(title, message);
        document.body.appendChild(overlay);
        // Do the transitions
        showWithTransition(overlay, card);

        // Start a promise (async function) that will resolve when the toast is closed (because we have animations and sounds to play)
        return new Promise((resolve) => {
            // Initialise a boolean to know if the toast is already closed
            let closed = false;

            // If not already closed, it will close the toast by doing animation and then remove
            // the toast element from the DOM and resolve the promise
            const closeToast = () => {
                if (closed) return;
                closed = true;
                // Do the closing animations
                overlay.classList.remove("opacity-100");
                overlay.classList.add("opacity-0");
                card.classList.remove("translate-y-0", "opacity-100");
                card.classList.add("translate-y-4", "opacity-0");
                // Wait 200ms to be sure animations ends and remove the element and resolve the promise (validate it)
                setTimeout(() => {
                    overlay.remove();
                    resolve();
                }, 200);
            };
            // If no sound given we wait the given time before closing the toast
            if (!soundSrc) {
                setTimeout(closeToast, autoCloseMs);
                return;
            }
            // Play the audio then close the toast (once in case a bug cause a closing loop)
            const audio = new Audio(soundSrc);
            audio.addEventListener("ended", closeToast, { once: true });
            // In case there is an error while playing the sound we close it after the given time
            audio.play().catch(() => {
                setTimeout(closeToast, autoCloseMs);
            });
        });
    };
})();
