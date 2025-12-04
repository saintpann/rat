<?php
// Use the shared configuration (Fixes the database connection)
require_once __DIR__ . '/config.php';

// Determine logged-in user and display name
$loggedInUser = null;
$userName = "Guest";
if (isset($_SESSION['user'])) {
    $loggedInUser = $_SESSION['user'];
    if (is_array($loggedInUser) && isset($loggedInUser['name'])) {
        $userName = $loggedInUser['name'];
    } elseif (is_object($loggedInUser) && isset($loggedInUser->name)) {
        $userName = $loggedInUser->name;
    }
}

// Application header fallback
$appHeader = isset($GLOBALS['header']) ? $GLOBALS['header'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>The Mouse - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/home.css">
</head>
<body>

    <header class="header">
        <div class="header-left">
            <a href="index.php">
                <img src="images/themouse.png" alt="The Mouse Logo" class="logo-img">
            </a>
            <div class="headstamp"><?php echo htmlspecialchars($appHeader); ?> </div>
        </div>
        <div class="header-right">
            <?php if ($loggedInUser !== null): ?>
                
                <?php if (isset($loggedInUser['role']) && strtolower($loggedInUser['role']) === 'staff'): ?>
                    <a href="employeeDashboard.php" class="account-icon" title="Staff Dashboard" style="color: #FFD700; margin-right: 15px;">
                        <i class="fas fa-tools"></i>
                    </a>
                <?php endif; ?>
                <span class="account-info">Welcome, <?php echo htmlspecialchars($userName); ?></span>
                <a href="profile.php" class="account-icon" title="Edit Profile">
                    <i class="fas fa-user-circle"></i>
                </a>

                <a href="logout.php" class="account-icon" title="Sign Out" style="margin-left: 15px; color: #ff6b6b;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="hero-section">
        <img src="images/themouse.png" alt="The Mouse Logo" class="hero-logo-large">
        <h1 class="hero-title">Your Ultimate Movie Experience</h1>
    </section>

    <section class="movie-listings">
        <div class="listing-header">
            <div class="listing-tabs">
                <button class="active">Now Showing</button>
            </div>
        </div>

        <div class="movie-grid">
            <a class='movieclick' href="avengers.php">
            <div class="movie-card">
                <img src="images/movie1.jpg" alt="Avengers: Endgame">
                <div class="movie-info">
                    <div class="title">Avengers: Endgame</div>
                    <div class="details">
                        <span class="genre">Action</span>
                        <span class="rating"><i class="fas fa-star"></i> 8.5</span>
                    </div>
                </div>
            </div>
            </a>

            <a class='movieclick' href="beautifulboy.php">
            <div class="movie-card">
                <img src="images/movie2.jpg" alt="Beautiful Boy">
                <div class="movie-info">
                    <div class="title">Beautiful Boy</div>
                    <div class="details">
                        <span class="genre">Drama</span>
                        <span class="rating"><i class="fas fa-star"></i> 7.8</span>
                    </div>
                </div>
            </div>
            </a>

            <a class='movieclick' href="wicked.php">
            <div class="movie-card">
                <img src="images/movie3.jpg" alt="Wicked">
                <div class="movie-info">
                    <div class="title">Wicked</div>
                    <div class="details">
                        <span class="genre">Fantasy</span>
                        <span class="rating"><i class="fas fa-star"></i> 8.2</span>
                    </div>
                </div>
            </div>
            </a>

            <a class='movieclick' href="lalaland.php">
            <div class="movie-card">
                <img src="images/movie4.jpg" alt="La La Land">
                <div class="movie-info">
                    <div class="title">La La Land</div>
                    <div class="details">
                        <span class="genre">Drama</span>
                        <span class="rating"><i class="fas fa-star"></i> 9.1</span>
                    </div>
                </div>
            </div>
            </a>
        </div>
    </section>

    <footer class="footer">
        <?php
        if (file_exists(__DIR__ . '/footer.php')) {
            include __DIR__ . '/footer.php';
        } elseif (file_exists(__DIR__ . '/footer.html')) {
            include __DIR__ . '/footer.html';
        } else {
            echo '<div class="footer-inner">© ' . date('Y') . ' The Mouse Cinema</div>';
        }
        ?>
    </footer>

    <?php if ($loggedInUser !== null): ?>
    <a href="myTickets.php" class="shopping-cart-btn">
        <i class="fas fa-shopping-cart"></i>
    </a>
    <?php endif; ?>

</body>
</html>