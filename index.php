<?php
require_once __DIR__ . '/core/dbConfig.php';
require_once __DIR__ . '/core/auth.php';
requireLogin();
$ownerAccess = isOwner();
$kitSearch = trim($_GET['kit_search'] ?? '');
$orderSearch = trim($_GET['order_search'] ?? '');

$kitSql = 'SELECT k.item_id, k.item_brand, k.item_type, k.price, k.date_added, k.last_updated,
                  CONCAT_WS(" ", u.first_name, u.last_name) AS added_by_name,
                  CONCAT_WS(" ", editor.first_name, editor.last_name) AS last_updated_by_name
           FROM cricket_kits k
           LEFT JOIN users u ON u.user_id = k.added_by
           LEFT JOIN users editor ON editor.user_id = k.last_updated_by
           WHERE k.deleted_at IS NULL';
$kitParams = [];
if ($kitSearch !== '') {
    $kitSql .= ' AND (CAST(k.item_id AS CHAR) LIKE :kit_id
                 OR k.item_brand LIKE :kit_brand
                 OR k.item_type LIKE :kit_type
                 OR CAST(k.price AS CHAR) LIKE :kit_price)';
    $kitTerm = '%' . $kitSearch . '%';
    $kitParams = [
        ':kit_id' => $kitTerm,
        ':kit_brand' => $kitTerm,
        ':kit_type' => $kitTerm,
        ':kit_price' => $kitTerm,
    ];
}
$kitSql .= ' ORDER BY k.item_id DESC';
$stmt = $pdo->prepare($kitSql);
$stmt->execute($kitParams);
$kits = $stmt->fetchAll();

$deletedKits = [];
if ($ownerAccess) {
    $deletedKits = $pdo->query('SELECT k.item_id, k.item_brand, k.item_type, k.deleted_at,
                CONCAT_WS(" ", u.first_name, u.last_name) AS deleted_by_name
         FROM cricket_kits k
         LEFT JOIN users u ON u.user_id = k.deleted_by
         WHERE k.deleted_at IS NOT NULL
         ORDER BY k.deleted_at DESC')->fetchAll();
}

$buyerSql = 'SELECT b.buyer_id, b.buyer_name, b.email, b.phone, b.shipping_address, b.quantity, b.order_date, b.last_updated,
            k.item_brand, k.item_type, k.price,
            CONCAT_WS(" ", u.first_name, u.last_name) AS added_by_name,
            CONCAT_WS(" ", editor.first_name, editor.last_name) AS last_updated_by_name
     FROM buyers b
     JOIN cricket_kits k ON k.item_id = b.item_id
     LEFT JOIN users u ON u.user_id = b.added_by
     LEFT JOIN users editor ON editor.user_id = b.last_updated_by
     WHERE b.deleted_at IS NULL AND k.deleted_at IS NULL';
if (!isOwner()) {
    $buyerSql .= ' AND b.added_by = :user_id';
}
if ($orderSearch !== '') {
    $buyerSql .= ' AND (CAST(b.buyer_id AS CHAR) LIKE :order_id
                   OR b.buyer_name LIKE :buyer_name
                   OR b.email LIKE :buyer_email
                   OR b.phone LIKE :buyer_phone
                   OR k.item_brand LIKE :order_brand
                   OR k.item_type LIKE :order_type)';
}
$buyerSql .= ' ORDER BY b.buyer_id DESC';
$buyerStmt = $pdo->prepare($buyerSql);
$buyerParams = [];
if (!isOwner()) {
    $buyerParams[':user_id'] = currentUserId();
}
if ($orderSearch !== '') {
    $orderTerm = '%' . $orderSearch . '%';
    $buyerParams += [
        ':order_id' => $orderTerm,
        ':buyer_name' => $orderTerm,
        ':buyer_email' => $orderTerm,
        ':buyer_phone' => $orderTerm,
        ':order_brand' => $orderTerm,
        ':order_type' => $orderTerm,
    ];
}
$buyerStmt->execute($buyerParams);
$buyers = $buyerStmt->fetchAll();

$deletedBuyerSql = 'SELECT b.buyer_id, b.buyer_name, b.deleted_at, b.deleted_with_item,
            k.item_brand, k.item_type,
            CONCAT_WS(" ", deleter.first_name, deleter.last_name) AS deleted_by_name
     FROM buyers b
     JOIN cricket_kits k ON k.item_id = b.item_id
     LEFT JOIN users deleter ON deleter.user_id = b.deleted_by
     WHERE b.deleted_at IS NOT NULL';
$deletedBuyerParams = [];
if (!$ownerAccess) {
    $deletedBuyerSql .= ' AND b.added_by = :user_id';
    $deletedBuyerParams[':user_id'] = currentUserId();
}
$deletedBuyerSql .= ' ORDER BY b.deleted_at DESC';
$deletedBuyerStmt = $pdo->prepare($deletedBuyerSql);
$deletedBuyerStmt->execute($deletedBuyerParams);
$deletedBuyers = $deletedBuyerStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cricket Kit Shop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f7fb;
            color: #222;
        }
        h1 {
            margin-bottom: 20px;
        }
        h2 {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #eaf1ff;
        }
        a {
            color: #0b5fff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        form {
            max-width: 500px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 9px;
            box-sizing: border-box;
        }
        button {
            margin-top: 16px;
            padding: 10px 16px;
            background: #0b5fff;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
        }
        .message {
            margin-bottom: 10px;
            color: #056b3a;
            font-weight: bold;
        }
        body { background: linear-gradient(135deg, #eef4ff, #f7fafc); }
        body > h1, body > h2, body > .table-scroll, body > form, body > .customer-note, body > div { max-width: 1200px; margin-left: auto; margin-right: auto; }
        body > div:first-of-type { display: flex; padding: 18px 22px; border-radius: 12px; background: #fff; box-shadow: 0 8px 24px rgba(22, 50, 92, .09); }
        h1 { color: #173a63; }
        h2 { color: #245b91; }
        .table-scroll { max-height: 420px; overflow: auto; margin-bottom: 30px; border-radius: 10px; box-shadow: 0 8px 24px rgba(22, 50, 92, .09); }
        table { min-width: 1000px; margin-bottom: 0; border-radius: 10px; overflow: hidden; }
        .table-scroll th { position: sticky; top: 0; z-index: 1; }
        th { background: #dbe9f9; color: #173a63; }
        tr:nth-child(even) td { background: #f8fbff; }
        form { border-top: 4px solid #145da0; }
        input, select, textarea { border: 1px solid #ccd6e0; border-radius: 5px; }
        button { background: #145da0; transition: transform .15s, background .15s; }
        button:hover { background: #0d477d; transform: translateY(-1px); }
        .actions a, body a { display: inline-block; }
        .customer-note { max-width: 1160px; padding: 14px 18px; background: #e8f4ff; border-left: 4px solid #145da0; border-radius: 6px; }
        .order-details { max-width: 1200px; margin: 28px auto 0; padding: 14px; background: #fff; border-radius: 10px; box-shadow: 0 8px 24px rgba(22, 50, 92, .09); }
        .order-details summary { color: #145da0; font-weight: bold; cursor: pointer; padding: 6px; }
        .order-details table { margin-top: 14px; }
        .search-form { display: flex; align-items: center; gap: 10px; max-width: 700px; margin-bottom: 16px; padding: 12px 16px; }
        .search-form label { margin: 0; white-space: nowrap; }
        .search-form input { flex: 1; min-width: 0; }
        .search-form button { margin: 0; white-space: nowrap; }
        .actions form { max-width: none; margin: 0; padding: 0; border: 0; box-shadow: none; }
        .actions button { margin: 0; padding: 0; background: transparent; color: #0b5fff; font: inherit; }
        .actions button:hover { background: transparent; transform: none; text-decoration: underline; }
        .table-scroll form { max-width: none; margin: 0; padding: 0; border: 0; background: transparent; box-shadow: none; }
        .table-scroll button { margin: 0; }
    </style>
</head>
<body>
    <!-- Escape dynamic text at the HTML output point to prevent stored or reflected markup from executing. -->
    <div style="display:flex;justify-content:space-between;align-items:center"><h1>Cricket Kit Shop</h1><div>Welcome, <strong><?= htmlspecialchars(currentUserName()) ?></strong> | <a href="logout.php">Logout</a></div></div>

    <?php if ($ownerAccess): ?>
    <h2>Cricket Kits</h2>
    <form class="search-form" method="get" action="index.php">
        <label for="kit_search">Search kits</label>
        <input id="kit_search" name="kit_search" type="search" value="<?= htmlspecialchars($kitSearch) ?>" placeholder="ID, brand, type, or price">
        <button type="submit">Search</button>
        <?php if ($kitSearch !== ''): ?><a href="index.php">Clear</a><?php endif; ?>
    </form>
    <div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Brand</th>
                <th>Type</th>
                <th>Price</th>
                <th>Date Added</th>
                <th>Added By</th>
                <th>Last Updated</th>
                <th>Last Updated By</th>
                <?php if ($ownerAccess): ?><th>Action</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($kits)): ?>
                <tr>
                    <td colspan="<?= $ownerAccess ? 9 : 8 ?>">No kits found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($kits as $kit): ?>
                    <tr>
                        <td><?= htmlspecialchars($kit['item_id']) ?></td>
                        <!-- Stored kit text is HTML-escaped before it is rendered in the table. -->
                        <td><?= htmlspecialchars($kit['item_brand']) ?></td>
                        <td><?= htmlspecialchars($kit['item_type']) ?></td>
                        <td>₱<?= number_format((float) $kit['price'], 2) ?></td>
                        <td><?= htmlspecialchars($kit['date_added']) ?></td>
                        <td><?= htmlspecialchars($kit['added_by_name'] ?: 'Unknown') ?></td>
                        <td><?= htmlspecialchars($kit['last_updated']) ?></td>
                        <td><?= htmlspecialchars($kit['last_updated_by_name'] ?: 'Unknown') ?></td>
                        <?php if ($ownerAccess): ?>
                        <td>
                            <div class="actions">
                                <a href="editOrder.php?id=<?= (int) $kit['item_id'] ?>">Edit</a>
                                <form method="post" action="core/handleForms.php" onsubmit="return confirm('Delete this kit and all related buyer orders?')">
                                    <input type="hidden" name="delete_item_id" value="<?= (int) $kit['item_id'] ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <h2>Deleted Cricket Kits</h2>
    <div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Brand</th>
                <th>Type</th>
                <th>Deleted At</th>
                <th>Deleted By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($deletedKits)): ?>
                <tr><td colspan="6">No deleted kits.</td></tr>
            <?php else: ?>
                <?php foreach ($deletedKits as $kit): ?>
                    <tr>
                        <td><?= (int) $kit['item_id'] ?></td>
                        <td><?= htmlspecialchars($kit['item_brand']) ?></td>
                        <td><?= htmlspecialchars($kit['item_type']) ?></td>
                        <td><?= htmlspecialchars($kit['deleted_at']) ?></td>
                        <td><?= htmlspecialchars($kit['deleted_by_name'] ?: 'Unknown') ?></td>
                        <td>
                            <form method="post" action="core/handleForms.php">
                                <input type="hidden" name="restore_item_id" value="<?= (int) $kit['item_id'] ?>">
                                <button type="submit">Restore</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>

    <?php if ($ownerAccess): ?>
    <h2>Add New Kit</h2>
    <form method="POST" action="core/handleForms.php">
        <input type="hidden" name="addKitBtn" value="1">

        <label for="item_brand">Brand</label>
        <input id="item_brand" name="item_brand" type="text" required>

        <label for="item_type">Type</label>
        <input id="item_type" name="item_type" type="text" required>

        <label for="price">Price</label>
        <input id="price" name="price" type="number" min="1" step="0.01" required>

        <button type="submit">Add Kit</button>
    </form>
    <?php endif; ?>

    <?php if (!$ownerAccess): ?>
    <h2>Place an Order</h2>
    <?php endif; ?>

    <?php if (!$ownerAccess): ?>
    <form method="POST" action="core/handleForms.php">
        <input type="hidden" name="placeOrderBtn" value="1">

        <label for="buyer_name">Buyer Name</label>
        <input id="buyer_name" name="buyer_name" type="text" required>

        <label for="item_id">Kit</label>
        <select id="item_id" name="item_id" required>
            <option value="">Select a kit</option>
            <?php foreach ($kits as $kit): ?>
                <!-- Escape stored labels; the numeric identifier is cast before entering the attribute. -->
                <option value="<?= (int) $kit['item_id'] ?>">
                    <?= htmlspecialchars($kit['item_brand']) ?> - <?= htmlspecialchars($kit['item_type']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required>

        <label for="phone">Phone</label>
        <input id="phone" name="phone" type="text" required>

        <label for="shipping_address">Shipping Address</label>
        <textarea id="shipping_address" name="shipping_address" rows="3" required></textarea>

        <label for="quantity">Quantity</label>
        <input id="quantity" name="quantity" type="number" min="1" value="1" required>

        <button type="submit">Place Order</button>
    </form>
    <?php endif; ?>

    <?php if ($ownerAccess): ?>
    <h2>Buyer Orders</h2>
    <?php else: ?>
    <details class="order-details">
        <summary>View my order details</summary>
    <?php endif; ?>
    <form class="search-form" method="get" action="index.php">
        <label for="order_search">Search orders</label>
        <input id="order_search" name="order_search" type="search" value="<?= htmlspecialchars($orderSearch) ?>" placeholder="ID, buyer, contact, or kit">
        <button type="submit">Search</button>
        <?php if ($orderSearch !== ''): ?><a href="index.php">Clear</a><?php endif; ?>
    </form>
    <div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>Buyer ID</th>
                <th>Buyer</th>
                <th>Kit</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Qty</th>
                <th>Order Date</th>
                <th>Added By</th>
                <th>Last Updated</th>
                <th>Last Updated By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($buyers)): ?>
                <tr>
                    <td colspan="12"><?= $orderSearch === '' ? 'No buyer orders yet.' : 'No matching orders found.' ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($buyers as $buyer): ?>
                    <tr>
                        <!-- Escape stored buyer and kit text at output so submitted markup stays inert. -->
                        <td><?= htmlspecialchars($buyer['buyer_id']) ?></td>
                        <td><?= htmlspecialchars($buyer['buyer_name']) ?></td>
                        <td><?= htmlspecialchars($buyer['item_brand']) ?> - <?= htmlspecialchars($buyer['item_type']) ?></td>
                        <td><?= htmlspecialchars($buyer['email']) ?></td>
                        <td><?= htmlspecialchars($buyer['phone']) ?></td>
                        <td><?= htmlspecialchars($buyer['shipping_address']) ?></td>
                        <td><?= htmlspecialchars($buyer['quantity']) ?></td>
                        <td><?= htmlspecialchars($buyer['order_date']) ?></td>
                        <td><?= htmlspecialchars($buyer['added_by_name'] ?: 'Unknown') ?></td>
                        <td><?= htmlspecialchars($buyer['last_updated']) ?></td>
                        <td><?= htmlspecialchars($buyer['last_updated_by_name'] ?: 'Unknown') ?></td>
                        <td>
                            <div class="actions">
                                <a href="editBuyerOrder.php?id=<?= (int) $buyer['buyer_id'] ?>">Edit</a>
                                <form method="post" action="core/handleForms.php" onsubmit="return confirm('Delete this order?')">
                                    <input type="hidden" name="delete_buyer_id" value="<?= (int) $buyer['buyer_id'] ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <h2>Deleted Buyer Orders</h2>
    <div class="table-scroll">
    <table>
        <thead>
            <tr>
                <th>Buyer ID</th>
                <th>Buyer</th>
                <th>Kit</th>
                <th>Deleted At</th>
                <th>Deleted By</th>
                <th>Restore</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($deletedBuyers)): ?>
                <tr><td colspan="6">No deleted orders.</td></tr>
            <?php else: ?>
                <?php foreach ($deletedBuyers as $buyer): ?>
                    <tr>
                        <td><?= (int) $buyer['buyer_id'] ?></td>
                        <td><?= htmlspecialchars($buyer['buyer_name']) ?></td>
                        <td><?= htmlspecialchars($buyer['item_brand']) ?> - <?= htmlspecialchars($buyer['item_type']) ?></td>
                        <td><?= htmlspecialchars($buyer['deleted_at']) ?></td>
                        <td><?= htmlspecialchars($buyer['deleted_by_name'] ?: 'Unknown') ?></td>
                        <td>
                            <?php if ((int) $buyer['deleted_with_item'] === 1): ?>
                                Restore the deleted kit to restore this order.
                            <?php else: ?>
                                <form method="post" action="core/handleForms.php">
                                    <input type="hidden" name="restore_buyer_id" value="<?= (int) $buyer['buyer_id'] ?>">
                                    <button type="submit">Restore</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <?php if (!$ownerAccess): ?></details><?php endif; ?>
</body>
</html>
