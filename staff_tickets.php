<?php
require_once __DIR__ . '/config.php';

// Staff authorization
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'staff') {
    header("Location: login.php");
    exit;
}

$message = '';
$messageType = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $tktId = intval($_POST['tkt_id']);
    $stmt = $mysqli->prepare("DELETE FROM " . TBL_TICKET . " WHERE TKT_ID = ?");
    $stmt->bind_param("i", $tktId);
    if ($stmt->execute()) {
        $message = "Ticket deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Error deleting ticket: " . $stmt->error;
        $messageType = "error";
    }
    $stmt->close();
}

// Fetch all tickets with related info
$query = "SELECT t.TKT_ID, t.TKT_PRICE, t.BK_ID, t.SHW_ID, t.ST_ID, 
                 b.BK_DATE, u.USR_FNAME, m.MOV_TITLE, s.ST_LABEL
          FROM " . TBL_TICKET . " t
          LEFT JOIN " . TBL_BOOKING . " b ON t.BK_ID = b.BK_ID
          LEFT JOIN " . TBL_USR . " u ON b.USR_ID = u.USR_ID
          LEFT JOIN " . TBL_SHOWING . " sh ON t.SHW_ID = sh.SHW_ID
          LEFT JOIN " . TBL_MOVIE . " m ON sh.MOV_ID = m.MOV_ID
          LEFT JOIN " . TBL_SEAT . " s ON t.ST_ID = s.ST_ID
          ORDER BY t.TKT_ID DESC";
$result = $mysqli->query($query);
$tickets = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}

$user = $_SESSION['user'];
$userName = $user['name'] ?? 'Staff';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Tickets - Staff</title>
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
        .note {
            background: #282828;
            padding: 15px;
            border-left: 4px solid #FFD700;
            margin-bottom: 20px;
            border-radius: 4px;
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
            <h1><i class="fas fa-ticket-alt"></i> Manage Tickets (TKT)</h1>
        </div>

        <div class="note">
            <i class="fas fa-info-circle"></i> <strong>Note:</strong> Tickets are automatically created when you add a booking. You can edit or delete tickets here, but new tickets should be created through the Bookings section.
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (count($tickets) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Movie</th>
                        <th>Seat</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['TKT_ID']); ?></td>
                            <td><?php echo htmlspecialchars($t['BK_ID']); ?></td>
                            <td><?php echo htmlspecialchars($t['USR_FNAME'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($t['MOV_TITLE'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($t['ST_LABEL'] ?? 'N/A'); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($t['TKT_PRICE'] ?? 0, 2)); ?></td>
                            <td>
                                <a href="staff_ticket_edit.php?id=<?php echo $t['TKT_ID']; ?>" class="action-btn edit-btn">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this ticket?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="tkt_id" value="<?php echo $t['TKT_ID']; ?>">
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
                <h2>No Tickets Found</h2>
                <p>Create bookings to automatically generate tickets.</p>
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