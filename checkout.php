<?php
require_once __DIR__ . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'] ?? null;
$userName = $user['name'] ?? 'Customer';

// Initialize variables
$message = '';
$messageType = '';
$bookingDetails = [];

// Handle form submission from seats.php: store selection in session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie']) && !isset($_POST['confirm_booking'])) {
    $movie = trim($_POST['movie'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');

    $selectedSeats = $_POST['seat'] ?? [];
    if (!is_array($selectedSeats)) {
        $selectedSeats = [$selectedSeats];
    }
    
    // DEBUG: Log received seats
    @file_put_contents(__DIR__ . '/checkout_debug.log', date('c') . " - SEATS RECEIVED: " . json_encode($selectedSeats) . "\n", FILE_APPEND);

    if (empty($selectedSeats)) {
        $message = 'Please select at least one seat.';
        $messageType = 'error';
    } else {
        // store checkout data in session for review and confirmation
        $_SESSION['checkout'] = [
            'movie' => $movie,
            'date' => $date,
            'time' => $time,
            'seats' => $selectedSeats,
            'seatCount' => count($selectedSeats)
        ];

        // price per seat (adjust as needed)
        $pricePerSeat = 250.00;
        $_SESSION['checkout']['pricePerSeat'] = $pricePerSeat;
        $_SESSION['checkout']['totalPrice'] = count($selectedSeats) * $pricePerSeat;

        // reload page to show review
        header('Location: checkout.php');
        exit;
    }
}

// Retrieve checkout data from session
if (isset($_SESSION['checkout'])) {
    $bookingDetails = $_SESSION['checkout'];
}

// Handle booking confirmation
// Handle booking confirmation (no payment method required)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    if (!isset($_SESSION['checkout'])) {
        $message = 'No booking data found. Please select seats again.';
        $messageType = 'error';
    } else {
        $checkoutData = $_SESSION['checkout'];
        $seats = $checkoutData['seats'] ?? [];
        $totalCost = $checkoutData['totalPrice'] ?? 0;

        // Convert date token to actual date
        $dateStr = $checkoutData['date'];
        if ($dateStr === 'today') {
            $actualDate = date('Y-m-d');
        } elseif ($dateStr === 'tomorrow') {
            $actualDate = date('Y-m-d', strtotime('+1 day'));
        } else {
            $actualDate = $dateStr;
        }

        // Convert time to 24-hour
        $timeStr = $checkoutData['time'];
        $time24 = date('H:i:s', strtotime($timeStr));

        $movieParam = $checkoutData['movie'];
        
        // Resolve movie slug/title to full database title
        $movieTitle = $movieParam;
        
        // Strategy 1: Direct case-insensitive match
        $escapedParam = $mysqli->real_escape_string($movieParam);
        $query = "SELECT MOV_TITLE FROM " . TBL_MOVIE . " WHERE LOWER(MOV_TITLE) = LOWER('$escapedParam') LIMIT 1";
        $result = $mysqli->query($query);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $movieTitle = $row['MOV_TITLE'];
        }
        
        // Strategy 2: Normalized slug matching (if direct match failed)
        if ($movieTitle === $movieParam) {
            // Remove spaces, colons, and other special chars, then compare
            $norm = preg_replace('/[^a-z0-9]/i', '', strtolower($movieParam));
            $query = "SELECT MOV_TITLE FROM " . TBL_MOVIE;
            $result = $mysqli->query($query);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $cand = preg_replace('/[^a-z0-9]/i', '', strtolower($row['MOV_TITLE']));
                    if ($cand === $norm) {
                        $movieTitle = $row['MOV_TITLE'];
                        break;
                    }
                }
            }
        }
        
        // Strategy 3: LIKE partial match (if still no match)
        if ($movieTitle === $movieParam) {
            $escapedParam = $mysqli->real_escape_string($movieParam);
            $query = "SELECT MOV_TITLE FROM " . TBL_MOVIE . " WHERE LOWER(MOV_TITLE) LIKE LOWER('%$escapedParam%') LIMIT 1";
            $result = $mysqli->query($query);
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $movieTitle = $row['MOV_TITLE'];
            }
        }

        // DEBUG: Log the lookup parameters
        $debugLog = "Movie param: '$movieParam' -> resolved to: '$movieTitle' | Date: '$actualDate' | Time input: '$timeStr' | Time24: '$time24'\n";
        @file_put_contents(__DIR__ . '/checkout_debug.log', date('c') . " - $debugLog", FILE_APPEND);

        // Find showing by exact title + date + time
        $stmt = $mysqli->prepare(
            "SELECT s.SHW_ID FROM " . TBL_SHOWING . " s JOIN " . TBL_MOVIE . " m ON s.MOV_ID = m.MOV_ID WHERE m.MOV_TITLE = ? AND DATE(s.SHW_START_TIME) = ? AND TIME(s.SHW_START_TIME) = ? LIMIT 1"
        );
        $stmt->bind_param('sss', $movieTitle, $actualDate, $time24);
        $stmt->execute();
        $res = $stmt->get_result();
        $showingRow = $res->fetch_assoc();
        $stmt->close();

        if (!$showingRow) {
            $message = 'Showing not found. Please select seats again.';
            $messageType = 'error';
            $debugInfo = ['movieTitle' => $movieTitle, 'actualDate' => $actualDate, 'time24' => $time24];
            $message .= ' Debug: ' . htmlspecialchars(json_encode($debugInfo));
            @file_put_contents(__DIR__ . '/checkout_debug.log', date('c') . " - Showing lookup failed: " . json_encode($debugInfo) . PHP_EOL, FILE_APPEND);
        } else {
            $showingId = $showingRow['SHW_ID'];

            // Begin transaction — create booking then tickets
            $mysqli->begin_transaction();
            try {
                $bookingDate = date('Y-m-d H:i:s');
                $stmt = $mysqli->prepare("INSERT INTO " . TBL_BOOKING . " (USR_ID, BK_DATE, BK_TOTAL_COST) VALUES (?, ?, ?)");
                $stmt->bind_param('isd', $userId, $bookingDate, $totalCost);
                if (!$stmt->execute()) {
                    throw new Exception('Error creating booking: ' . $stmt->error);
                }
                $bookingId = $mysqli->insert_id;
                $stmt->close();

                // Insert tickets for each seat
                $ticketPricePerSeat = $checkoutData['pricePerSeat'] ?? ($totalCost / max(1, count($seats)));
                foreach ($seats as $seatLabel) {
                    // find seat id
                    $stmt = $mysqli->prepare("SELECT ST_ID FROM " . TBL_SEAT . " WHERE ST_LABEL = ? LIMIT 1");
                    $stmt->bind_param('s', $seatLabel);
                    $stmt->execute();
                    $sres = $stmt->get_result();
                    $row = $sres->fetch_assoc();
                    $stmt->close();

                    if (!$row) {
                        throw new Exception('Seat ' . $seatLabel . ' not found.');
                    }
                    $stId = $row['ST_ID'];

                    $stmt = $mysqli->prepare("INSERT INTO " . TBL_TICKET . " (BK_ID, SHW_ID, ST_ID, TKT_PRICE) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('iiid', $bookingId, $showingId, $stId, $ticketPricePerSeat);
                    if (!$stmt->execute()) {
                        throw new Exception('Error creating ticket for ' . $seatLabel . ': ' . $stmt->error);
                    }
                    $stmt->close();
                }

                $mysqli->commit();
                unset($_SESSION['checkout']);
                $message = 'Booking confirmed! Your booking ID is #' . $bookingId . '. Redirecting to your tickets...';
                $messageType = 'success';
                header('Refresh: 2; url=myTickets.php');
            } catch (Exception $e) {
                $mysqli->rollback();
                $message = $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout - The Mouse Cinema</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/home.css">
    <style>
        .checkout-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .checkout-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #FFD700;
        }

        .checkout-header h1 {
            color: #EDE6D6;
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .booking-summary {
            background: #282828;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #383838;
        }

        .booking-summary h2 {
            color: #FFD700;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #383838;
            padding-bottom: 15px;
        }

        .summary-item {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #383838;
        }

        .summary-item label {
            color: #A9A9A9;
            font-weight: 600;
            margin: 0;
        }

        .summary-item .value {
            color: #EDE6D6;
            font-weight: 700;
        }

        .seats-list {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            max-height: 150px;
            overflow-y: auto;
        }

        .seat-badge {
            display: inline-block;
            background: #FFD700;
            color: #1a1a1a;
            padding: 5px 10px;
            border-radius: 3px;
            margin: 5px 5px 5px 0;
            font-weight: bold;
            font-size: 12px;
        }

        .price-breakdown {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            color: #A9A9A9;
            margin-bottom: 10px;
        }

        .price-row.total {
            border-top: 2px solid #FFD700;
            padding-top: 10px;
            color: #FFD700;
            font-weight: bold;
            font-size: 1.1em;
        }

        .payment-section {
            background: #282828;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #383838;
        }

        .payment-section h2 {
            color: #FFD700;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            color: #EDE6D6;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #383838;
            border-radius: 5px;
            background: #1a1a1a;
            color: #EDE6D6;
            font-family: Poppins, sans-serif;
            font-size: 14px;
            box-sizing: border-box;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 5px rgba(255, 215, 0, 0.3);
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 25px;
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
            text-align: center;
            text-decoration: none;
        }

        .btn-confirm {
            background: #FFD700;
            color: #1a1a1a;
        }

        .btn-confirm:hover {
            opacity: 0.8;
        }

        .btn-back {
            background: #383838;
            color: #A9A9A9;
        }

        .btn-back:hover {
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
            border-left: 4px solid #90EE90;
        }

        .message.error {
            background: #5c1a1a;
            color: #ff6b6b;
            border-left: 4px solid #ff6b6b;
        }

        .message.info {
            background: #1a3a52;
            color: #87CEEB;
            border-left: 4px solid #87CEEB;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state-icon {
            font-size: 4em;
            color: #FFD700;
            margin-bottom: 20px;
        }

        .empty-state p {
            color: #A9A9A9;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }

            .checkout-header h1 {
                font-size: 1.8em;
            }

            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body style="background: #1a1a1a; color: #EDE6D6;">

    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <a href="index.php">
                <img src="images/themouse.png" alt="The Mouse Logo" class="logo-img">
            </a>
            <div class="headstamp">Checkout</div>
        </div>
        <div class="header-right">
            <span class="account-info"><?php echo htmlspecialchars($userName); ?></span>
            <a href="logout.php" class="account-icon" title="Sign Out" style="margin-left: 15px; color: #ff6b6b;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>

    <div class="checkout-container">

        <div class="checkout-header">
            <h1><i class="fas fa-shopping-cart"></i> Order Review</h1>
            <p style="color: #A9A9A9;">Review your booking and confirm to proceed</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($bookingDetails)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                <p>No booking in progress. Please select seats to continue.</p>
                <a href="index.php" class="btn btn-back">Return to Home</a>
            </div>
        <?php else: ?>
            <!-- Checkout Form -->
            <form method="POST">
                <div class="checkout-content">

                    <!-- Left: Booking Summary -->
                    <div class="booking-summary">
                        <h2><i class="fas fa-film"></i> Booking Details</h2>

                        <div class="summary-item">
                            <label>Movie:</label>
                            <div class="value"><?php echo htmlspecialchars($bookingDetails['movie']); ?></div>
                        </div>

                        <div class="summary-item">
                            <label>Date:</label>
                            <div class="value"><?php echo htmlspecialchars($bookingDetails['date']); ?></div>
                        </div>

                        <div class="summary-item">
                            <label>Time:</label>
                            <div class="value"><?php echo htmlspecialchars($bookingDetails['time']); ?></div>
                        </div>

                        <div class="summary-item">
                            <label>Seats Selected:</label>
                            <div class="value"><?php echo htmlspecialchars($bookingDetails['seatCount']); ?></div>
                        </div>

                        <div style="margin-top: 20px;">
                            <label style="color: #FFD700; font-weight: bold;">Your Seats:</label>
                            <div class="seats-list">
                                <?php foreach ($bookingDetails['seats'] as $seat): ?>
                                    <span class="seat-badge"><?php echo htmlspecialchars($seat); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="price-breakdown">
                            <div class="price-row">
                                <span>Seat Price (₱250/seat):</span>
                                <span>₱<?php echo number_format($bookingDetails['totalPrice'] ?? 0, 2); ?></span>
                            </div>
                            <div class="price-row">
                                <span>Processing Fee:</span>
                                <span>₱0.00</span>
                            </div>
                            <div class="price-row total">
                                <span>Total Amount:</span>
                                <span>₱<?php echo number_format($bookingDetails['totalPrice'] ?? 0, 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Payment Section -->
                    <div class="payment-section">
                        <h2><i class="fas fa-credit-card"></i> Payment Info</h2>

                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="payment-method">Payment Method <span style="color: #ff6b6b;">*</span></label>
                            <select id="payment-method" name="payment_method" required>
                                <option value="">-- Select Payment Method --</option>
                                <option value="credit-card">Credit Card</option>
                                <option value="debit-card">Debit Card</option>
                                <option value="gcash">GCash</option>
                                <option value="bank-transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div style="background: #1a1a1a; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 3px solid #FFD700;">
                            <p style="color: #A9A9A9; font-size: 12px; margin: 0;">
                                <i class="fas fa-lock"></i> Your payment information is secure and encrypted. No charges will be made until you confirm the booking.
                            </p>
                        </div>

                        <div class="btn-group">
                            <button type="submit" name="confirm_booking" value="1" class="btn btn-confirm">
                                <i class="fas fa-check"></i> Confirm & Pay
                            </button>
                            <a href="index.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Back</a>
                        </div>
                    </div>

                </div>
            </form>

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
