<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth() {
    if(!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }
}

function checkAdmin() {
    if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
        header('Location: dashboard.php');
        exit;
    }
}
?>
