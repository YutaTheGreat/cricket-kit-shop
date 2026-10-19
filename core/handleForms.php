<?php
require_once __DIR__ . '/dbConfig.php';
require_once __DIR__ . '/../model/models.php';

// Handle create a cricket kit entry
if (isset($_POST['addKitBtn'])) {
    $item_brand = trim($_POST['item_brand'] ?? '');
    $item_type = trim($_POST['item_type'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);

    if ($item_brand !== '' && $item_type !== '' && $price !== false && $price > 0) {
        $success = insertCricketKit($pdo, $item_brand, $item_type, $price);

        if ($success) {
            header('Location: ../index.php?kit=added');
            exit();
        }
    }

    header('Location: ../index.php?kit=error');
    exit();
}

// Handle update cricket kit entry
if (isset($_POST['editKitBtn'])) {
    $item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $item_brand = trim($_POST['item_brand'] ?? '');
    $item_type = trim($_POST['item_type'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);

    if ($item_id !== false && $item_id > 0 && $item_brand !== '' && $item_type !== '' && $price !== false && $price > 0) {
        $success = updateCricketKit($pdo, $item_id, $item_brand, $item_type, $price);

        if ($success) {
            header('Location: ../index.php?kit=updated');
            exit();
        }
    }

    header('Location: ../index.php?kit=update_error');
    exit();
}

// Handle create buyer order
if (isset($_POST['placeOrderBtn'])) {
    $buyer_name = trim($_POST['buyer_name'] ?? '');
    $item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if (
        $buyer_name !== '' &&
        $item_id !== false && $item_id > 0 &&
        $email !== '' &&
        $phone !== '' &&
        $shipping_address !== '' &&
        $quantity !== false && $quantity > 0
    ) {
        $success = insertBuyer($pdo, $buyer_name, $item_id, $email, $phone, $shipping_address, $quantity);

        if ($success) {
            header('Location: ../index.php?order=placed');
            exit();
        }
    }

    header('Location: ../index.php?order=error');
    exit();
}

// Handle update buyer order
if (isset($_POST['editBuyerBtn'])) {
    $buyer_id = filter_input(INPUT_POST, 'buyer_id', FILTER_VALIDATE_INT);
    $buyer_name = trim($_POST['buyer_name'] ?? '');
    $item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if (
        $buyer_id !== false && $buyer_id > 0 &&
        $buyer_name !== '' &&
        $item_id !== false && $item_id > 0 &&
        $email !== '' &&
        $phone !== '' &&
        $shipping_address !== '' &&
        $quantity !== false && $quantity > 0
    ) {
        $success = updateBuyer($pdo, $buyer_id, $buyer_name, $item_id, $email, $phone, $shipping_address, $quantity);

        if ($success) {
            header('Location: ../index.php?order=updated');
            exit();
        }
    }

    header('Location: ../index.php?order=update_error');
    exit();
}

// Handle delete parent kit record
if (isset($_GET['delete_item_id'])) {
    $item_id = filter_input(INPUT_GET, 'delete_item_id', FILTER_VALIDATE_INT);

    if ($item_id !== false && $item_id > 0) {
        $success = deleteCricketKit($pdo, $item_id);

        if ($success) {
            header('Location: ../index.php?kit=deleted');
            exit();
        }
    }

    header('Location: ../index.php?kit=delete_error');
    exit();
}

// Handle delete buyer record
if (isset($_GET['delete_buyer_id'])) {
    $buyer_id = filter_input(INPUT_GET, 'delete_buyer_id', FILTER_VALIDATE_INT);

    if ($buyer_id !== false && $buyer_id > 0) {
        $success = deleteBuyer($pdo, $buyer_id);

        if ($success) {
            header('Location: ../index.php?order=deleted');
            exit();
        }
    }

    header('Location: ../index.php?order=delete_error');
    exit();
}
?>