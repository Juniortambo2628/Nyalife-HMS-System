<?php
// Include the database configuration
require_once 'config/database.php';

// Connect to the database
$conn = connectDB();

// Get the table structure
$result = $conn->query("DESCRIBE consultations");

if ($result) {
    echo "<h2>Consultations Table Structure</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error describing table: " . $conn->error;
}

// Close the connection
$conn->close();
?>
