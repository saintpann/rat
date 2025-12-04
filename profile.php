<?php
require_once __DIR__ . '/config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];
$message = '';
$messageType = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($fname) || empty($lname) || empty($email)) {
        $message = 'First name, last name, and email are required.';
        $messageType = 'error';
    } else {
        $stmt = $mysqli->prepare("UPDATE " . TBL_USR . " SET USR_FNAME = ?, USR_LNAME = ?, USR_EMAIL = ? WHERE USR_ID = ?");
        $stmt->bind_param("sssi", $fname, $lname, $email, $userId);
        
        if ($stmt->execute()) {
            // Update session
            $_SESSION['user']['name'] = $fname . ' ' . $lname;
            $_SESSION['user']['email'] = $email;
            $user = $_SESSION['user'];
            $message = 'Profile updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating profile: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    }
}
?>
<html>
<head>
    <title>Edit Profile - The Mouse</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link rel="stylesheet" type="text/css" href="css/profile.css">
</head>
<body style="background: #1a1a1a; color: #EDE6D6;">

    <div class="edit-container">
        <div class="edit-header">
            <a href="index.php">
                <img src="images/themouse.png" alt="Logo" class="logo-img">
            </a>
            <h2>Edit Profile</h2>
        </div>

        <?php if (!empty($message)): ?>
            <div style="color: <?php echo $messageType === 'error' ? '#ff4d4d' : '#4dff4d'; ?>; background: rgba(<?php echo $messageType === 'error' ? '255,0,0' : '0,255,0'; ?>,0.1); padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="profile.php" method="POST" class="edit-form">
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="form-input" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="fname" value="<?php echo htmlspecialchars($user['fname'] ?? ''); ?>" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lname" value="<?php echo htmlspecialchars($user['lname'] ?? ''); ?>" class="form-input" required>
                </div>
            </div>

            <div class="button-group">
                 <a href="index.php" class="cancel-btn">Cancel</a>
    
                 <button type="button" class="cancel-btn" onclick="window.location.href='logout.php'" style="color: #ff6b6b; border-color: #ff6b6b;">
                   Sign Out
                 </button>
    
                 <button type="submit" class="save-btn">Save Changes</button>
            </div>
        </form>
    </div>

</body>
</html>