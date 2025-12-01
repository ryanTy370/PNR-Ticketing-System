<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bicol_express_online_ticketing_system";

// Other configuration options
$siteTitle = "Bicol Express";
$siteURL = "http://localhost/";

// Set timezone
date_default_timezone_set('Asia/Manila');

// Error reporting settings - comment these out in production
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Session settings
ini_set('session.cookie_httponly', 1);
session_start();
?> 