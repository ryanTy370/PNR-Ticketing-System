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

// Query to get all stations with their location details
$query = "SELECT 
    ts.StationID,
    ts.StationName,
    l.City,
    l.Province
FROM TrainStations ts
JOIN Locations l ON ts.LocationID = l.LocationID
ORDER BY l.Province, l.City, ts.StationName";

$result = $conn->query($query);

if ($result) {
    $stations = [];
    while ($row = $result->fetch_assoc()) {
        $stations[] = $row;
    }
    echo json_encode(['success' => true, 'stations' => $stations]);
} else {
    echo json_encode(['success' => false, 'message' => "Error: " . $conn->error]);
}

$conn->close();
?> 