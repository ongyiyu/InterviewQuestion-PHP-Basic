function verifyUser() {
    const usernameInput = document.getElementById("username");
    const resultEl      = document.getElementById("result");

    const username = usernameInput.value.trim();

    /* ── Step 1: Client-side empty check ── */
    if (username === "") {
        showResult(resultEl, "Key in username and click submit.", "info");
        return; // Stop — no need to contact the server
    }

    /* ── Step 2: Build and send AJAX POST request ── */
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "info.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    /* ── Step 3: Handle server response ── */
    xhr.onload = function () {
        const response = this.responseText.trim();

        if (response === "Verified") {
            showResult(resultEl, "✓ Verified", "success");
        } else {
            showResult(resultEl, "✗ Invalid username. Try again.", "error");
        }
    };

    /* ── Step 4: Handle network/server errors ── */
    xhr.onerror = function () {
        showResult(resultEl, "Network error. Please try again.", "error");
    };

    // Encode and send the username as form data
    xhr.send("username=" + encodeURIComponent(username));
}

/**
 * Updates the result paragraph with a message and a CSS class
 * that controls its color (success | error | info).
 *
 * @param {HTMLElement} el        - The result <p> element.
 * @param {string}      message   - Text to display.
 * @param {string}      cssClass  - One of: "success", "error", "info".
 */
function showResult(el, message, cssClass) {
    // Remove any previous state classes before applying the new one
    el.className = cssClass;
    el.innerHTML = message;
}