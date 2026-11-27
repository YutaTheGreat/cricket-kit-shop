<?php

function insertCricketKit(PDO $pdo, string $item_brand, string $item_type, int $price, int $userId): bool
{
    $stmt = $pdo->prepare("INSERT INTO cricket_kits (item_brand, item_type, price, added_by, last_updated_by) VALUES (:item_brand, :item_type, :price, :added_by, :last_updated_by)");
    return $stmt->execute([':item_brand' => $item_brand, ':item_type' => $item_type, ':price' => $price, ':added_by' => $userId, ':last_updated_by' => $userId]);
}

function updateCricketKit(PDO $pdo, int $item_id, string $item_brand, string $item_type, int $price, int $userId): bool
{
    $stmt = $pdo->prepare("UPDATE cricket_kits SET item_brand = :item_brand, item_type = :item_type, price = :price, last_updated_by = :user_id, last_updated = CURRENT_TIMESTAMP WHERE item_id = :item_id AND deleted_at IS NULL");
    return $stmt->execute([':item_id' => $item_id, ':item_brand' => $item_brand, ':item_type' => $item_type, ':price' => $price, ':user_id' => $userId]);
}

function insertBuyer(PDO $pdo, string $buyer_name, int $item_id, string $email, string $phone, string $shipping_address, int $quantity, int $userId, ?string $imagePath = null): bool
{
    $stmt = $pdo->prepare("INSERT INTO buyers (buyer_name, item_id, email, phone, shipping_address, quantity, image_path, added_by, last_updated_by)
        SELECT :buyer_name, item_id, :email, :phone, :shipping_address, :quantity, :image_path, :added_by, :last_updated_by
        FROM cricket_kits WHERE item_id = :item_id AND deleted_at IS NULL");
    return $stmt->execute([':buyer_name' => $buyer_name, ':item_id' => $item_id, ':email' => $email, ':phone' => $phone, ':shipping_address' => $shipping_address, ':quantity' => $quantity, ':image_path' => $imagePath, ':added_by' => $userId, ':last_updated_by' => $userId]) && $stmt->rowCount() > 0;
}

function updateBuyer(PDO $pdo, int $buyer_id, string $buyer_name, int $item_id, string $email, string $phone, string $shipping_address, int $quantity, int $userId, bool $isOwner, ?string $imagePath = null): bool
{
    $stmt = $pdo->prepare("UPDATE buyers SET buyer_name = :buyer_name, item_id = :item_id, email = :email, phone = :phone, shipping_address = :shipping_address, quantity = :quantity, image_path = COALESCE(:image_path, image_path), last_updated_by = :last_updated_by, last_updated = CURRENT_TIMESTAMP WHERE buyer_id = :buyer_id AND deleted_at IS NULL AND EXISTS (SELECT 1 FROM cricket_kits k WHERE k.item_id = :kit_id AND k.deleted_at IS NULL) AND (:is_owner = 1 OR added_by = :user_id)");
    $params = [
        ':buyer_id' => $buyer_id,
        ':buyer_name' => $buyer_name,
        ':item_id' => $item_id,
        ':kit_id' => $item_id,
        ':email' => $email,
        ':phone' => $phone,
        ':shipping_address' => $shipping_address,
        ':quantity' => $quantity,
        ':image_path' => $imagePath,
        ':last_updated_by' => $userId,
        ':is_owner' => $isOwner ? 1 : 0,
        ':user_id' => $userId,
    ];
    if (!$stmt->execute($params)) {
        return false;
    }
    if ($stmt->rowCount() > 0) {
        return true;
    }

    $check = $pdo->prepare('SELECT 1 FROM buyers b JOIN cricket_kits k ON k.item_id = b.item_id WHERE b.buyer_id = :buyer_id AND b.deleted_at IS NULL AND k.deleted_at IS NULL AND (:is_owner = 1 OR b.added_by = :user_id)');
    $check->execute([
        ':buyer_id' => $buyer_id,
        ':is_owner' => $isOwner ? 1 : 0,
        ':user_id' => $userId,
    ]);
    return $check->fetchColumn() !== false;
}

function deleteBuyer(PDO $pdo, int $buyer_id, int $userId, bool $isOwner): bool
{
    $stmt = $pdo->prepare("UPDATE buyers SET deleted_at = CURRENT_TIMESTAMP, deleted_by = :deleted_by, deleted_with_item = 0 WHERE buyer_id = :buyer_id AND deleted_at IS NULL AND (:is_owner = 1 OR added_by = :user_id)");
    $stmt->execute([
        ':buyer_id' => $buyer_id,
        ':deleted_by' => $userId,
        ':is_owner' => $isOwner ? 1 : 0,
        ':user_id' => $userId,
    ]);
    return $stmt->rowCount() > 0;
}

function deleteCricketKit(PDO $pdo, int $item_id, int $userId): bool
{
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE cricket_kits SET deleted_at = CURRENT_TIMESTAMP, deleted_by = :deleted_by WHERE item_id = :item_id AND deleted_at IS NULL");
        $stmt->execute([':item_id' => $item_id, ':deleted_by' => $userId]);
        $deleted = $stmt->rowCount() > 0;
        if ($deleted) {
            $pdo->prepare("UPDATE buyers SET deleted_at = CURRENT_TIMESTAMP, deleted_by = :deleted_by, deleted_with_item = 1 WHERE item_id = :item_id AND deleted_at IS NULL")
                ->execute([':item_id' => $item_id, ':deleted_by' => $userId]);
        }
        $pdo->commit();
        return $deleted;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function restoreBuyer(PDO $pdo, int $buyer_id, int $userId, bool $isOwner): bool
{
    $stmt = $pdo->prepare("UPDATE buyers b
        JOIN cricket_kits k ON k.item_id = b.item_id
        SET b.deleted_at = NULL, b.deleted_by = NULL, b.deleted_with_item = 0
        WHERE b.buyer_id = :buyer_id AND b.deleted_at IS NOT NULL AND b.deleted_with_item = 0
          AND k.deleted_at IS NULL AND (:is_owner = 1 OR b.added_by = :user_id)");
    $stmt->execute([
        ':buyer_id' => $buyer_id,
        ':is_owner' => $isOwner ? 1 : 0,
        ':user_id' => $userId,
    ]);
    return $stmt->rowCount() > 0;
}

function restoreCricketKit(PDO $pdo, int $item_id): bool
{
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE cricket_kits SET deleted_at = NULL, deleted_by = NULL WHERE item_id = :item_id AND deleted_at IS NOT NULL");
        $stmt->execute([':item_id' => $item_id]);
        $restored = $stmt->rowCount() > 0;
        if ($restored) {
            $pdo->prepare("UPDATE buyers SET deleted_at = NULL, deleted_by = NULL, deleted_with_item = 0 WHERE item_id = :item_id AND deleted_at IS NOT NULL AND deleted_with_item = 1")
                ->execute([':item_id' => $item_id]);
        }
        $pdo->commit();
        return $restored;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
?>