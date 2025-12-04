<?php
require_once __DIR__ . '/config.php';

// Cache control
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
$loggedInUser = null;
$userName = "Guest";
if (isset($_SESSION['user'])) {
    $loggedInUser = $_SESSION['user'];
    $userName = $loggedInUser['name'] ?? 'Guest';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>The Mouse - La La Land</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" type="text/css" href="css/movie.css">
    </head>
    <body>
        <header class="header">
            <div class="header-left">
                <a href="index.php">
                    <img src="images/themouse.png" alt="The Mouse Logo" class="logo-img">
                </a>
                <div class="headstamp">Cinema</div>
            </div>
            <div class="header-right">
                <?php if ($loggedInUser != null) { ?>
                <span class="account-info">Welcome, <?php echo htmlspecialchars($userName); ?></span>
                <a href="profile.php" class="account-icon" title="Edit Profile">
                    <i class="fas fa-user-circle"></i>
                </a>
                <a href="logout.php" class="account-icon" title="Sign Out" style="margin-left: 15px; color: #ff6b6b;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                <?php } else { ?>
                <a href="login.php" class="login-btn">Login</a>
                <?php } ?>
            </div>
        </header>

        <div class="movie-detail-container">
            <div class="movie-hero">
                <div class="movie-poster">
                    <img src="images/movie4.jpg" alt="La La Land">
                </div>
                <div class="movie-info">
                    <h1 class="movie-title">La La Land</h1>
                    <div class="movie-meta">
                        <span class="rating"><i class="fas fa-star"></i> 9.1/10</span>
                        <span class="duration">2h 8m</span>
                        <span class="year">2016</span>
                        <span class="genre-tag">Musical</span>
                        <span class="genre-tag">Romance</span>
                        <span class="genre-tag">Drama</span>
                    </div>
                    <p class="movie-synopsis">
                        While navigating their careers in Los Angeles, a pianist and an actress fall in love 
                        while attempting to reconcile their aspirations for the future. This modern musical 
                        about everyday life explores the joy and pain of pursuing your dreams, and the 
                        people we meet along the way that change us forever.
                    </p>
                </div>
            </div>

            <div class="movie-details-section">
                <div class="details-grid">
                    <div class="detail-card">
                        <h3>Director</h3>
                        <p>Damien Chazelle</p>
                    </div>
                    <div class="detail-card">
                        <h3>Cast</h3>
                        <p>Ryan Gosling, Emma Stone, John Legend, Rosemarie DeWitt</p>
                    </div>
                    <div class="detail-card">
                        <h3>Release Date</h3>
                        <p>December 9, 2016</p>
                    </div>
                    <div class="detail-card">
                        <h3>Language</h3>
                        <p>English</p>
                    </div>
                </div>
            </div>

            <div class="showtimes-section">
                <h2>Showtimes</h2>

                <!-- Today's Showtimes -->
                <div class="time-group">
                    <h3>Today</h3>
                    <div class="time-slots">
                        <?php if ($loggedInUser != null) { ?>
                        <!-- Logged-in users see clickable buttons -->
                        <a href="seats.php?movie=lalaland&date=today&time=12:00PM" class="time-slot">12:00 PM</a>
                        <a href="seats.php?movie=lalaland&date=today&time=3:00PM" class="time-slot">3:00 PM</a>
                        <a href="seats.php?movie=lalaland&date=today&time=6:00PM" class="time-slot">6:00 PM</a>
                        <a href="seats.php?movie=lalaland&date=today&time=9:00PM" class="time-slot">9:00 PM</a>
                        <?php } else { ?>
                        <!-- Guests see non-clickable times -->
                        <span class="time-slot">12:00 PM</span>
                        <span class="time-slot">3:00 PM</span>
                        <span class="time-slot">6:00 PM</span>
                        <span class="time-slot">9:00 PM</span>
                        <?php } ?>
                    </div>
                </div>

                <!-- Tomorrow's Showtimes -->
                <div class="time-group">
                    <h3>Tomorrow</h3>
                    <div class="time-slots">
                        <?php if ($loggedInUser != null) { ?>
                        <!-- Logged-in users see clickable buttons -->
                        <a href="seats.php?movie=lalaland&date=tomorrow&time=12:00PM" class="time-slot">12:00 PM</a>
                        <a href="seats.php?movie=lalaland&date=tomorrow&time=3:00PM" class="time-slot">3:00 PM</a>
                        <a href="seats.php?movie=lalaland&date=tomorrow&time=6:00PM" class="time-slot">6:00 PM</a>
                        <a href="seats.php?movie=lalaland&date=tomorrow&time=9:00PM" class="time-slot">9:00 PM</a>
                        <?php } else { ?>
                        <!-- Guests see non-clickable times -->
                        <span class="time-slot">12:00 PM</span>
                        <span class="time-slot">3:00 PM</span>
                        <span class="time-slot">6:00 PM</span>
                        <span class="time-slot">10:30 PM</span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <button class="shopping-cart-btn">
            <i class="fas fa-shopping-cart"></i>
        </button>
    </body>
</html>