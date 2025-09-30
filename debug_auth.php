<?php
// Debug version of auth.php to help troubleshoot
session_start();

echo "<h2>Debug Auth System</h2>";
echo "<p>POST data received:</p>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

if (isset($_POST['action'])) {
    echo "<p>Action: " . $_POST['action'] . "</p>";
    
    try {
        require_once __DIR__ . '/config.php';
        echo "<p>✓ Database connection successful</p>";
        
        if ($_POST['action'] === 'register') {
            $username = input('username');
            $email = input('email');
            $password = input('password');
            
            echo "<p>Username: $username</p>";
            echo "<p>Email: $email</p>";
            echo "<p>Password length: " . strlen($password) . "</p>";
            
            if ($username && $email && $password && strlen($password) >= 6) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $username, $email, $hash);
                
                if ($stmt->execute()) {
                    echo "<p>✓ Registration successful!</p>";
                    echo "<p><a href='index1.html'>Back to Login</a></p>";
                } else {
                    echo "<p>❌ Registration failed: " . $stmt->error . "</p>";
                }
                $stmt->close();
            } else {
                echo "<p>❌ Invalid input data</p>";
            }
        }
        
        if ($_POST['action'] === 'login') {
            $username = input('username');
            $password = input('password');
            
            echo "<p>Username: $username</p>";
            echo "<p>Password length: " . strlen($password) . "</p>";
            
            $stmt = $mysqli->prepare('SELECT id, username, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1');
            $stmt->bind_param('ss', $username, $username);
            $stmt->execute();
            $stmt->bind_result($id, $uname, $hash);
            
            if ($stmt->fetch() && password_verify($password, $hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $uname;
                echo "<p>✓ Login successful!</p>";
                echo "<p><a href='index.html'>Go to Home Page</a></p>";
            } else {
                echo "<p>❌ Invalid credentials</p>";
            }
            $stmt->close();
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>No action specified</p>";
}

echo "<hr>";
echo "<p><a href='index1.html'>Back to Login Page</a></p>";
echo "<p><a href='test.php'>Test Database Connection</a></p>";
?>
