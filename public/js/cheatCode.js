(function () {
    let input = "";
    const cheatCode = "motherlode";

    // Listen for keyboard input on the entire window
    window.addEventListener("keydown", (e) => {
        // Ignore input if the user is currently typing in an input field or textarea
        if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") return;

        input += e.key.toLowerCase();

        // Keep only the last 'n' characters (length of the cheat code)
        if (input.length > cheatCode.length) {
            input = input.substring(input.length - cheatCode.length);
        }

        // Check if the sequence matches the cheat code
        if (input === cheatCode) {
            console.log("'motherlode' cheat detected");

            // Send the cheat code to the backend via POST request
            fetch("/actions/unlockMotherlode.php", {
                method: "POST",
            })
                .then(async (response) => {
                    // Get the raw response and clean the BOM invisible character that make crash the JSON parsing
                    const raw = await response.text();
                    const cleaned = raw.replace(/^\uFEFF+/, "");
                    return JSON.parse(cleaned);
                })
                // Then we take the json data to check if the request succeed
                .then((data) => {
                    // If succeed we call the showAchievementToast() to show the achievement and reload the page
                    if (data.succeed) {
                        window.showAchievementToast({
                            title: "Succès débloqué",
                            message: "Motherlode ! Jette un oeil a tes succès.",
                            soundSrc: "/sounds/motherlode.mp3",
                            autoCloseMs: 2500
                        }).finally(() => {
                            window.location.reload();
                        });
                    } else {
                        console.log("Error while unlocking 'Motherlode' achievement : " + data.error);
                    }
                })
                .catch((error) => console.error("Cheat error:", error));

            // Reset the input string after a successful match
            input = "";
        }
    });
})();
