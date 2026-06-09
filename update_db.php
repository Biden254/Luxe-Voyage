<?php
$host = getenv('DB_HOST');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_DATABASE');

$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<h2>Executing Database Schema Sync...</h2>";

define('COLOR_OK', '#2f855a');
define('COLOR_ERROR', '#c53030');

function execSql($conn, $sql, $message) {
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: " . COLOR_OK . ";'>✅ $message</p>";
    } else {
        echo "<p style='color: " . COLOR_ERROR . ";'>❌ $message: " . mysqli_error($conn) . "</p>";
    }
}

function columnExists($conn, $table, $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

function constraintExists($conn, $table, $constraint) {
    $result = mysqli_query($conn, "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND CONSTRAINT_NAME = '$constraint'");
    return $result && mysqli_num_rows($result) > 0;
}

// 1. Ensure destinations table exists with proper columns
$sql_destinations = "CREATE TABLE IF NOT EXISTS destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    image VARCHAR(255) NULL,
    country VARCHAR(100) NULL,
    best_season VARCHAR(100) NULL,
    popular_attractions TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";
execSql($conn, $sql_destinations, "Destinations table exists or created");

// 2. Ensure hotels table exists and has required columns
if (!columnExists($conn, 'hotels', 'destination_id')) {
    execSql($conn, "ALTER TABLE hotels ADD COLUMN destination_id INT NULL AFTER host_id", "Added hotels.destination_id column");
}

if (!columnExists($conn, 'hotels', 'image')) {
    execSql($conn, "ALTER TABLE hotels ADD COLUMN image VARCHAR(255) NULL AFTER price", "Added hotels.image column");
}

if (!columnExists($conn, 'hotels', 'location')) {
    execSql($conn, "ALTER TABLE hotels ADD COLUMN location VARCHAR(255) NULL AFTER image", "Added hotels.location column");
}

if (!columnExists($conn, 'hotels', 'amenities')) {
    execSql($conn, "ALTER TABLE hotels ADD COLUMN amenities TEXT NULL AFTER location", "Added hotels.amenities column");
}

if (columnExists($conn, 'hotels', 'destination_id') && !constraintExists($conn, 'hotels', 'fk_hotels_destinations')) {
    execSql($conn, "ALTER TABLE hotels ADD CONSTRAINT fk_hotels_destinations FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL", "Added hotels.destination_id foreign key");
}

// 3. Ensure bookings table exists and has expected columns
$sql_bookings = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    hotel_id INT NOT NULL,
    destination_id INT NULL,
    check_in DATE,
    check_out DATE,
    guests INT DEFAULT 1,
    special_requests TEXT NULL,
    status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL
) ENGINE=InnoDB";
execSql($conn, $sql_bookings, "Bookings table exists or created");

if (!columnExists($conn, 'bookings', 'customer_id') && columnExists($conn, 'bookings', 'user_id')) {
    execSql($conn, "ALTER TABLE bookings ADD COLUMN customer_id INT NULL AFTER user_id", "Added bookings.customer_id column");
    execSql($conn, "UPDATE bookings SET customer_id = user_id WHERE customer_id IS NULL", "Copied values from bookings.user_id to bookings.customer_id");
    execSql($conn, "ALTER TABLE bookings MODIFY COLUMN customer_id INT NOT NULL", "Changed bookings.customer_id to NOT NULL");
}

if (!columnExists($conn, 'bookings', 'destination_id')) {
    execSql($conn, "ALTER TABLE bookings ADD COLUMN destination_id INT NULL AFTER hotel_id", "Added bookings.destination_id column");
}

if (!constraintExists($conn, 'bookings', 'fk_bookings_destinations') && columnExists($conn, 'bookings', 'destination_id')) {
    execSql($conn, "ALTER TABLE bookings ADD CONSTRAINT fk_bookings_destinations FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL", "Added bookings.destination_id foreign key");
}

if (!columnExists($conn, 'bookings', 'guests')) {
    execSql($conn, "ALTER TABLE bookings ADD COLUMN guests INT DEFAULT 1 AFTER check_out", "Added bookings.guests column");
}

if (!columnExists($conn, 'bookings', 'special_requests')) {
    execSql($conn, "ALTER TABLE bookings ADD COLUMN special_requests TEXT NULL AFTER guests", "Added bookings.special_requests column");
}

if (!columnExists($conn, 'bookings', 'booking_date')) {
    execSql($conn, "ALTER TABLE bookings ADD COLUMN booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status", "Added bookings.booking_date column");
}

if (!columnExists($conn, 'bookings', 'created_at')) {
    execSql($conn, "ALTER TABLE bookings ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER booking_date", "Added bookings.created_at column");
}

if (!columnExists($conn, 'bookings', 'updated_at')) {
    execSql($conn, "ALTER TABLE bookings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at", "Added bookings.updated_at column");
}

mysqli_close($conn);
echo "<h3>Migration Complete. Refresh your dashboard and remove this file when done.</h3>";
?>