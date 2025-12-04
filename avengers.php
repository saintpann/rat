<?php
require_once __DIR__ . '/config.php';

// Session check
$loggedInUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$userName = $loggedInUser ? ($loggedInUser['name'] ?? 'Guest') : 'Guest';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>The Mouse - Avengers: Endgame</title>
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
                <div class="headstamp">The Mouse Cinema</div>
            </div>
            <div class="header-right">
                <?php if ($loggedInUser): ?>
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

        <div class="movie-detail-container">
            <div class="movie-hero">
                <div class="movie-poster">
                    <img src="images/movie1.jpg" alt="Avengers: Endgame">
                </div>
                <div class="movie-info">
                    <h1 class="movie-title">Avengers: Endgame</h1>
                    <div class="movie-meta">
                        <span class="rating"><i class="fas fa-star"></i> 8.5/10</span>
                        <span class="duration">3h 1m</span>
                        <span class="year">2019</span>
                        <span class="genre-tag">Action</span>
                        <span class="genre-tag">Adventure</span>
                        <span class="genre-tag">Sci-Fi</span>
                    </div>
                    <p class="movie-synopsis">
                        After the devastating events of Avengers: Infinity War, the universe is in ruins. 
                        With the help of remaining allies, the Avengers assemble once more in order to 
                        reverse Thanos' actions and restore balance to the universe.
                    </p>
                </div>
            </div>

            <div class="movie-details-section">
                <div class="details-grid">
                    <div class="detail-card">
                        <h3>Director</h3>
                        <p>Anthony Russo, Joe Russo</p>
                    </div>
                    <div class="detail-card">
                        <h3>Cast</h3>
                        <p>Robert Downey Jr., Chris Evans, Mark Ruffalo, Chris Hemsworth, Scarlett Johansson</p>
                    </div>
                    <div class="detail-card">
                        <h3>Release Date</h3>
                        <p>April 26, 2019</p>
                    </div>
                    <div class="detail-card">
                        <h3>Language</h3>
                        <p>English</p>
                    </div>
                </div>
            </div>

            <!-- Showtimes Section -->
            <div class="showtimes-section">
                <h2>Showtimes</h2>

                <div class="time-group">
                    <h3>Today</h3>
                    <div class="time-slots">
                        <?php if ($loggedInUser): ?>
                        <a href="seats.php?movie=4&date=today&time=10:00AM" class="time-slot">10:00 AM</a>
                        <a href="seats.php?movie=4&date=today&time=1:30PM" class="time-slot">1:30 PM</a>
                        <a href="seats.php?movie=4&date=today&time=4:45PM" class="time-slot">4:45 PM</a>
                        <a href="seats.php?movie=4&date=today&time=8:00PM" class="time-slot">8:00 PM</a>
                        <?php else: ?>
                        <span class="time-slot">10:00 AM</span>
                        <span class="time-slot">1:30 PM</span>
                        <span class="time-slot">4:45 PM</span>
                        <span class="time-slot">8:00 PM</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="time-group">
                    <h3>Tomorrow</h3>
                    <div class="time-slots">
                        <?php if ($loggedInUser): ?>
                        <a href="seats.php?movie=4&date=tomorrow&time=10:00AM" class="time-slot">10:00 AM</a>
                        <a href="seats.php?movie=4&date=tomorrow&time=1:30PM" class="time-slot">1:30 PM</a>
                        <a href="seats.php?movie=4&date=tomorrow&time=4:45PM" class="time-slot">4:45 PM</a>
                        <a href="seats.php?movie=4&date=tomorrow&time=8:00PM" class="time-slot">8:00 PM</a>
                        <?php else: ?>
                        <span class="time-slot">10:00 AM</span>
                        <span class="time-slot">1:30 PM</span>
                        <span class="time-slot">4:45 PM</span>
                        <span class="time-slot">8:00 PM</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($loggedInUser): ?>
        <a href="myTickets.php" class="shopping-cart-btn">
            <i class="fas fa-shopping-cart"></i>
        </a>
        <?php endif; ?>

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