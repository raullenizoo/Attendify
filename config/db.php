<?php

// ─────────────────────────────────────────────
// Database Configuration - ATTENDIFY
// ─────────────────────────────────────────────

$host     = 'localhost';
$dbname   = 'attendify';
$username = 'root';
$password = '';

// Show errors only during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Enable MySQLi Exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = new mysqli($host, $username, $password, $dbname);

    // Set charset
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    // Hide sensitive details
    die("Database connection error. Please contact administrator.");

}

// Now $conn is available for queries