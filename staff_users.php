<?php
require_once __DIR__ . '/config.php';

// Staff authorization check
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'staff') {
    header("Location: login.php");
    exit;
}

$message = '';
$messageType = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $usrId = intval($_POST['usr_id']);
    $stmt = $mysqli->prepare("DELETE FROM " . TBL_USR . " WHERE USR_ID = ?");
    $stmt->bind_param("i", $usrId);
    if ($stmt->execute()) {
        $message = "User deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Error deleting user: " . $stmt->error;
        $messageType = "error";
    }
    $stmt->close();
}

// Fetch all users
$query = "SELECT USR_ID, USR_EMAIL, USR_FNAME, USR_ROLE FROM " . TBL_USR . " ORDER BY USR_ID DESC";
$result = $mysqli->query($query);
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$user = $_SESSION['user'];
$userName = $user['name'] ?? 'Staff';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Staff</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/home.css">
    <style>
        .staff-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #FFD700;
        }
        .page-header h1 {
            color: #EDE6D6;
            margin: 0;
        }
        .add-btn {
            display: inline-block;
            background: #FFD700;
            color: #1a1a1a;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        .add-btn:hover {
            background: #FFC700;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #2d5016;
            color: #90EE90;
        }
        .message.error {
            background: #5c1a1a;
            color: #ff6b6b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #282828;
            border: 1px solid #383838;
            border-radius: 5px;
            overflow: hidden;
        }
        th {
            background: #1a1a1a;
            color: #FFD700;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #383838;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #383838;
            color: #A9A9A9;
        }
        tr:hover {
            background: #323232;
        }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 5px;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
            transition: opacity 0.3s;
        }
        .edit-btn {
            background: #FFD700;
            color: #1a1a1a;
        }
        .edit-btn:hover {
            opacity: 0.8;
        }
        .delete-btn {
            background: #ff6b6b;
            color: white;
        }
        .delete-btn:hover {
            opacity: 0.8;
        }
        .back-btn {
            color: #FFD700;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #A9A9A9;
        }
    </style>
</head>
<body style="background: #1a1a1a; color: #EDE6D6;">

    <header class="header">
        <div class="header-left">
            <a href="employeeDashboard.php">
                <img src="images/themouse.png" alt="The Mouse Logo" class="logo-img">
            </a>
            <div class="headstamp">Staff Panel</div>
        </div>
        <div class="header-right">
            <span class="account-info"><?php echo htmlspecialchars($userName); ?></span>
            <a href="logout.php" class="account-icon" title="Sign Out" style="margin-left: 15px; color: #ff6b6b;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>

    <div class="staff-container">
        <a href="employeeDashboard.php" class="back-btn">← Back to Dashboard</a>

        <div class="page-header">
            <h1><i class="fas fa-users"></i> Manage Users (USR)</h1>
            <a href="staff_user_add.php" class="add-btn">+ Add New User</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (count($users) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['USR_ID']); ?></td>
                            <td><?php echo htmlspecialchars($u['USR_EMAIL']); ?></td>
                            <td><?php echo htmlspecialchars($u['USR_FNAME'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($u['USR_ROLE'] ?? 'Customer'); ?></td>
                            <td>
                                <a href="staff_user_edit.php?id=<?php echo $u['USR_ID']; ?>" class="action-btn edit-btn">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="usr_id" value="<?php echo $u['USR_ID']; ?>">
                                    <button type="submit" class="action-btn delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5; display: block; margin-bottom: 15px;"></i>
                <h2>No Users Found</h2>
                <p>Create your first user by clicking the "Add New User" button above.</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <?php
        if (file_exists(__DIR__ . '/footer.php')) {
            include __DIR__ . '/footer.php';
        } else {
            echo '<div class="footer-inner">© ' . date('Y') . ' The Mouse Cinema</div>';
        }
        ?>
    </footer>

</body>
</html>