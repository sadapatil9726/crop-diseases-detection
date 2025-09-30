<?php
session_start();
require_once __DIR__ . '/config.php';

// Simple admin panel to view user data
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User Data</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: #4CAF50; color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; }
        .nav-links { text-align: center; margin: 20px 0; }
        .nav-links a { margin: 0 10px; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; }
        .nav-links a:hover { background: #1976D2; }
        .search-box { margin: 20px 0; }
        .search-box input { padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 5px; }
        .search-box button { padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Admin Panel - User Database</h1>
        
        <div class="nav-links">
            <a href="index.html">🏠 Home</a>
            <a href="index1.html">🔑 Login Page</a>
            <a href="test.php">🔧 Test DB</a>
            <a href="quick_test.html">⚡ Quick Test</a>
        </div>

        <?php
        try {
            // Get total users count
            $result = $mysqli->query("SELECT COUNT(*) as total FROM users");
            $totalUsers = $result->fetch_assoc()['total'];
            
            // Get recent registrations (last 7 days)
            $result = $mysqli->query("SELECT COUNT(*) as recent FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $recentUsers = $result->fetch_assoc()['recent'];
            
            echo '<div class="stats">';
            echo '<div class="stat-card"><div class="stat-number">' . $totalUsers . '</div><div>Total Users</div></div>';
            echo '<div class="stat-card"><div class="stat-number">' . $recentUsers . '</div><div>New This Week</div></div>';
            echo '</div>';
            
            // Search functionality
            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
            echo '<div class="search-box">';
            echo '<form method="GET">';
            echo '<input type="text" name="search" placeholder="Search by username or email..." value="' . htmlspecialchars($searchTerm) . '">';
            echo '<button type="submit">🔍 Search</button>';
            if ($searchTerm) {
                echo '<a href="admin.php" style="margin-left: 10px; color: #666;">Clear</a>';
            }
            echo '</form>';
            echo '</div>';
            
            // Build query based on search
            $query = "SELECT id, username, email, created_at FROM users";
            if ($searchTerm) {
                $query .= " WHERE username LIKE '%" . $mysqli->real_escape_string($searchTerm) . "%' OR email LIKE '%" . $mysqli->real_escape_string($searchTerm) . "%'";
            }
            $query .= " ORDER BY created_at DESC LIMIT 100";
            
            $result = $mysqli->query($query);
            
            if ($result && $result->num_rows > 0) {
                echo '<h2>📊 User Data' . ($searchTerm ? ' (Search: "' . htmlspecialchars($searchTerm) . '")' : '') . '</h2>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Registration Date</th><th>Actions</th></tr>';
                
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td><strong>' . htmlspecialchars($row['username']) . '</strong></td>';
                    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                    echo '<td>' . date('d M Y, H:i', strtotime($row['created_at'])) . '</td>';
                    echo '<td><button onclick="deleteUser(' . $row['id'] . ')" style="background: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">🗑️ Delete</button></td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                if ($searchTerm) {
                    echo '<p>❌ No users found matching "' . htmlspecialchars($searchTerm) . '"</p>';
                } else {
                    echo '<p>📝 No users registered yet. <a href="index1.html">Register first user</a></p>';
                }
            }
            
        } catch (Exception $e) {
            echo '<p style="color: red;">❌ Database Error: ' . $e->getMessage() . '</p>';
            echo '<p>Make sure XAMPP MySQL is running!</p>';
        }
        ?>
        
        <hr style="margin: 40px 0;">
        <h3>🛠️ Database Actions</h3>
        <div style="text-align: center;">
            <button onclick="exportData()" style="background: #2196F3; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px;">📥 Export Data</button>
            <button onclick="clearAllData()" style="background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px;">🗑️ Clear All Data</button>
        </div>
    </div>

    <script>
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('admin_actions.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=delete&user_id=' + userId
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                });
            }
        }
        
        function clearAllData() {
            if (confirm('⚠️ This will delete ALL user data! Are you sure?')) {
                if (confirm('🚨 FINAL WARNING: This cannot be undone!')) {
                    fetch('admin_actions.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=clear_all'
                    })
                    .then(response => response.text())
                    .then(data => {
                        alert(data);
                        location.reload();
                    });
                }
            }
        }
        
        function exportData() {
            window.open('admin_actions.php?action=export', '_blank');
        }
    </script>
</body>
</html>
