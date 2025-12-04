<?php
require_once __DIR__ . '/config.php';
// Simple DB status/debug page — remove after use
header('Content-Type: text/html; charset=utf-8');
echo "<h2>DB Status — The Mouse</h2>";
// Connection test
if ($mysqli->connect_errno) {
    echo '<p style="color: red;">DB connect error: ' . htmlspecialchars($mysqli->connect_error) . '</p>';
    exit;
} else {
    echo '<p style="color: green;">Connected to MySQL server: ' . htmlspecialchars($mysqli->host_info) . '</p>';
}

// Current database
$res = $mysqli->query("SELECT DATABASE() AS db");
$row = $res->fetch_assoc();
echo '<p>Current database: <strong>' . htmlspecialchars($row['db']) . '</strong></p>';

// Quick counts
$tables = ['room','movie','seat','showing','booking','ticket'];
echo '<h3>Table counts</h3><ul>';
foreach ($tables as $t) {
    $r = $mysqli->query("SELECT COUNT(*) AS c FROM `" . $t . "`");
    if ($r) {
        $c = $r->fetch_assoc()['c'];
        echo '<li>' . htmlspecialchars($t) . ': <strong>' . intval($c) . '</strong></li>';
    } else {
        echo '<li>' . htmlspecialchars($t) . ': <span style="color:red">ERROR (' . htmlspecialchars($mysqli->error) . ')</span></li>';
    }
}
echo '</ul>';

// Show sample showings for a date (use ?date=YYYY-MM-DD or defaults to today)
$date = $_GET['date'] ?? date('Y-m-d');
$movie = $_GET['movie'] ?? '';

echo '<h3>Showings for ' . htmlspecialchars($date) . '</h3>';
if (!empty($movie)) {
    $stmt = $mysqli->prepare("SELECT s.SHW_ID, m.MOV_TITLE, s.SHW_START_TIME FROM " . TBL_SHOWING . " s JOIN " . TBL_MOVIE . " m ON s.MOV_ID = m.MOV_ID WHERE m.MOV_TITLE = ? AND DATE(s.SHW_START_TIME) = ? ORDER BY s.SHW_START_TIME");
    $stmt->bind_param('ss', $movie, $date);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $stmt = $mysqli->prepare("SELECT s.SHW_ID, m.MOV_TITLE, s.SHW_START_TIME FROM " . TBL_SHOWING . " s JOIN " . TBL_MOVIE . " m ON s.MOV_ID = m.MOV_ID WHERE DATE(s.SHW_START_TIME) = ? ORDER BY m.MOV_TITLE, s.SHW_START_TIME");
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $res = $stmt->get_result();
}

if ($res && $res->num_rows > 0) {
    echo '<table border="1" cellpadding="6" cellspacing="0">';
    echo '<tr><th>SHW_ID</th><th>MOV_TITLE</th><th>SHW_START_TIME</th></tr>';
    while ($r = $res->fetch_assoc()) {
        echo '<tr><td>' . htmlspecialchars($r['SHW_ID']) . '</td><td>' . htmlspecialchars($r['MOV_TITLE']) . '</td><td>' . htmlspecialchars($r['SHW_START_TIME']) . '</td></tr>';
    }
    echo '</table>';
} else {
    echo '<p>No showings found for that date' . (!empty($movie) ? ' and movie' : '') . '.</p>';
}

// Specific check for a movie/time if provided via GET
if (!empty($_GET['time']) && !empty($_GET['movie'])) {
    $time = date('H:i:s', strtotime($_GET['time']));
    $stmt2 = $mysqli->prepare("SELECT s.SHW_ID FROM " . TBL_SHOWING . " s JOIN " . TBL_MOVIE . " m ON s.MOV_ID = m.MOV_ID WHERE m.MOV_TITLE = ? AND DATE(s.SHW_START_TIME) = ? AND TIME(s.SHW_START_TIME) = ? LIMIT 1");
    $stmt2->bind_param('sss', $_GET['movie'], $date, $time);
    $stmt2->execute();
    $r2 = $stmt2->get_result()->fetch_assoc();
    echo '<h3>Specific lookup for movie/time</h3>';
    if ($r2) {
        echo '<p>Found showing ID: <strong>' . htmlspecialchars($r2['SHW_ID']) . '</strong></p>';
    } else {
        echo '<p style="color: red;">No showing found for that movie/time.</p>';
    }
}

echo '<p style="margin-top:20px;color:#666">After debugging remove this file to avoid exposing DB info.</p>';

?>