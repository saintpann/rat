<?php
require_once __DIR__ . '/config.php';

echo "<h2>Session Debugger</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user']['role'])) {
    $role = $_SESSION['user']['role'];
    echo "<p><strong>Current Role:</strong> " . htmlspecialchars($role) . "</p>";
    echo "<p><strong>Check Result:</strong> " . (strtolower($role) === 'staff' ? "PASS (You are Staff)" : "FAIL (Not Staff)") . "</p>";
} else {
    echo "<p>No user role found in session.</p>";
}
?>