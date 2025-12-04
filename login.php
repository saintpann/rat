<?php
require_once __DIR__ . '/config.php';

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $errorMessage = 'Email and password are required.';
    } else {
        // Query user from database
        $stmt = $mysqli->prepare("SELECT USR_ID, USR_EMAIL, USR_PASSWORD, USR_FNAME, USR_ROLE FROM " . TBL_USR . " WHERE USR_EMAIL = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // For compatibility, check both password_hash and plain password
            if (password_verify($password, $user['USR_PASSWORD']) || $user['USR_PASSWORD'] === $password) {
                // Login successful
                $_SESSION['user'] = [
                    'id' => $user['USR_ID'],
                    'email' => $user['USR_EMAIL'],
                    'name' => $user['USR_FNAME'],
                    'role' => $user['USR_ROLE']
                ];

                // --- NEW REDIRECT LOGIC ---
                if (isset($user['USR_ROLE']) && strtolower($user['USR_ROLE']) === 'staff') {
                    header("Location: employeeDashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
                // --------------------------

            } else {
                $errorMessage = 'Invalid email or password.';
            }
        } else {
            $errorMessage = 'Invalid email or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>The Mouse - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/login.css">
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <img src="images/themouse.png" alt="The Mouse Logo" class="logo-img-login">
        </div>
        <?php if (!empty($errorMessage)): ?>
        <div style="color: #ff4d4d; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px;">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>
        <form method="POST" class="login-form">
            <h2>Sign In</h2>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>

            <button type="submit" class="form-button">Sign In</button>
        </form>

        <div class="signup-link">
            New to The Mouse? <a href="signup.php">Sign up now.</a>
        </div>
    </div>

</body>
</html>