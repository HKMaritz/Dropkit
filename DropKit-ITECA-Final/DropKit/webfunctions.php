<?php

// Start session once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Bring in your PDO connection
require_once __DIR__ . '/db.php';// defines $pdo

// Authentication Helpers

/** Is a user logged in? */
function isLoggedIn(): bool {
    return isset($_SESSION['SeshKey']);
}

/** Is the logged in user an admin*/
function isAdmin(): bool {
    return (bool) ($_SESSION['SeshKey']['is_admin'] ?? false);
}

/**Get the current user ID or null*/
function getLoggedInUserId(): ?int {
    return $_SESSION['SeshKey']['user_id'] ?? null;
}

/**Get current username or “User”*/
function getLoggedInUsername(): string {
    return $_SESSION['SeshKey']['name'] ?? 'User';
}

/** Get current user email or nul*/
function getLoggedInEmail(): ?string {
    return $_SESSION['SeshKey']['email'] ?? null;
}


// Cart Helpers for session  cookie  database
/** Persist the session cart into a cookie for 30 days*/
function saveCartToCookie(): void {
    $cart = $_SESSION['seshCart'] ?? [];
    setcookie(
        'seshCart',
        json_encode($cart),
        [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'httponly'=> true,
        ]
    );
}

/** Load the cart from the cookie into the session*/
function loadCartFromCookie(): void {
    if (! isset($_SESSION['seshCart'])) {
        $cookie = $_COOKIE['seshCart'] ?? '';
        $data   = json_decode($cookie, true);
        $_SESSION['seshCart'] = is_array($data) ? $data : [];
    }
}

/**Load the cart items from the database into the session*/
function loadCartFromDB(): void {
    if (! isLoggedIn()) {
        return;
    }

    $uid = getLoggedInUserId();
    $_SESSION['seshCart'] = [];

    global $pdo;
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM cart_items WHERE user_id = ?");
    $stmt->execute([$uid]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $_SESSION['seshCart'][(int)$row['product_id']] = (int)$row['quantity'];
    }
}

/** Save the session cart into the database overwriting the existing*/
function saveCartToDB(): void {
    if (! isLoggedIn()) {
        return;
    }

    $uid = getLoggedInUserId();
    global $pdo;

    // Delete the  old items
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->execute([$uid]);

    // Insert new ones
    if (! empty($_SESSION['seshCart'])) {
        $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($_SESSION['seshCart'] as $pid => $qty) {
            $stmt->execute([$uid, $pid, $qty]);
        }
    }
}

/**Clear the cart entirely (session, cookie, and DB if desired).*/
function clearCart(bool $clearDB = true): void {
    // Clear session + cookie
    $_SESSION['seshCart'] = [];
    setcookie('seshCart', '', time() - 3600, '/');

    // Optionally clear DB
    if ($clearDB && isLoggedIn()) {
        $uid  = getLoggedInUserId();
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$uid]);
    }
}

/** Retrieve the cart for a given user ID from the database*/
function getCartFromDB(int $uid): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM cart_items WHERE user_id = ?");
    $stmt->execute([$uid]);
    $rows = $stmt->fetchAll();

    $cart = [];
    foreach ($rows as $row) {
        $cart[] = [
            'product_id' => (int)$row['product_id'],
            'quantity' => (int)$row['quantity'],
        ];
    }
    return $cart;
}
