<?php
require_once __DIR__ . '/core/dbConfig.php';
require_once __DIR__ . '/core/auth.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($firstName === '' || $lastName === '' || $address === '' || $age === false || $age < 1 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $username === '' || strlen($password) < 6) {
        $error = 'Complete every field. Age must be valid and password must be at least 6 characters.';
    } else {
        $check = $pdo->prepare('SELECT user_id FROM users WHERE username = :username OR email = :email LIMIT 1');
        $check->execute([':username' => $username, ':email' => $email]);
        if ($check->fetch()) {
            $error = 'Username or email is already registered.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, address, age, phone, email, username, password, role) VALUES (:first_name, :last_name, :address, :age, :phone, :email, :username, :password, :role)');
            $stmt->execute([':first_name' => $firstName, ':last_name' => $lastName, ':address' => $address, ':age' => $age, ':phone' => $phone, ':email' => $email, ':username' => $username, ':password' => password_hash($password, PASSWORD_DEFAULT), ':role' => 'customer']);
            header('Location: login.php?registered=1');
            exit();
        }
    }
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Register</title><style>body{font-family:Arial;background:#eef3f9;margin:0}.card{max-width:620px;margin:30px auto;background:white;padding:28px;border-radius:12px;box-shadow:0 8px 24px #0002}.grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}label{display:block;font-weight:bold;margin-top:4px}input,textarea{box-sizing:border-box;width:100%;padding:10px;margin-top:5px}button{margin-top:18px;width:100%;padding:11px;background:#145da0;color:white;border:0;border-radius:5px}.full{grid-column:1/-1}.error{color:#b00020}@media(max-width:600px){.grid{grid-template-columns:1fr}}</style></head><body><main class="card"><h1>Register User</h1><?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?><form method="post"><div class="grid"><div><label>First name</label><input name="first_name" required></div><div><label>Last name</label><input name="last_name" required></div><div><label>Age</label><input name="age" type="number" min="1" required></div><div><label>Phone</label><input name="phone"></div><div><label>Email</label><input name="email" type="email" required></div><div><label>Username</label><input name="username" required></div><div><label>Password</label><input name="password" type="password" minlength="6" required></div><div class="full"><label>Address</label><textarea name="address" required></textarea></div></div><button>Register</button></form><p><a href="login.php">Already have an account? Login</a></p></main></body></html>
