<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        if (basename($scriptDir) === 'core') {
            $scriptDir = dirname($scriptDir);
        }
        header('Location: ' . rtrim($scriptDir, '/') . '/login.php');
        exit();
    }
}

function currentUserId(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function currentUserName(): string
{
    return trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?: 'User';
}

function isOwner(): bool
{
    return ($_SESSION['role'] ?? 'customer') === 'owner';
}

function requireOwner(): void
{
    requireLogin();
    if (!isOwner()) {
        header('Location: ../index.php?access=denied');
        exit();
    }
}
