<?php
require_once __DIR__ . '/config.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'delete':
        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        if ($userId > 0) {
            $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                echo "✅ User deleted successfully";
            } else {
                echo "❌ Error deleting user: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "❌ Invalid user ID";
        }
        break;
        
    case 'clear_all':
        if ($mysqli->query("TRUNCATE TABLE users")) {
            echo "✅ All user data cleared successfully";
        } else {
            echo "❌ Error clearing data: " . $mysqli->error;
        }
        break;
        
    case 'export':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_H-i-s') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Username', 'Email', 'Registration Date']);
        
        $result = $mysqli->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['username'],
                $row['email'],
                $row['created_at']
            ]);
        }
        fclose($output);
        break;
        
    default:
        echo "❌ Invalid action";
}
?>
