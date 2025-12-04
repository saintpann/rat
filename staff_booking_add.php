<?php
require_once __DIR__ . '/config.php';

// Staff authorization
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'staff') {
    header("Location: login.php");
    exit;
}

$message = '';
$messageType = '';

// Get available users for booking
$usersQuery = "SELECT USR_ID, USR_FNAME, USR_EMAIL FROM " . TBL_USR . " WHERE USR_ROLE = 'Customer' ORDER BY USR_FNAME";
$usersResult = $mysqli->query($usersQuery);
$users = [];
if ($usersResult) {
    while ($row = $usersResult->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get available showings for booking
$showingsQuery = "SELECT s.SHW_ID, m.MOV_TITLE, s.SHW_START_TIME 
                  FROM " . TBL_SHOWING . " s
                  JOIN " . TBL_MOVIE . " m ON s.MOV_ID = m.MOV_ID
                  ORDER BY s.SHW_START_TIME DESC";
$showingsResult = $mysqli->query($showingsQuery);
$showings = [];
if ($showingsResult) {
    while ($row = $showingsResult->fetch_assoc()) {
        $showings[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usrId = intval($_POST['usr_id'] ?? 0);
    $bkDate = trim($_POST['bk_date'] ?? '');
    $bkTotal = floatval($_POST['bk_total_cost'] ?? 0);
    $shwId = intval($_POST['shw_id'] ?? 0);
    $seatId = intval($_POST['seat_id'] ?? 0);
    $tktPrice = floatval($_POST['tkt_price'] ?? 0);

    if (empty($usrId) || empty($bkDate) || $bkTotal <= 0 || empty($shwId) || empty($seatId) || $tktPrice <= 0) {
        $message = "All fields are required and must be valid.";
        $messageType = "error";
    } else {
        // Start transaction
        $mysqli->begin_transaction();
        
        try {
            // Create booking
            $stmt = $mysqli->prepare("INSERT INTO " . TBL_BOOKING . " (USR_ID, BK_DATE, BK_TOTAL_COST) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $usrId, $bkDate, $bkTotal);
            if (!$stmt->execute()) {
                throw new Exception("Error creating booking: " . $stmt->error);
            }
            $bkId = $mysqli->insert_id;
            $stmt->close();

            // Create ticket (automatically associated with booking)
            $stmt = $mysqli->prepare("INSERT INTO " . TBL_TICKET . " (BK_ID, SHW_ID, ST_ID, TKT_PRICE) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $bkId, $shwId, $seatId, $tktPrice);
            if (!$stmt->execute()) {
                throw new Exception("Error creating ticket: " . $stmt->error);
            }
            $stmt->close();

            $mysqli->commit();
            $message = "Booking and ticket created successfully!";
            $messageType = "success";
            header("Refresh: 2; url=staff_bookings.php");
        } catch (Exception $e) {
            $mysqli->rollback();
            $message = $e->getMessage();
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
    <title>Add Booking - Staff</title>
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
            <h1><i class="fas fa-plus"></i> Add New Booking</h1>
            <p style="color: #A9A9A9; margin: 10px 0 0 0;">Adding a booking automatically creates a ticket</p>
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
                        <option value="<?php echo $u['USR_ID']; ?>"><?php echo htmlspecialchars($u['USR_FNAME']) . ' (' . htmlspecialchars($u['USR_EMAIL']) . ')'; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="bk_date">Booking Date <span style="color: #ff6b6b;">*</span></label>
                <input type="datetime-local" id="bk_date" name="bk_date" required>
            </div>

            <div class="form-group">
                <label for="bk_total_cost">Booking Total <span style="color: #ff6b6b;">*</span></label>
                <input type="number" id="bk_total_cost" name="bk_total_cost" step="0.01" min="0" required placeholder="0.00">
            </div>

            <div class="form-group">
                <label for="shw_id">Showing <span style="color: #ff6b6b;">*</span></label>
                <select id="shw_id" name="shw_id" required>
                    <option value="">-- Select Showing --</option>
                    <?php foreach ($showings as $s): ?>
                        <option value="<?php echo $s['SHW_ID']; ?>"><?php echo htmlspecialchars($s['MOV_TITLE']) . ' - ' . htmlspecialchars($s['SHW_START_TIME']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="seat_id">Seat ID <span style="color: #ff6b6b;">*</span></label>
                <input type="number" id="seat_id" name="seat_id" min="1" required placeholder="e.g., 1">
            </div>

            <div class="form-group">
                <label for="tkt_price">Ticket Price <span style="color: #ff6b6b;">*</span></label>
                <input type="number" id="tkt_price" name="tkt_price" step="0.01" min="0" required placeholder="0.00">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-submit">Create Booking & Ticket</button>
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