<?php
// Simple session auth helper. Include at top of protected pages.
session_start();

// inactivity timeout in seconds
$timeout = 300; // 5 minutes

// login check function
function require_login(){
    global $timeout;
    if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
        header('Location: /formulario/registros/login.php');
        exit;
    }
    // check inactivity
    if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active']) > $timeout) {
        // destroy and redirect to login with timeout info
        session_unset(); session_destroy();
        header('Location: /formulario/registros/login.php?timeout=1');
        exit;
    }
    // update last active
    $_SESSION['last_active'] = time();
}

// helper to perform login (call from login handler)
function do_login(){
    session_regenerate_id(true);
    $_SESSION['logged'] = true;
    $_SESSION['last_active'] = time();
}

// helper to logout
function do_logout(){
    session_unset();
    session_destroy();
}
