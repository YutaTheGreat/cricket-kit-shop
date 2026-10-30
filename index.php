<?php
require_once __DIR__ . '/core/dbConfig.php';
require_once __DIR__ . '/core/auth.php';
requireLogin();
$ownerAccess = isOwner();

$stmt = $pdo->query(
    'SELECT k.item_id, k.item_brand, k.item_type, k.price, k.date_added, k.last_updated,
            CONCAT_WS(" ", u.first_name, u.last_name) AS added_by_name
     FROM cricket_kits k
     LEFT JOIN users u ON u.user_id = k.added_by
     ORDER BY k.item_id DESC'
);
$kits = $stmt->fetchAll();

$buyerSql = 'SELECT b.buyer_id, b.buyer_name, b.email, b.phone, b.shipping_address, b.quantity, b.order_date, b.last_updated,
            k.item_brand, k.item_type, k.price,
            CONCAT_WS(" ", u.first_name, u.last_name) AS added_by_name
     FROM buyers b
     JOIN cricket_kits k ON k.item_id = b.item_id
     LEFT JOIN users u ON u.user_id = b.added_by';
if (!isOwner()) {
    $buyerSql .= ' WHERE b.added_by = :user_id';
}
$buyerSql .= ' ORDER BY b.buyer_id DESC';
$buyerStmt = $pdo->prepare($buyerSql);
if (!isOwner()) {
    $buyerStmt->execute([':user_id' => currentUserId()]);
} else {
    $buyerStmt->execute();
}
$buyers = $buyerStmt->fetchAll();
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
        body > h1, body > h2, body > table, body > form, body > .customer-note, body > div { max-width: 1200px; margin-left: auto; margin-right: auto; }
        body > div:first-of-type { display: flex; padding: 18px 22px; border-radius: 12px; background: #fff; box-shadow: 0 8px 24px rgba(22, 50, 92, .09); }
        h1 { color: #173a63; }
        h2 { color: #245b91; }
        table { border-radius: 10px; overflow: hidden; }
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
    </style>
</head>
<body>
    <div style="display:flex;justify-content:space-between;align-items:center"><h1>Cricket Kit Shop</h1><div>Welcome, <strong><?= htmlspecialchars(currentUserName()) ?></strong> | <a href="logout.php">Logout</a></div></div>

    <?php if ($ownerAccess): ?>
    <h2>Cricket Kits</h2>
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
                <?php if ($ownerAccess): ?><th>Action</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($kits)): ?>
                <tr>
                    <td colspan="<?= $ownerAccess ? 8 : 7 ?>">No kits found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($kits as $kit): ?>
                    <tr>
                        <td><?= htmlspecialchars($kit['item_id']) ?></td>
                        <td><?= htmlspecialchars($kit['item_brand']) ?></td>
                        <td><?= htmlspecialchars($kit['item_type']) ?></td>
                        <td>₱<?= number_format((float) $kit['price'], 2) ?></td>
                        <td><?= htmlspecialchars($kit['date_added']) ?></td>
                        <td><?= htmlspecialchars($kit['added_by_name'] ?: 'Unknown') ?></td>
                        <td><?= htmlspecialchars($kit['last_updated']) ?></td>
                        <?php if ($ownerAccess): ?>
                        <td>
                            <div class="actions">
                                <a href="editOrder.php?id=<?= (int) $kit['item_id'] ?>">Edit</a>
                                <a href="core/handleForms.php?delete_item_id=<?= (int) $kit['item_id'] ?>" onclick="return confirm('Delete this kit and all related buyer orders?')">Delete</a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
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
                <option value="<?= (int) $kit['item_id'] ?>"><?= htmlspecialchars($kit['item_brand']) ?> - <?= htmlspecialchars($kit['item_type']) ?></option>
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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($buyers)): ?>
                <tr>
                    <td colspan="11">No buyer orders yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($buyers as $buyer): ?>
                    <tr>
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
                        <td>
                            <div class="actions">
                                <a href="editBuyerOrder.php?id=<?= (int) $buyer['buyer_id'] ?>">Edit</a>
                                <a href="core/handleForms.php?delete_buyer_id=<?= (int) $buyer['buyer_id'] ?>" onclick="return confirm('Delete this order?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if (!$ownerAccess): ?></details><?php endif; ?>
</body>
</html>
