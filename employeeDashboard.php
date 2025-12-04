<?php
require_once __DIR__ . '/config.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$userRole = $user['role'] ?? '';

// Only allow Staff role on employee dashboard
if (strtolower($userRole) !== 'staff') {
    header("Location: index.php");
    exit;
}

$userName = $user['name'] ?? 'Staff Member';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard - The Mouse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/home.css">
    <style>
        .staff-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .staff-header {
            text-align: center;
            margin-bottom: 50px;
            border-bottom: 3px solid #FFD700;
            padding-bottom: 20px;
        }
        .staff-header h1 {
            color: #EDE6D6;
            margin-bottom: 10px;
        }
        .staff-header p {
            color: #A9A9A9;
        }
        .staff-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        .staff-card {
            background: #282828;
            border: 1px solid #383838;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }
        .staff-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(255,215,0,0.2);
            border-color: #FFD700;
        }
        .staff-card i {
            font-size: 48px;
            color: #FFD700;
            margin-bottom: 15px;
            display: block;
        }
        .staff-card h3 {
            color: #EDE6D6;
            margin-bottom: 10px;
        }
        .staff-card p {
            color: #A9A9A9;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .staff-btn {
            display: inline-block;
            background: #FFD700;
            color: #1a1a1a;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }
        .staff-btn:hover {
            background: #FFC700;
        }
        .logout-btn {
            background: #ff6b6b;
            color: white;
        }
        .logout-btn:hover {
            background: #ff5252;
        }
        .staff-footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #383838;
        }
    </style>
</head>
<body style="background: #1a1a1a; color: #EDE6D6;">

    <header class="header">
        <div class="header-left">
            <a href="index.php">
                <img src="images/themouse.png" alt="The Mouse Logo" class="logo-img">
            </a>
            <div class="headstamp">Staff Panel</div>
        </div>
        <div class="header-right">
            <span class="account-info">Welcome, <strong><?php echo htmlspecialchars($userName); ?></strong></span>
            <a href="logout.php" class="account-icon" title="Sign Out" style="margin-left: 15px; color: #ff6b6b;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>

    <div class="staff-container">
        <div class="staff-header">
            <h1><i class="fas fa-shield-alt"></i> Staff Administration Panel</h1>
            <p>Manage users, bookings, and tickets</p>
        </div>

        <div class="staff-grid">
            <!-- Users Management -->
            <div class="staff-card">
                <i class="fas fa-users"></i>
                <h3>Manage Users</h3>
                <p>View, add, edit, and delete user accounts (USR)</p>
                <a href="staff_users.php" class="staff-btn">Open</a>
            </div>

            <!-- Bookings Management -->
            <div class="staff-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Manage Bookings</h3>
                <p>View, add, edit, and delete bookings (BK)</p>
                <a href="staff_bookings.php" class="staff-btn">Open</a>
            </div>

            <!-- Tickets Management -->
            <div class="staff-card">
                <i class="fas fa-ticket-alt"></i>
                <h3>Manage Tickets</h3>
                <p>View, edit, and delete tickets (TKT)</p>
                <a href="staff_tickets.php" class="staff-btn">Open</a>
            </div>
        </div>

        <div class="staff-footer">
            <a href="index.php" style="color: #FFD700; text-decoration: none;">← Back to Home</a>
            <a href="logout.php" class="staff-btn logout-btn" style="margin-left: 20px;">Logout</a>
        </div>
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