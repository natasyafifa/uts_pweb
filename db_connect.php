<?php
// db_connect.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'hanasora';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("Koneksi DB gagal: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
