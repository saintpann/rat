<?php
// Test page to verify seat POST data

echo "<h2>Seat POST Data Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    var_dump($_POST);
    echo "</pre>";
    
    $seats = $_POST['seat'] ?? [];
    echo "<h3>Seats Array:</h3>";
    echo "<pre>";
    var_dump($seats);
    echo "</pre>";
    
    if (is_array($seats)) {
        echo "<p>Number of seats: " . count($seats) . "</p>";
        echo "<ul>";
        foreach ($seats as $seat) {
            echo "<li>" . htmlspecialchars($seat) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Seats is not an array!</p>";
    }
} else {
    echo "<p>No POST data. Create a test form:</p>";
    echo "<form method='POST'>";
    echo "  <input type='checkbox' name='seat' value='A1'> A1<br>";
    echo "  <input type='checkbox' name='seat' value='A2'> A2<br>";
    echo "  <input type='checkbox' name='seat' value='A3'> A3<br>";
    echo "  <button type='submit'>Submit</button>";
    echo "</form>";
}
?>
