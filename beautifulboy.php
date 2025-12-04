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
        <title>The Mouse - Beautiful Boy</title>
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
                    <img src="images/movie2.jpg" alt="Beautiful Boy">
                </div>
                <div class="movie-info">
                    <h1 class="movie-title">Beautiful Boy</h1>
                    <div class="movie-meta">
                        <span class="rating"><i class="fas fa-star"></i> 7.8/10</span>
                        <span class="duration">2h 0m</span>
                        <span class="year">2018</span>
                        <span class="genre-tag">Drama</span>
                        <span class="genre-tag">Biography</span>
                    </div>
                    <p class="movie-synopsis">
                        Based on the best-selling pair of memoirs from father and son David and Nic Sheff, 
                        Beautiful Boy chronicles the heartbreaking and inspiring experience of survival, 
                        relapse, and recovery in a family coping with addiction over many years.
                    </p>
                </div>
            </div>

            <div class="movie-details-section">
                <div class="details-grid">
                    <div class="detail-card">
                        <h3>Director</h3>
                        <p>Felix van Groeningen</p>
                    </div>
                    <div class="detail-card">
                        <h3>Cast</h3>
                        <p>Steve Carell, Timothée Chalamet, Maura Tierney, Amy Ryan</p>
                    </div>
                    <div class="detail-card">
                        <h3>Release Date</h3>
                        <p>October 12, 2018</p>
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
                        <a href="seats.php?movie=beautifulboy&date=today&time=11:00AM" class="time-slot">11:00 AM</a>
                        <a href="seats.php?movie=beautifulboy&date=today&time=2:15PM" class="time-slot">2:15 PM</a>
                        <a href="seats.php?movie=beautifulboy&date=today&time=5:30PM" class="time-slot">5:30 PM</a>
                        <a href="seats.php?movie=beautifulboy&date=today&time=8:45PM" class="time-slot">8:45 PM</a>
                        <?php } else { ?>
                        <!-- Guests see non-clickable times -->
                        <span class="time-slot">11:00 AM</span>
                        <span class="time-slot">2:15 PM</span>
                        <span class="time-slot">5:30 PM</span>
                        <span class="time-slot">8:45 PM</span>
                        <?php } ?>
                    </div>
                </div>

                <!-- Tomorrow's Showtimes -->
                <div class="time-group">
                    <h3>Tomorrow</h3>
                    <div class="time-slots">
                        <?php if ($loggedInUser != null) { ?>
                        <!-- Logged-in users see clickable buttons -->
                        <a href="seats.php?movie=beautifulboy&date=tomorrow&time=11:00AM" class="time-slot">11:00 AM</a>
                        <a href="seats.php?movie=beautifulboy&date=tomorrow&time=2:15PM" class="time-slot">2:15 PM</a>
                        <a href="seats.php?movie=beautifulboy&date=tomorrow&time=5:30PM" class="time-slot">5:30 PM</a>
                        <a href="seats.php?movie=beautifulboy&date=tomorrow&time=8:45PM" class="time-slot">8:45 PM</a>
                        <?php } else { ?>
                        <!-- Guests see non-clickable times -->
                        <span class="time-slot">11:00 AM</span>
                        <span class="time-slot">2:15 PM</span>
                        <span class="time-slot">5:30 PM</span>
                        <span class="time-slot">8:45 PM</span>
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