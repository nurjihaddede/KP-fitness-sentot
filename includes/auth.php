<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php') {
    redirect('login.php');
}
?>