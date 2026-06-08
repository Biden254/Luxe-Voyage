<?php
$host = getenv('DB_HOST');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_DATABASE');
$conn = mysqli_connect($host, $username, $password, $dbname, 3307);

if (!$conn) {
    die("Database connection failed");
}
?>
