<?php
require_once __DIR__ . '/config.php';

// Staff authorization
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'staff') {
    header("Location: login.php");
    exit;
}

$message = '';
$messageType = '';
$ticket = [];

$tktId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($tktId === 0) {
    header("Location: staff_tickets.php");
    exit;
}

// Fetch ticket
$stmt = $mysqli->prepare("SELECT TKT_ID, BK_ID, SHW_ID, ST_ID, TKT_PRICE FROM " . TBL_TICKET . " WHERE TKT_ID = ?");
$stmt->bind_param("i", $tktId);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();
$stmt->close();

if (!$ticket) {
    header("Location: staff_tickets.php");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tktPrice = floatval($_POST['tkt_price'] ?? 0);

    if ($tktPrice <= 0) {
        $message = "Ticket price must be greater than 0.";
        $messageType = "error";
    } else {
        $stmt = $mysqli->prepare("UPDATE " . TBL_TICKET . " SET TKT_PRICE = ? WHERE TKT_ID = ?");
        $stmt->bind_param("di", $tktPrice, $tktId);

        if ($stmt->execute()) {
            $message = "Ticket updated successfully!";
            $messageType = "success";
            // Refresh ticket data
            $stmt = $mysqli->prepare("SELECT TKT_ID, BK_ID, SHW_ID, ST_ID, TKT_PRICE FROM " . TBL_TICKET . " WHERE TKT_ID = ?");
            $stmt->bind_param("i", $tktId);
            $stmt->execute();
            $result = $stmt->get_result();
            $ticket = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "Error updating ticket: " . $stmt->error;
            $messageType = "error";
        }
    }
}

$user = $_SESSION['user'];
$userName = $user['name'] ?? 'Staff';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Ticket - Staff</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/home.css">
    <style>
        .staff-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .form-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #FFD700;
        }
        .form-header h1 {
            color: #EDE6D6;
            margin: 0 0 10px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #EDE6D6;
            font-weight: bold;
            margin-bottom: 8px;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #383838;
            border-radius: 5px;
            background: #282828;
            color: #EDE6D6;
            font-family: Poppins, sans-serif;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 5px rgba(255, 215, 0, 0.3);
        }
        input:disabled {
            background: #1a1a1a;
            color: #A9A9A9;
            cursor: not-allowed;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            transition: opacity 0.3s;
        }
        .btn-submit {
            background: #FFD700;
            color: #1a1a1a;
        }
        .btn-submit:hover {
            opacity: 0.8;
        }
        .btn-cancel {
            background: #383838;
            color: #A9A9A9;
            text-decoration: none;
            text-align: center;
        }
        .btn-cancel:hover {
            opacity: 0.8;
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
        .back-btn {
            color: #FFD700;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
        .read-only-note {
            color: #A9A9A9;
            font-size: 12px;
            margin-top: 5px;
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
        <a href="staff_tickets.php" class="back-btn">← Back to Tickets</a>

        <div class="form-header">
            <h1><i class="fas fa-edit"></i> Edit Ticket</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="tkt_id">Ticket ID</label>
                <input type="number" id="tkt_id" value="<?php echo htmlspecialchars($ticket['TKT_ID']); ?>" disabled>
                <div class="read-only-note">Read-only field</div>
            </div>

            <div class="form-group">
                <label for="bk_id">Booking ID</label>
                <input type="number" id="bk_id" value="<?php echo htmlspecialchars($ticket['BK_ID']); ?>" disabled>
                <div class="read-only-note">Read-only field</div>
            </div>

            <div class="form-group">
                <label for="shw_id">Showing ID</label>
                <input type="number" id="shw_id" value="<?php echo htmlspecialchars($ticket['SHW_ID']); ?>" disabled>
                <div class="read-only-note">Read-only field</div>
            </div>

            <div class="form-group">
                <label for="st_id">Seat ID</label>
                <input type="number" id="st_id" value="<?php echo htmlspecialchars($ticket['ST_ID']); ?>" disabled>
                <div class="read-only-note">Read-only field</div>
            </div>

            <div class="form-group">
                <label for="tkt_price">Ticket Price <span style="color: #ff6b6b;">*</span></label>
                <input type="number" id="tkt_price" name="tkt_price" step="0.01" min="0" required value="<?php echo htmlspecialchars($ticket['TKT_PRICE']); ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-submit">Update Ticket</button>
                <a href="staff_tickets.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
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