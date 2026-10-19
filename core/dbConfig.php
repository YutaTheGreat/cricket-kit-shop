<?php
// Database connection credentials
$host = "localhost";
$user = "root";
$password = "";
$dbname = "cricket_kit_shop_db";

// Data Source Name (DSN) specifying driver, host, and database name
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

try {
    // Create a PDO instance/connection
    $pdo = new PDO($dsn, $user, $password);
    
    // Set PDO error mode to Exception to catch database errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to Associative Array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
