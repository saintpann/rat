<?php
require_once __DIR__ . '/config.php';

// Staff authorization
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'staff') {
    header("Location: login.php");
    exit;
}

$message = '';
$messageType = '';
$booking = [];

$bkId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($bkId === 0) {
    header("Location: staff_bookings.php");
    exit;
}

// Fetch booking
$stmt = $mysqli->prepare("SELECT BK_ID, USR_ID, BK_DATE, BK_TOTAL_COST FROM " . TBL_BOOKING . " WHERE BK_ID = ?");
$stmt->bind_param("i", $bkId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    header("Location: staff_bookings.php");
    exit;
}

// Get available customers
$usersQuery = "SELECT USR_ID, USR_FNAME, USR_EMAIL FROM " . TBL_USR . " WHERE USR_ROLE = 'Customer' ORDER BY USR_FNAME";
$usersResult = $mysqli->query($usersQuery);
$users = [];
if ($usersResult) {
    while ($row = $usersResult->fetch_assoc()) {
        $users[] = $row;
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usrId = intval($_POST['usr_id'] ?? 0);
    $bkDate = trim($_POST['bk_date'] ?? '');
    $bkTotal = floatval($_POST['bk_total_cost'] ?? 0);

    if (empty($usrId) || empty($bkDate) || $bkTotal <= 0) {
        $message = "All fields are required and must be valid.";
        $messageType = "error";
    } else {
        $stmt = $mysqli->prepare("UPDATE " . TBL_BOOKING . " SET USR_ID = ?, BK_DATE = ?, BK_TOTAL_COST = ? WHERE BK_ID = ?");
        $stmt->bind_param("isdi", $usrId, $bkDate, $bkTotal, $bkId);

        if ($stmt->execute()) {
            $message = "Booking updated successfully!";
            $messageType = "success";
            // Refresh booking data
            $stmt = $mysqli->prepare("SELECT BK_ID, USR_ID, BK_DATE, BK_TOTAL_COST FROM " . TBL_BOOKING . " WHERE BK_ID = ?");
            $stmt->bind_param("i", $bkId);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "Error updating booking: " . $stmt->error;
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
    <title>Edit Booking - Staff</title>
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
        input, select {
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
        input:focus, select:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 5px rgba(255, 215, 0, 0.3);
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
        <a href="staff_bookings.php" class="back-btn">← Back to Bookings</a>

        <div class="form-header">
            <h1><i class="fas fa-edit"></i> Edit Booking</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="usr_id">Customer <span style="color: #ff6b6b;">*</span></label>
                <select id="usr_id" name="usr_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['USR_ID']; ?>" <?php echo ($booking['USR_ID'] == $u['USR_ID']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['USR_FNAME']) . ' (' . htmlspecialchars($u['USR_EMAIL']) . ')'; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="bk_date">Booking Date <span style="color: #ff6b6b;">*</span></label>
                <input type="datetime-local" id="bk_date" name="bk_date" required value="<?php echo str_replace(' ', 'T', substr($booking['BK_DATE'], 0, 16)); ?>">
            </div>

            <div class="form-group">
                <label for="bk_total_cost">Booking Total <span style="color: #ff6b6b;">*</span></label>
                <input type="number" id="bk_total_cost" name="bk_total_cost" step="0.01" min="0" required value="<?php echo htmlspecialchars($booking['BK_TOTAL_COST']); ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-submit">Update Booking</button>
                <a href="staff_bookings.php" class="btn btn-cancel">Cancel</a>
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