<?php
require_once __DIR__ . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Get parameters from URL
$movieParam = htmlspecialchars($_GET['movie'] ?? 'Unknown Movie');
$dateParam = htmlspecialchars($_GET['date'] ?? 'today');
$time = htmlspecialchars($_GET['time'] ?? 'Unknown Time');

// Determine actual movie title and id from the parameter (accepts numeric IDs, slugs, or titles)
$movieId = null;
$movieTitle = $movieParam;

if (is_numeric($movieParam)) {
    // If numeric, treat as MOV_ID
    $movieId = intval($movieParam);
    $stmt = $mysqli->prepare("SELECT MOV_TITLE FROM " . TBL_MOVIE . " WHERE MOV_ID = ? LIMIT 1");
    $stmt->bind_param("i", $movieId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $movieTitle = $row['MOV_TITLE'];
    }
    $stmt->close();
} else {
    // Try direct case-insensitive title match first
    $stmt = $mysqli->prepare("SELECT MOV_ID, MOV_TITLE FROM " . TBL_MOVIE . " WHERE LOWER(MOV_TITLE) = LOWER(?) LIMIT 1");
    $stmt->bind_param("s", $movieParam);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $movieId = $row['MOV_ID'];
        $movieTitle = $row['MOV_TITLE'];
    }
    $stmt->close();
    
    // If no exact match, try normalized slug matching
    if (!$movieId) {
        // Remove spaces, colons, and other special chars, then compare
        $norm = preg_replace('/[^a-z0-9]/i', '', strtolower($movieParam));
        $stmt = $mysqli->prepare("SELECT MOV_ID, MOV_TITLE FROM " . TBL_MOVIE);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res && $res->num_rows > 0) {
            while ($r = $res->fetch_assoc()) {
                $cand = preg_replace('/[^a-z0-9]/i', '', strtolower($r['MOV_TITLE']));
                if ($cand === $norm) {
                    $movieId = $r['MOV_ID'];
                    $movieTitle = $r['MOV_TITLE'];
                    break;
                }
            }
        }
        $stmt->close();
    }
    
    // If still no match, try LIKE partial match
    if (!$movieId) {
        $likeParam = '%' . $movieParam . '%';
        $stmt = $mysqli->prepare("SELECT MOV_ID, MOV_TITLE FROM " . TBL_MOVIE . " WHERE LOWER(MOV_TITLE) LIKE LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $likeParam);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $movieId = $row['MOV_ID'];
            $movieTitle = $row['MOV_TITLE'];
        }
        $stmt->close();
    }
}

// Convert "today"/"tomorrow" to actual dates for display
if ($dateParam === 'today') {
    $actualDate = date('Y-m-d');
    $displayDate = 'Today, ' . date('M d, Y');
} elseif ($dateParam === 'tomorrow') {
    $actualDate = date('Y-m-d', strtotime('+1 day'));
    $displayDate = 'Tomorrow, ' . date('M d, Y', strtotime('+1 day'));
} else {
    $actualDate = $dateParam;
    $displayDate = $dateParam;
}

// Get occupied seats for this showing
$occupiedSeats = [];
$timeStr = date('H:i:s', strtotime($time));
if ($movieId) {
    $stmt = $mysqli->prepare("
        SELECT st.ST_LABEL
        FROM " . TBL_TICKET . " t
        JOIN " . TBL_SEAT . " st ON t.ST_ID = st.ST_ID
        JOIN " . TBL_SHOWING . " s ON t.SHW_ID = s.SHW_ID
        WHERE s.MOV_ID = ? AND DATE(s.SHW_START_TIME) = ? AND TIME(s.SHW_START_TIME) = ?
    ");
    $stmt->bind_param("iss", $movieId, $actualDate, $timeStr);
} else {
    $stmt = $mysqli->prepare("
        SELECT st.ST_LABEL
        FROM " . TBL_TICKET . " t
        JOIN " . TBL_SEAT . " st ON t.ST_ID = st.ST_ID
        JOIN " . TBL_SHOWING . " s ON t.SHW_ID = s.SHW_ID
        JOIN " . TBL_MOVIE . " m ON s.MOV_ID = m.MOV_ID
        WHERE m.MOV_TITLE = ? AND DATE(s.SHW_START_TIME) = ? AND TIME(s.SHW_START_TIME) = ?
    ");
    $stmt->bind_param("sss", $movieTitle, $actualDate, $timeStr);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $occupiedSeats[] = $row['ST_LABEL'];
}
$stmt->close();

$user = $_SESSION['user'];
$userName = $user['name'] ?? 'Customer';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Select Seats - <?php echo htmlspecialchars($movieTitle); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Link to your seats.css -->
    <link rel="stylesheet" type="text/css" href="css/seats.css">
</head>
<body style="background: #1a1a1a; color: #EDE6D6;">

    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <a href="index.php">
                <img src="images/themouse.png" alt="Logo" class="logo-img">
            </a>
        </div>
        <div class="header-right">
            <span class="account-info">Seat Selection</span>
            <a href="index.php" class="close-btn"><i class="fas fa-times"></i></a>
        </div>
    </header>

    <div class="main-container">
        
        <!-- Info Bar -->
        <div class="movie-info-bar">
            <h2><?php echo htmlspecialchars($movieTitle); ?></h2>
            <div class="meta-details">
                <span><i class="far fa-calendar-alt"></i> <?php echo $displayDate; ?></span>
                <span class="divider">|</span>
                <span><i class="far fa-clock"></i> <?php echo $time; ?></span>
            </div>
        </div>

        <!-- The Screen -->
        <div class="screen-container">
            <div class="screen"></div>
            <p>SCREEN</p>
        </div>

        <!-- Seat Map Form -->
        <form action="checkout.php" method="POST">
            <!-- Hidden inputs to pass data to checkout -->
            <input type="hidden" name="movie" value="<?php echo htmlspecialchars($movieTitle); ?>">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($dateParam); ?>">
            <input type="hidden" name="time" value="<?php echo htmlspecialchars($time); ?>">

            <div class="seat-grid">
                <?php 
                   // Generate 5 Rows (A-E)
                   $rows = ["A", "B", "C", "D", "E"];
                   $seatsPerRow = 8;

                   foreach ($rows as $row) {
                ?>
                    <div class="seat-row">
                        <span class="row-label"><?php echo $row; ?></span>
                        <?php 
                           for ($i = 1; $i <= $seatsPerRow; $i++) { 
                               $seatID = $row . $i;
                               
                               // Check if seat is occupied
                               $isTaken = in_array($seatID, $occupiedSeats);
                               $statusClass = $isTaken ? "occupied" : "available";
                               $disabledAttr = $isTaken ? "disabled" : "";
                        ?>
                            <!-- Add a gap (aisle) in the middle -->
                            <?php if ($i == 5) { ?><div class="aisle"></div><?php } ?>

                            <input type="checkbox" name="seat" value="<?php echo $seatID; ?>" id="<?php echo $seatID; ?>" <?php echo $disabledAttr; ?>>
                            <label for="<?php echo $seatID; ?>" class="seat <?php echo $statusClass; ?>"></label>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>

            <!-- Legend -->
            <div class="legend">
                <div class="legend-item"><span class="seat-sample available"></span> Available</div>
                <div class="legend-item"><span class="seat-sample selected"></span> Selected</div>
                <div class="legend-item"><span class="seat-sample occupied"></span> Occupied</div>
            </div>

            <!-- Bottom Bar -->
            <div class="checkout-bar">
                <div class="total-section">
                    <span class="label">Total Price</span>
                    <span class="amount" id="total">$0.00</span>
                </div>
                <button type="submit" class="checkout-btn">Proceed to Checkout</button>
            </div>
        </form>
    </div>

    <!-- JavaScript for Price Calculation -->
    <script>
        var checkboxes = document.querySelectorAll('input[type="checkbox"]:not(:disabled)');
        var totalDisplay = document.getElementById('total');
        var pricePerSeat = 12.50; // Set ticket price

        console.log('Initializing checkboxes. Total count: ' + checkboxes.length);
        
        for (var i = 0; i < checkboxes.length; i++) {
            (function(cb) {
                cb.addEventListener('change', function() {
                    console.log(this.value + ' was ' + (this.checked ? 'CHECKED' : 'UNCHECKED'));
                    var count = document.querySelectorAll('input[type="checkbox"]:checked').length;
                    console.log('Current total checked: ' + count);
                    totalDisplay.innerText = '$' + (count * pricePerSeat).toFixed(2);
                });
            })(checkboxes[i]);
        }
        
        // Debug: Log checked seats before form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            var checked = document.querySelectorAll('input[type="checkbox"]:checked');
            console.log('Seats checked at submission: ' + checked.length);
            for (var i = 0; i < checked.length; i++) {
                console.log('  - ' + checked[i].value);
            }
            
            // Verify all checkboxes are properly registered
            var allCheckboxes = document.querySelectorAll('input[type="checkbox"]:not(:disabled)');
            console.log('Total checkboxes available: ' + allCheckboxes.length);
            console.log('Checkboxes that are checked: ' + checked.length);
            
            // Show which ones are checked vs not checked
            for (var j = 0; j < allCheckboxes.length; j++) {
                if (allCheckboxes[j].checked) {
                    console.log('✓ ' + allCheckboxes[j].value + ' is CHECKED');
                }
            }
        });
    </script>

</body>
</html>