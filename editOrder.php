<?php
require_once __DIR__ . '/core/dbConfig.php';

$message = '';

$item_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$kit = null;

if ($item_id > 0) {
    $stmt = $pdo->prepare('SELECT item_id, item_brand, item_type, price FROM cricket_kits WHERE item_id = :id');
    $stmt->execute([':id' => $item_id]);
    $kit = $stmt->fetch();
}

if (!$kit) {
    echo "<script>alert('Kit not found.'); window.location.href='index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Cricket Kit</title>
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
        input {
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
        .error {
            color: red;
        }
        a {
            color: #0b5fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Edit Cricket Kit</h1>

        <?php if ($message !== ''): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="core/handleForms.php">
            <input type="hidden" name="editKitBtn" value="1">
            <input type="hidden" name="item_id" value="<?= (int) $kit['item_id'] ?>">

            <label for="item_brand">Brand</label>
            <input id="item_brand" name="item_brand" type="text" value="<?= htmlspecialchars($kit['item_brand']) ?>" required>

            <label for="item_type">Type</label>
            <input id="item_type" name="item_type" type="text" value="<?= htmlspecialchars($kit['item_type']) ?>" required>

            <label for="price">Price</label>
            <input id="price" name="price" type="number" min="1" step="1" value="<?= htmlspecialchars((string) $kit['price']) ?>" required>

            <button type="submit">Update Kit</button>
        </form>

        <p><a href="index.php">Back to list</a></p>
    </div>
</body>
</html>
