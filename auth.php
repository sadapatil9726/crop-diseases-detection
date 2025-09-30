<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

function json_response($ok, $message, $extra = []) {
    echo json_encode(array_merge([
        'ok' => $ok,
        'message' => $message
    ], $extra));
    exit;
}

if ($action === 'register') {
    $username = input('username');
    $email = input('email');
    $password = input('password');

    if ($username === '' || $email === '' || $password === '') {
        json_response(false, 'All fields are required.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(false, 'Invalid email format.');
    }
    if (strlen($password) < 6) {
        json_response(false, 'Password must be at least 6 characters.');
    }

    // Check if user exists
    $stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
    if (!$stmt) json_response(false, 'DB error: ' . $mysqli->error);
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        json_response(false, 'Username or email already exists.');
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
    if (!$stmt) json_response(false, 'DB error: ' . $mysqli->error);
    $stmt->bind_param('sss', $username, $email, $hash);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        $stmt->close();
        json_response(true, 'Registration successful.', ['redirect' => 'index.html']);
    } else {
        $err = $stmt->error;
        $stmt->close();
        json_response(false, 'Registration failed: ' . $err);
    }
}

if ($action === 'login') {
    $username = input('username');
    $password = input('password');

    if ($username === '' || $password === '') {
        json_response(false, 'Username and password are required.');
    }

    // Allow login by username or email
    $stmt = $mysqli->prepare('SELECT id, username, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1');
    if (!$stmt) json_response(false, 'DB error: ' . $mysqli->error);
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $stmt->bind_result($id, $uname, $hash);

    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $uname;
        $stmt->close();
        json_response(true, 'Login successful.', ['redirect' => 'index.html']);
    } else {
        $stmt->close();
        json_response(false, 'Invalid credentials.');
    }
}

if ($action === 'logout') {
    session_destroy();
    json_response(true, 'Logged out.');
}

if ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        json_response(true, 'Authenticated', ['username' => $_SESSION['username']]);
    } else {
        json_response(false, 'Not authenticated');
    }
}

http_response_code(400);
json_response(false, 'Invalid action.');
