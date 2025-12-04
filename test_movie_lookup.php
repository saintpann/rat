<?php
require_once __DIR__ . '/config.php';

echo "<h2>Movie Lookup Test</h2>";

// Test direct LOWER match
echo "<h3>Test 1: Direct LOWER() match for 'beautifulboy'</h3>";
$query = "SELECT MOV_TITLE FROM movie WHERE LOWER(MOV_TITLE) = LOWER('beautifulboy') LIMIT 1";
echo "<p>Query: " . htmlspecialchars($query) . "</p>";
$result = $mysqli->query($query);
if ($result) {
    echo "<p>Rows found: " . $result->num_rows . "</p>";
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<p>Result: " . htmlspecialchars($row['MOV_TITLE']) . "</p>";
    }
} else {
    echo "<p style='color:red'>Error: " . htmlspecialchars($mysqli->error) . "</p>";
}

// Test normalized slug matching
echo "<h3>Test 2: Slug normalization match for 'beautifulboy'</h3>";
$norm = preg_replace('/[^a-z0-9]/i', '', strtolower('beautifulboy'));
echo "<p>Input normalized to: '$norm'</p>";
$query = "SELECT MOV_TITLE FROM movie";
$result = $mysqli->query($query);
if ($result && $result->num_rows > 0) {
    echo "<p>All movies in database:</p><ul>";
    while ($row = $result->fetch_assoc()) {
        $cand = preg_replace('/[^a-z0-9]/i', '', strtolower($row['MOV_TITLE']));
        $match = ($cand === $norm) ? " ✓ MATCH!" : "";
        echo "<li>" . htmlspecialchars($row['MOV_TITLE']) . " -> norm: '$cand'$match</li>";
    }
    echo "</ul>";
}

// Test LIKE match
echo "<h3>Test 3: LIKE match for 'beautifulboy'</h3>";
$query = "SELECT MOV_TITLE FROM movie WHERE LOWER(MOV_TITLE) LIKE LOWER('%beautifulboy%') LIMIT 1";
echo "<p>Query: " . htmlspecialchars($query) . "</p>";
$result = $mysqli->query($query);
if ($result) {
    echo "<p>Rows found: " . $result->num_rows . "</p>";
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<p>Result: " . htmlspecialchars($row['MOV_TITLE']) . "</p>";
    }
} else {
    echo "<p style='color:red'>Error: " . htmlspecialchars($mysqli->error) . "</p>";
}

echo "<p style='margin-top:20px'><a href='db_status.php'>Back to DB Status</a></p>";
?>
