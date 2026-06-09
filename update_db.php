<?php
$host = getenv('DB_HOST');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_DATABASE');

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<h2>Starting Database Migration...</h2>";

// 1. Check if the column already exists to prevent duplicate column errors
$check_column = "SHOW COLUMNS FROM bookings LIKE 'total_amount'";
$result = mysqli_query($conn, $check_column);
$column_exists = mysqli_num_rows($result) > 0;

if (!$column_exists) {
    // 2. Add the total_amount column right after the check_out field
    $alter_sql = "ALTER TABLE bookings ADD COLUMN total_amount DECIMAL(10, 2) NOT NULL AFTER check_out";
    
    if (mysqli_query($conn, $alter_sql)) {
        echo "<p style='color: green;'>✅ Success: 'total_amount' column added to bookings table.</p>";
    } else {
        echo "<p style='color: red;'>❌ Error altering table: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: orange;'>ℹ️ Notice: 'total_amount' column already exists in bookings table. No changes made.</p>";
}

// 3. Make sure check_in and check_out are properly set to NOT NULL for safety
$modify_dates_sql = "ALTER TABLE bookings 
                     MODIFY COLUMN check_in DATE NOT NULL, 
                     MODIFY COLUMN check_out DATE NOT NULL";

if (mysqli_query($conn, $modify_dates_sql)) {
    echo "<p style='color: green;'>✅ Success: Date columns set to NOT NULL.</p>";
} else {
    echo "<p style='color: red;'>❌ Error modifying date columns: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);
echo "<h3>Migration Complete. Please delete this file from your server immediately.</h3>";
?>