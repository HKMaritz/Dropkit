<?php

// Enable  error reporting
ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);



//Bootstrap your PDO connection and session with webfunctions

require_once __DIR__ . '/db.php';           

require_once __DIR__ . '/webfunctions.php'; 



$errors  = [];

$success = false;



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Collect & trim input

    $username  = trim($_POST['username']         ?? '');

    $email  = trim($_POST['email']            ?? '');

    $password  =  $_POST['password'] ?? '';

    $confirmPassword =  $_POST['confirm_password'] ?? '';



    //Validate

    if ($username === '') {

        $errors[] = "Username is required.";

    }

    if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "A valid email address is required.";

    }

    if (strlen($password) < 6) {

        $errors[] = "Password must be at least 6 characters long.";

    }

    if ($password !== $confirmPassword) {

        $errors[] = "Password and confirmation do not match.";

    }

    // heck duplicates

    if (empty($errors)) {

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");

        $stmt->execute([$username, $email]);

        if ((int)$stmt->fetchColumn() > 0) {

            $errors[] = "That username or email is already taken.";

        }

    }
    //Insert new user

    if (empty($errors)) {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("

            INSERT INTO users (username, email, password, saved_cart, is_admin)

            VALUES (?, ?, ?, ?, ?)

        ");

        $stmt->execute([

            $username,

            $email,

            $hash,

            json_encode([]), // empty the art

            0 // is the admin false

        ]);

        $success = true;

    }

}
?>

<!DOCTYPE html>

<html lang="en">

<head>

  <meta charset="UTF-8">

  <title>Register – DropKit</title>

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

    input[type="text"],

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

    <h2>Create Account</h2>



    <?php if ($success): ?>

      <div class="success-msg">

        Registration successful! <a href="login.php">Log in here</a>.

      </div>

    <?php endif; ?>



    <?php if (!$success && !empty($errors)): ?>

      <div class="error-list">

        <ul>

          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>

        </ul>

      </div>

    <?php endif; ?>



    <?php if (! $success): ?>

      <form method="post" action="register.php">

        <div class="form-group">

          <label for="username">Username</label>

          <input
            type="text"
            id="username"
            name="username"

            value="<?= htmlspecialchars($username ?? '') ?>"

            required

          />

        </div>



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



        <div class="form-group">

          <label for="confirm_password">Confirm Password</label>

          <input

            type="password"
            id="confirm_password"
            name="confirm_password"

            required

          />

        </div>



        <button type="submit" class="btn-primary-large">Register</button>

      </form>

    <?php endif; ?>



    <div class="no-account">

      Already have an account? <a href="login.php">Log in here</a>.

    </div>

  </div>

</body>

</html>

