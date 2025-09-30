<?php
// Simple test to check if PHP and database connection work
echo "<h2>Testing PHP and Database Connection</h2>";

// Test PHP
echo "<p>✓ PHP is working</p>";

// Test database connection
try {
    require_once __DIR__ . '/config.php';
    echo "<p>✓ Database connection successful</p>";
    echo "<p>✓ Database 'crop_site' created/exists</p>";
    echo "<p>✓ Users table created/exists</p>";
    
    // Test if we can query the users table
    $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✓ Users table accessible. Current user count: " . $row['count'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index1.html'>Go to Login Page</a></p>";
echo "<p><a href='index.html'>Go to Home Page</a></p>";
?>
