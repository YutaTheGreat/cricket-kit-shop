<?php

function insertCricketKit(PDO $pdo, string $item_brand, string $item_type, int $price): bool
{
    $stmt = $pdo->prepare(
        "INSERT INTO cricket_kits (item_brand, item_type, price) VALUES (:item_brand, :item_type, :price)"
    );

    return $stmt->execute([
        ':item_brand' => $item_brand,
        ':item_type' => $item_type,
        ':price' => $price,
    ]);
}

function updateCricketKit(PDO $pdo, int $item_id, string $item_brand, string $item_type, int $price): bool
{
    $stmt = $pdo->prepare(
        "UPDATE cricket_kits SET item_brand = :item_brand, item_type = :item_type, price = :price WHERE item_id = :item_id"
    );

    return $stmt->execute([
        ':item_id' => $item_id,
        ':item_brand' => $item_brand,
        ':item_type' => $item_type,
        ':price' => $price,
    ]);
}

function insertBuyer(PDO $pdo, string $buyer_name, int $item_id, string $email, string $phone, string $shipping_address, int $quantity): bool
{
    $stmt = $pdo->prepare(
        "INSERT INTO buyers (buyer_name, item_id, email, phone, shipping_address, quantity) VALUES (:buyer_name, :item_id, :email, :phone, :shipping_address, :quantity)"
    );

    return $stmt->execute([
        ':buyer_name' => $buyer_name,
        ':item_id' => $item_id,
        ':email' => $email,
        ':phone' => $phone,
        ':shipping_address' => $shipping_address,
        ':quantity' => $quantity,
    ]);
}

function updateBuyer(PDO $pdo, int $buyer_id, string $buyer_name, int $item_id, string $email, string $phone, string $shipping_address, int $quantity): bool
{
    $stmt = $pdo->prepare(
        "UPDATE buyers SET buyer_name = :buyer_name, item_id = :item_id, email = :email, phone = :phone, shipping_address = :shipping_address, quantity = :quantity WHERE buyer_id = :buyer_id"
    );

    return $stmt->execute([
        ':buyer_id' => $buyer_id,
        ':buyer_name' => $buyer_name,
        ':item_id' => $item_id,
        ':email' => $email,
        ':phone' => $phone,
        ':shipping_address' => $shipping_address,
        ':quantity' => $quantity,
    ]);
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

        $deleteBuyers = $pdo->prepare("DELETE FROM buyers WHERE item_id = :item_id");
        $deleteBuyers->execute([':item_id' => $item_id]);

        $deleteKit = $pdo->prepare("DELETE FROM cricket_kits WHERE item_id = :item_id");
        $deleted = $deleteKit->execute([':item_id' => $item_id]);

        $pdo->commit();
        return $deleted;
    } catch (Exception $e) {
        $pdo->rollBack();
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