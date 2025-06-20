<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($username === '') $errors[] = "Username is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email address is required.";
    if ($password === '') $errors[] = "Password is required.";
    if ($confirm === '') $errors[] = "Please confirm your password.";
    if ($password !== '' && $confirm !== '' && $password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Username or email already taken.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $emptyCart = json_encode([]);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, saved_cart) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed, $emptyCart);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: login_pg.php?registered=1"); //if loged in
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register – DropKit</title>
  <link rel="stylesheet" href="style.css">
  <style>
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
    .already {
      margin-top: 15px;
      text-align: center;
      font-size: 0.9rem;
    }
    .already a {
      color: #6c63ff;
      text-decoration: none;
    }
    .already a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="form-container">
    <h2>Create an Account</h2>

    <?php if (!empty($errors)): ?>
      <div class="error-list">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="register.php">
      <div class="form-group">
        <label for="username">Username</label>
        <input 
          type="text" 
          id="username" 
          name="username" 
          value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input 
          type="email" 
          id="email" 
          name="email" 
          value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input 
          type="password" 
          id="password" 
          name="password">
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input 
          type="password" 
          id="confirm_password" 
          name="confirm_password">
      </div>

      <button type="submit" class="btn-primary-large">Register</button>
    </form>

    <div class="already">
      Already have an account? <a href="login.php">Log in here</a>.
    </div>
  </div>

</body>
</html>
