<?php
require_once __DIR__ . '/config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    
    if (empty($email) || empty($password) || empty($fname) || empty($lname)) {
        $message = 'All fields are required.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
        $messageType = 'error';
    } else {
        // Check if email exists
        $checkStmt = $mysqli->prepare("SELECT USR_ID FROM " . TBL_USR . " WHERE USR_EMAIL = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $message = 'Email already registered.';
            $messageType = 'error';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 'Customer';
            
            $stmt = $mysqli->prepare("INSERT INTO " . TBL_USR . " (USR_EMAIL, USR_PASSWORD, USR_FNAME, USR_LNAME, USR_ROLE) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $email, $hashedPassword, $fname, $lname, $role);
            
            if ($stmt->execute()) {
                $message = 'Account created! Redirecting to login...';
                $messageType = 'success';
                header("Refresh: 2; url=login.php");
            } else {
                $message = 'Error creating account: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>The Mouse - Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/signup.css">
    <style>
        .login-container { max-width: 500px; margin: 40px auto; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <img src="images/themouse.png" alt="The Mouse Logo" class="logo-img-login">
        </div>

        <?php if (!empty($message)): ?>
        <div style="color: <?php echo $messageType === 'error' ? '#ff4d4d' : '#4dff4d'; ?>; background: rgba(<?php echo $messageType === 'error' ? '255,0,0' : '0,255,0'; ?>,0.1); padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <h2>Create Account</h2>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="fname">First Name</label>
                <input type="text" id="fname" name="fname" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="lname">Last Name</label>
                <input type="text" id="lname" name="lname" class="form-input" required>
            </div>

            <button type="submit" class="form-button">Sign Up</button>
        </form>

        <div class="signup-link">
            Already have an account? <a href="login.php">Log In here.</a>
        </div>
    </div>

</body>
</html>