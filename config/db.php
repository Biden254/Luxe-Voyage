<?php
$host = getenv('DB_HOST');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_DATABASE');
$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Database connection failed");
}

// Helper: check whether a specific column exists in a table
function columnExistsInDb($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $res && mysqli_num_rows($res) > 0;
}

// Flags other scripts can use to avoid selecting missing columns
$has_hotel_image = columnExistsInDb($conn, 'hotels', 'image');
$has_destination_image = columnExistsInDb($conn, 'destinations', 'image');
// Booking date column may be absent on older schemas
$has_booking_date = columnExistsInDb($conn, 'bookings', 'booking_date');

?>
