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
            // Send the cheat code to the backend via POST request
            fetch("/actions/cheat.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ cheat: "motherlode" })
            })
                .then(response => response.json())
                .then(data => {
                    // If the achievement is successfully unlocked, reload the page to display it
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(error => console.error("Cheat error:", error));

            // Reset the input string after a successful match
            input = "";
        }
    });
})();
