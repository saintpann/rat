<?php
require_once __DIR__ . '/config.php';

// Staff authorization
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'staff') {
    header("Location: login.php");
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $role = trim($_POST['role'] ?? 'Customer');

    if (empty($email) || empty($password) || empty($fname) || empty($lname)) {
        $message = "All fields are required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
        $messageType = "error";
    } else {
        // Check if email exists
        $checkStmt = $mysqli->prepare("SELECT USR_ID FROM " . TBL_USR . " WHERE USR_EMAIL = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "Email already registered.";
            $messageType = "error";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO " . TBL_USR . " (USR_EMAIL, USR_PASSWORD, USR_FNAME, USR_LNAME, USR_ROLE) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $email, $hashedPassword, $fname, $lname, $role);

            if ($stmt->execute()) {
                $message = "User created successfully!";
                $messageType = "success";
                header("Refresh: 2; url=staff_users.php");
            } else {
                $message = "Error creating user: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}

$user = $_SESSION['user'];
$userName = $user['name'] ?? 'Staff';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add User - Staff</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/home.css">
    <style>
        .staff-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .form-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #FFD700;
        }
        .form-header h1 {
            color: #EDE6D6;
            margin: 0 0 10px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #EDE6D6;
            font-weight: bold;
            margin-bottom: 8px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #383838;
            border-radius: 5px;
            background: #282828;
            color: #EDE6D6;
            font-family: Poppins, sans-serif;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 5px rgba(255, 215, 0, 0.3);
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            transition: opacity 0.3s;
        }
        .btn-submit {
            background: #FFD700;
            color: #1a1a1a;
        }
        .btn-submit:hover {
            opacity: 0.8;
        }
        .btn-cancel {
            background: #383838;
            color: #A9A9A9;
            text-decoration: none;
            text-align: center;
        }
        .btn-cancel:hover {
            opacity: 0.8;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #2d5016;
            color: #90EE90;
        }
        .message.error {
            background: #5c1a1a;
            color: #ff6b6b;
        }
        .back-btn {
            color: #FFD700;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body style="background: #1a1a1a; color: #EDE6D6;">

    <header class="header">
        <div class="header-left">
            <a href="employeeDashboard.php">
                <img src="images/themouse.png" alt="The Mouse Logo" class="logo-img">
            </a>
            <div class="headstamp">Staff Panel</div>
        </div>
        <div class="header-right">
            <span class="account-info"><?php echo htmlspecialchars($userName); ?></span>
            <a href="logout.php" class="account-icon" title="Sign Out" style="margin-left: 15px; color: #ff6b6b;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>

    <div class="staff-container">
        <a href="staff_users.php" class="back-btn">← Back to Users</a>

        <div class="form-header">
            <h1><i class="fas fa-user-plus"></i> Add New User</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email <span style="color: #ff6b6b;">*</span></label>
                <input type="email" id="email" name="email" required placeholder="user@example.com">
            </div>

            <div class="form-group">
                <label for="password">Password <span style="color: #ff6b6b;">*</span></label>
                <input type="password" id="password" name="password" required placeholder="Enter password">
            </div>

            <div class="form-group">
                <label for="fname">First Name <span style="color: #ff6b6b;">*</span></label>
                <input type="text" id="fname" name="fname" required placeholder="John">
            </div>

            <div class="form-group">
                <label for="lname">Last Name <span style="color: #ff6b6b;">*</span></label>
                <input type="text" id="lname" name="lname" required placeholder="Doe">
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="Customer">Customer</option>
                    <option value="Staff">Staff</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-submit">Create User</button>
                <a href="staff_users.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
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