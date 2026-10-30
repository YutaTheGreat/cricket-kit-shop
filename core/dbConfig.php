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

    // Create the user entity and add audit columns to an existing installation.
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        address TEXT NOT NULL,
        age INT NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $hasRole = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'")->fetchColumn();
    if (!$hasRole) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('owner', 'customer') NOT NULL DEFAULT 'customer'");
    }

    $ownerHash = '$2y$10$I3e.qw8ZKt7YQugfRIzX1.fFlLQcXDJ0.9aJOARRP5489A1htT0We';
    $owner = $pdo->prepare('SELECT user_id FROM users WHERE username = :username LIMIT 1');
    $owner->execute([':username' => 'owner']);
    if (!$owner->fetch()) {
        $seedOwner = $pdo->prepare("INSERT INTO users (first_name, last_name, address, age, phone, email, username, password, role)
            VALUES ('Shop', 'Owner', 'Cricket Kit Shop Office', 30, '00000000000', 'owner@cricketkit.shop', 'owner', :password, 'owner')");
        $seedOwner->execute([':password' => $ownerHash]);
    }

    foreach (['cricket_kits', 'buyers'] as $table) {
        $hasAddedBy = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = 'added_by'")->fetchColumn();
        if (!$hasAddedBy) {
            $pdo->exec("ALTER TABLE $table ADD COLUMN added_by INT NULL");
        }
        $hasLastUpdated = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = 'last_updated'")->fetchColumn();
        if (!$hasLastUpdated) {
            $pdo->exec("ALTER TABLE $table ADD COLUMN last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        }
    }
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
