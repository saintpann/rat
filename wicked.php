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
        <title>The Mouse - Wicked</title>
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
                    <img src="images/movie3.jpg" alt="Wicked">
                </div>
                <div class="movie-info">
                    <h1 class="movie-title">Wicked</h1>
                    <div class="movie-meta">
                        <span class="rating"><i class="fas fa-star"></i> 8.2/10</span>
                        <span class="duration">2h 35m</span>
                        <span class="year">2024</span>
                        <span class="genre-tag">Fantasy</span>
                        <span class="genre-tag">Musical</span>
                        <span class="genre-tag">Drama</span>
                    </div>
                    <p class="movie-synopsis">
                        The extraordinary untold story of the Wicked Witch of the West and Glinda the Good Witch, 
                        long before Dorothy arrives in Oz. Based on the acclaimed Broadway musical, this epic 
                        fantasy follows two young women who meet in the land of Oz and become rivals, 
                        until the world decides to call one "good" and the other "wicked."
                    </p>
                </div>
            </div>

            <div class="movie-details-section">
                <div class="details-grid">
                    <div class="detail-card">
                        <h3>Director</h3>
                        <p>Jon M. Chu</p>
                    </div>
                    <div class="detail-card">
                        <h3>Cast</h3>
                        <p>Cynthia Erivo, Ariana Grande, Jonathan Bailey, Ethan Slater</p>
                    </div>
                    <div class="detail-card">
                        <h3>Release Date</h3>
                        <p>November 27, 2024</p>
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
                        <a href="seats.php?movie=wicked&date=today&time=10:30AM" class="time-slot">10:30 AM</a>
                        <a href="seats.php?movie=wicked&date=today&time=2:00PM" class="time-slot">2:00 PM</a>
                        <a href="seats.php?movie=wicked&date=today&time=5:30PM" class="time-slot">5:30 PM</a>
                        <a href="seats.php?movie=wicked&date=today&time=9:00PM" class="time-slot">9:00 PM</a>
                        <?php } else { ?>
                        <!-- Guests see non-clickable times -->
                        <span class="time-slot">10:30 AM</span>
                        <span class="time-slot">2:00 PM</span>
                        <span class="time-slot">5:30 PM</span>
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
                        <a href="seats.php?movie=wicked&date=tomorrow&time=10:30AM" class="time-slot">10:30 AM</a>
                        <a href="seats.php?movie=wicked&date=tomorrow&time=2:00PM" class="time-slot">2:00 PM</a>
                        <a href="seats.php?movie=wicked&date=tomorrow&time=5:30PM" class="time-slot">5:30 PM</a>
                        <a href="seats.php?movie=wicked&date=tomorrow&time=9:00PM" class="time-slot">9:00 PM</a>
                        <?php } else { ?>
                        <!-- Guests see non-clickable times -->
                        <span class="time-slot">10:30 AM</span>
                        <span class="time-slot">2:00 PM</span>
                        <span class="time-slot">5:30 PM</span>
                        <span class="time-slot">10:15 PM</span>
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