<?php
$host = getenv('DB_HOST');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_DATABASE');

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<h2>Executing Option B: Database Architecture Sync...</h2>";

// 1. Create the missing destinations table
$sql_destinations = "CREATE TABLE IF NOT EXISTS destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql_destinations)) {
    echo "<p style='color: green;'>✅ Success: 'destinations' table created.</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating destinations table: " . mysqli_error($conn) . "</p>";
}

// 2. Add destination_id to hotels table if it doesn't exist yet
$check_column = "SHOW COLUMNS FROM hotels LIKE 'destination_id'";
$result = mysqli_query($conn, $check_column);

if (mysqli_num_rows($result) == 0) {
    // If your dashboard query joins hotels and destinations via an ID, we add it here
    $alter_hotels = "ALTER TABLE hotels 
                     ADD COLUMN destination_id INT NULL AFTER host_id,
                     ADD FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL";
    
    if (mysqli_query($conn, $alter_hotels)) {
        echo "<p style='color: green;'>✅ Success: Linked 'hotels' to 'destinations' via foreign key.</p>";
    } else {
        echo "<p style='color: red;'>❌ Note on Hotels Alteration: " . mysqli_error($conn) . " (If your query doesn't use destination_id in hotels, this can be safely ignored).</p>";
    }
} else {
    echo "<p style='color: orange;'>ℹ️ Notice: 'destination_id' already exists in hotels table.</p>";
}

mysqli_close($conn);
echo "<h3>Migration Complete. Refresh your dashboard and delete this file from your server!</h3>";
?>