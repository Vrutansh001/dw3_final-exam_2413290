<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not redirect to login page
function check_login() {
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }
}

// Get current user ID
function get_user_id() {
    return isset($_SESSION["id"]) ? $_SESSION["id"] : null;
}

// Get current username
function get_username() {
    return isset($_SESSION["username"]) ? $_SESSION["username"] : "";
}
?>
