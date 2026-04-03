<?php

/* ── Only process POST requests that include 'username' ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {

    // Sanitise input: strip leading/trailing whitespace
    $username = trim($_POST['username']);

    /* ── Authorised username check ── */
    if ($username === "abc") {
        echo "Verified"; // Correct username → grant access
    } else {
        echo "Error";    // Incorrect username → deny access
    }
}
?>