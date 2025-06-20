<?php

//Enable full error reporting

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bootstrap your PDO connection and cart helpers
require_once __DIR__ . '/db.php';           
require_once __DIR__ . '/webfunctions.php'; 

// no session start here webfunctions already started it

$errors         = [];
$justRegistered = (isset($_GET['registered']) && $_GET['registered'] === '1');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Collect and validate input
    $email    = trim($_POST['email']    ?? '');
    $password =           $_POST['password'] ?? '';

    if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if ($password === '') {
        $errors[] = "Please enter your password.";
    }

    // If no validation errors, attempt lookup
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT id, username, email, password, is_admin
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(); // returns false if not found

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // :) Auth succeeded: set session
                $_SESSION['SeshKey'] = [
                    'user_id'  => $user['id'],
                    'name' => $user['username'],
                    'email' => $user['email'],
                    'is_admin' => $user['is_admin'],
                ];

                //  Sync cart
                loadCartFromDB();
                saveCartToCookie();

                //  Redirect
                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Log In – DropKit</title>
  <style>
    body {
      font-family: sans-serif;
      background: #f5f5f5;
      margin: 0; padding: 0;
    }
    .form-container {
      max-width: 400px;
      margin: 80px auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .form-container h2 {
      margin-bottom: 20px;
      text-align: center;
    }
    .form-group {
      margin-bottom: 15px;
    }
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
    }
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }
    .error-list {
      background: #ffe6e6;
      border: 1px solid #ff9999;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 15px;
      color: #660000;
    }
    .success-msg {
      background: #e6ffe6;
      border: 1px solid #99ff99;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 15px;
      color: #006600;
      text-align: center;
    }
    .btn-primary-large {
      width: 100%;
      padding: 12px;
      background-color: #6c63ff;
      color: #fff;
      font-size: 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .btn-primary-large:hover {
      background-color: #5848d2;
      transform: scale(1.02);
    }
    .no-account {
      margin-top: 15px;
      text-align: center;
      font-size: 0.9rem;
    }
    .no-account a {
      color: #6c63ff;
      text-decoration: none;
    }
    .no-account a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Log In</h2>

    <?php if ($justRegistered): ?>
      <div class="success-msg">
        Registration successful! You may now log in.
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="error-list">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="login.php">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input
          type="email"
          id="email"
          name="email"
          value="<?= htmlspecialchars($email ?? '') ?>"
          required
        />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          required
        />
      </div>

      <button type="submit" class="btn-primary-large">
        Log In
      </button>
    </form>

    <div class="no-account">
      Don’t have an account?
      <a href="register.php">Register here</a>.
    </div>
  </div>
</body>
</html>
