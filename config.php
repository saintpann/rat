<?php
/**
 * Database Configuration
 * Reusable connection for The Mouse application
 * Uses themouse database schema
 */

$db_servername = 'localhost:3310';
$db_user = 'root';
$db_password = 'stephenpan04';
$db_name = 'themouse_db';

// Create connection
$mysqli = new mysqli($db_servername, $db_user, $db_password, $db_name);

// Check connection
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Set charset to UTF-8
$mysqli->set_charset("utf8");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set cache headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Define table names for easier reference
define('TBL_USR', 'usr');
define('TBL_BOOKING', 'booking');
define('TBL_TICKET', 'ticket');
define('TBL_MOVIE', 'movie');
define('TBL_ROOM', 'room');
define('TBL_SHOWING', 'showing');
define('TBL_SEAT', 'seat');
?>
