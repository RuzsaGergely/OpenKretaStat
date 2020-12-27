<?php
session_start();

if (!isset($_SESSION["login-status"]) || !$_SESSION["login-status"]) {
    header("Location: index.php");
    exit();
}

$config = parse_ini_file('config.ini');