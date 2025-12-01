<?php
// Database connection
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "bicol_express_online_ticketing_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

// Query to get all trains
$query = "SELECT 
    TrainID,
    TrainNumber,
    TrainName,
    FleetType
FROM Trains
ORDER BY TrainName, TrainNumber";

$result = $conn->query($query);

if ($result) {
    $trains = [];
    while ($row = $result->fetch_assoc()) {
        $trains[] = $row;
    }
    echo json_encode(['success' => true, 'trains' => $trains]);
} else {
    echo json_encode(['success' => false, 'message' => "Error: " . $conn->error]);
}

$conn->close();
?> 