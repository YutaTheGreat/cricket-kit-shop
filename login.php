<?php
require_once __DIR__ . '/core/dbConfig.php';
require_once __DIR__ . '/core/auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1');
    $stmt->execute([':username' => $username, ':email' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php');
        exit();
    }
    $error = 'Invalid username/email or password.';
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login</title>
<style>body{font-family:Arial;background:#eef3f9;margin:0;display:grid;place-items:center;min-height:100vh}.card{width:min(420px,90%);background:white;padding:28px;border-radius:12px;box-shadow:0 8px 24px #0002}label{display:block;margin-top:14px;font-weight:bold}input{box-sizing:border-box;width:100%;padding:10px;margin-top:5px}button{width:100%;margin-top:18px;padding:11px;background:#145da0;color:white;border:0;border-radius:5px}.error{color:#b00020}.links{text-align:center;margin-top:16px}</style>
</head>
<body><main class="card"><h1>Cricket Kit Shop Login</h1><?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?><form method="post"><label for="username">Username or email</label><input id="username" name="username" required><label for="password">Password</label><input id="password" name="password" type="password" required><button>Login</button></form><div class="links"><a href="register.php">Create an account</a></div></main></body>
</html>
