<?php

function storeOrderImage(array $file): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK || !isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid order image upload.');
    }

    $fileSize = filesize($file['tmp_name']);
    if ($fileSize === false || $fileSize < 1 || $fileSize > 5 * 1024 * 1024) {
        throw new RuntimeException('Invalid order image upload.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        throw new RuntimeException('Unable to inspect order image.');
    }
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($extensions[$mimeType]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Unsupported order image type.');
    }

    $directory = __DIR__ . '/../uploads/order-images';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create order image directory.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('Unable to save order image.');
    }

    return 'uploads/order-images/' . $filename;
}

function deleteOrderImage(?string $imagePath): void
{
    if ($imagePath === null || !preg_match('/\Auploads\/order-images\/[a-f0-9]{32}\.(?:jpg|png|webp)\z/', $imagePath)) {
        return;
    }

    $fullPath = __DIR__ . '/../' . $imagePath;
    if (is_file($fullPath) && !unlink($fullPath)) {
        throw new RuntimeException('Unable to remove replaced order image.');
    }
}
