<?php
// config.php - DB connection and common utilities
session_start();
$host = 'localhost';
$db   = 'house_renting_system';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("DB Connection failed: " . $mysqli->connect_error);
}

function esc($s) { return htmlspecialchars($s, ENT_QUOTES); }
