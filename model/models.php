<?php

function insertCricketKit(PDO $pdo, string $item_brand, string $item_type, int $price, int $userId): bool
{
    $stmt = $pdo->prepare("INSERT INTO cricket_kits (item_brand, item_type, price, added_by) VALUES (:item_brand, :item_type, :price, :user_id)");
    return $stmt->execute([':item_brand' => $item_brand, ':item_type' => $item_type, ':price' => $price, ':user_id' => $userId]);
}

function updateCricketKit(PDO $pdo, int $item_id, string $item_brand, string $item_type, int $price, int $userId): bool
{
    $stmt = $pdo->prepare("UPDATE cricket_kits SET item_brand = :item_brand, item_type = :item_type, price = :price, added_by = :user_id, last_updated = CURRENT_TIMESTAMP WHERE item_id = :item_id");
    return $stmt->execute([':item_id' => $item_id, ':item_brand' => $item_brand, ':item_type' => $item_type, ':price' => $price, ':user_id' => $userId]);
}

function insertBuyer(PDO $pdo, string $buyer_name, int $item_id, string $email, string $phone, string $shipping_address, int $quantity, int $userId): bool
{
    $stmt = $pdo->prepare("INSERT INTO buyers (buyer_name, item_id, email, phone, shipping_address, quantity, added_by) VALUES (:buyer_name, :item_id, :email, :phone, :shipping_address, :quantity, :user_id)");
    return $stmt->execute([':buyer_name' => $buyer_name, ':item_id' => $item_id, ':email' => $email, ':phone' => $phone, ':shipping_address' => $shipping_address, ':quantity' => $quantity, ':user_id' => $userId]);
}

function updateBuyer(PDO $pdo, int $buyer_id, string $buyer_name, int $item_id, string $email, string $phone, string $shipping_address, int $quantity, int $userId): bool
{
    $stmt = $pdo->prepare("UPDATE buyers SET buyer_name = :buyer_name, item_id = :item_id, email = :email, phone = :phone, shipping_address = :shipping_address, quantity = :quantity, added_by = :user_id, last_updated = CURRENT_TIMESTAMP WHERE buyer_id = :buyer_id");
    return $stmt->execute([':buyer_id' => $buyer_id, ':buyer_name' => $buyer_name, ':item_id' => $item_id, ':email' => $email, ':phone' => $phone, ':shipping_address' => $shipping_address, ':quantity' => $quantity, ':user_id' => $userId]);
}

function deleteBuyer(PDO $pdo, int $buyer_id): bool
{
    $stmt = $pdo->prepare("DELETE FROM buyers WHERE buyer_id = :buyer_id");
    return $stmt->execute([':buyer_id' => $buyer_id]);
}

function deleteCricketKit(PDO $pdo, int $item_id): bool
{
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM buyers WHERE item_id = :item_id")->execute([':item_id' => $item_id]);
        $deleted = $pdo->prepare("DELETE FROM cricket_kits WHERE item_id = :item_id")->execute([':item_id' => $item_id]);
        $pdo->commit();
        return $deleted;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

function getAllCricketKits(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM cricket_kits ORDER BY date_added DESC");
    return $stmt->fetchAll();
}

function getBuyerById(PDO $pdo, int $buyer_id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM buyers WHERE buyer_id = :buyer_id");
    $stmt->execute([':buyer_id' => $buyer_id]);
    $result = $stmt->fetch();
    return $result ?: null;
}
?>