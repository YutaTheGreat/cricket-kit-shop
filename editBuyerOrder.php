<?php
require_once __DIR__ . '/core/dbConfig.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buyer_id = isset($_POST['buyer_id']) ? (int) $_POST['buyer_id'] : 0;
    $buyer_name = trim($_POST['buyer_name'] ?? '');
    $item_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

    if ($buyer_id > 0 && $buyer_name !== '' && $item_id > 0 && $email !== '' && $phone !== '' && $shipping_address !== '' && $quantity > 0) {
        $sql = 'UPDATE buyers
                SET buyer_name = :buyer_name,
                    item_id = :item_id,
                    email = :email,
                    phone = :phone,
                    shipping_address = :shipping_address,
                    quantity = :quantity
                WHERE buyer_id = :buyer_id';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':buyer_name' => $buyer_name,
            ':item_id' => $item_id,
            ':email' => $email,
            ':phone' => $phone,
            ':shipping_address' => $shipping_address,
            ':quantity' => $quantity,
            ':buyer_id' => $buyer_id,
        ]);

        $message = 'Order updated successfully.';
    } else {
        $message = 'Please complete all fields.';
    }
}

$buyer_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$buyer = null;

if ($buyer_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM buyers WHERE buyer_id = :buyer_id');
    $stmt->execute([':buyer_id' => $buyer_id]);
    $buyer = $stmt->fetch();
}

if (!$buyer) {
    echo "<script>alert('Order not found.'); window.location.href='index.php';</script>";
    exit;
}

$kits = $pdo->query('SELECT item_id, item_brand, item_type FROM cricket_kits ORDER BY item_id DESC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buyer Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f7fb;
            color: #222;
        }
        .card {
            max-width: 600px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        button {
            margin-top: 20px;
            padding: 10px 18px;
            background: #0b5fff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .message {
            margin-bottom: 15px;
            color: green;
            font-weight: bold;
        }
        a {
            color: #0b5fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Edit Buyer Order</h1>

        <?php if ($message !== ''): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="editBuyerOrder.php?id=<?= (int) $buyer['buyer_id'] ?>">
            <input type="hidden" name="buyer_id" value="<?= (int) $buyer['buyer_id'] ?>">

            <label for="buyer_name">Buyer Name</label>
            <input id="buyer_name" name="buyer_name" type="text" value="<?= htmlspecialchars($buyer['buyer_name']) ?>" required>

            <label for="item_id">Kit</label>
            <select id="item_id" name="item_id" required>
                <?php foreach ($kits as $kit): ?>
                    <option value="<?= (int) $kit['item_id'] ?>" <?= ((int) $kit['item_id'] === (int) $buyer['item_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kit['item_brand']) ?> - <?= htmlspecialchars($kit['item_type']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= htmlspecialchars($buyer['email']) ?>" required>

            <label for="phone">Phone</label>
            <input id="phone" name="phone" type="text" value="<?= htmlspecialchars($buyer['phone']) ?>" required>

            <label for="shipping_address">Shipping Address</label>
            <textarea id="shipping_address" name="shipping_address" rows="3" required><?= htmlspecialchars($buyer['shipping_address']) ?></textarea>

            <label for="quantity">Quantity</label>
            <input id="quantity" name="quantity" type="number" min="1" value="<?= (int) $buyer['quantity'] ?>" required>

            <button type="submit">Update Order</button>
        </form>

        <p><a href="index.php">Back to shop</a></p>
    </div>
</body>
</html>
