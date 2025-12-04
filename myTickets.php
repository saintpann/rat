<?php
require_once __DIR__ . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Fetch user's tickets from database
$userId = $_SESSION['user']['id'];
$query = "SELECT t.TKT_ID, t.TKT_PRICE, m.MOV_TITLE, s.SHW_START_TIME, st.ST_LABEL, b.BK_DATE
          FROM " . TBL_TICKET . " t
          JOIN " . TBL_BOOKING . " b ON t.BK_ID = b.BK_ID
          JOIN " . TBL_SHOWING . " s ON t.SHW_ID = s.SHW_ID
          JOIN " . TBL_MOVIE . " m ON s.MOV_ID = m.MOV_ID
          JOIN " . TBL_SEAT . " st ON t.ST_ID = st.ST_ID
          WHERE b.USR_ID = ?
          ORDER BY s.SHW_START_TIME DESC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$tickets = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}
$stmt->close();
?>
<html>
<head>
    <title>My Tickets - The Mouse</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Reuse your home or seats CSS, or create a simple new one -->
    <link rel="stylesheet" type="text/css" href="css/home.css"> 
    
    <style>
        .ticket-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 20px;
        }
        .ticket-card {
            background-color: #1F1F1F;
            border-left: 5px solid #FFD700;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ticket-info h3 { margin: 0 0 5px 0; color: #EDE6D6; }
        .ticket-info p { margin: 0; color: #A0A0A0; font-size: 14px; }
        .ticket-seats { text-align: right; }
        .seat-badge {
            display: inline-block;
            background-color: #FFD700;
            color: #234E70;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            margin-left: 5px;
        }
        .no-tickets { text-align: center; color: #666; margin-top: 50px; }
    </style>
</head>
<body>

    <!-- Header (Same as Home) -->
    <header class="header">
        <div class="header-left">
            <a href="index.php">
                <img src="images/themouse.png" alt="Logo" class="logo-img">
            </a>
        </div>
        <div class="header-right">
            <a href="index.php" class="account-icon"><i class="fas fa-times"></i></a>
        </div>
    </header>

    <div class="ticket-container">
        <h2 style="color: #FFD700; border-bottom: 1px solid #333; padding-bottom: 10px;">My Tickets</h2>

        <?php if (empty($tickets)): ?>
            <div class="no-tickets">
                <h3>No tickets found.</h3>
                <p>Go book a movie to see it here!</p>
            </div>
        <?php else: ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-card">
                    <div class="ticket-info">
                        <h3><?php echo htmlspecialchars(strtoupper($ticket['MOV_TITLE'])); ?></h3>
                        <p>
                            <i class="far fa-calendar-alt"></i> 
                            <?php echo htmlspecialchars(date('M d, Y', strtotime($ticket['SHW_START_TIME']))); ?> | 
                            <i class="far fa-clock"></i> 
                            <?php echo htmlspecialchars(date('h:i A', strtotime($ticket['SHW_START_TIME']))); ?>
                        </p>
                        <p><i class="fas fa-chair"></i> Seat: <?php echo htmlspecialchars($ticket['ST_LABEL']); ?></p>
                        <p style="color: #FFD700; font-weight: bold;">₱<?php echo htmlspecialchars(number_format($ticket['TKT_PRICE'], 2)); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>