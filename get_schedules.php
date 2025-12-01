<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bicol_express_online_ticketing_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Get parameters
$trainId = filter_var($_GET['train_id'], FILTER_SANITIZE_NUMBER_INT);
$fleetType = filter_var($_GET['fleet_type'], FILTER_SANITIZE_STRING);
$travelDate = filter_var($_GET['travel_date'], FILTER_SANITIZE_STRING);

// Debug logging
error_log("Train ID: " . $trainId);
error_log("Fleet Type: " . $fleetType);
error_log("Travel Date: " . $travelDate);

// Validate inputs
if (empty($trainId) || empty($fleetType) || empty($travelDate)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

// Format date for query
$formattedDate = date('Y-m-d', strtotime($travelDate));

// Query to find schedules for the selected train, fleet type, and date
$query = "SELECT 
    s.ScheduleID,
    DATE_FORMAT(s.DepartureTime, '%H:%i') as DepartureTime,
    DATE_FORMAT(s.ArrivalTime, '%H:%i') as ArrivalTime,
    r.RouteID,
    r.Distance,
    ts_dep.StationName as DepartureStation,
    ts_arr.StationName as ArrivalStation,
    t.FleetType
FROM Schedules s
JOIN Routes r ON s.RouteID = r.RouteID
JOIN TrainStations ts_dep ON r.DepartureStationID = ts_dep.StationID
JOIN TrainStations ts_arr ON r.ArrivalStationID = ts_arr.StationID
JOIN Trains t ON s.TrainID = t.TrainID
WHERE s.TrainID = ? 
AND t.FleetType = ?
AND DATE(s.DepartureTime) = ?
ORDER BY s.DepartureTime";

// Debug logging
error_log("Query: " . $query);

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $trainId, $fleetType, $formattedDate);
$stmt->execute();
$result = $stmt->get_result();

// Set header before sending response
header('Content-Type: application/json');

if ($result) {
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        // Check if this is the Naga-Legazpi route (or vice versa)
        $isNagaLegazpiRoute = false;
        
        // Check if the current route is between Naga and Legazpi (in either direction)
        if (($row['DepartureStation'] == 'Naga Railway Hub' && $row['ArrivalStation'] == 'Legazpi Central Station') ||
            ($row['DepartureStation'] == 'Legazpi Central Station' && $row['ArrivalStation'] == 'Naga Railway Hub')) {
            $isNagaLegazpiRoute = true;
        }
        
        // Get the fare from the database instead of calculating it dynamically
        // This ensures consistency with the admin-set fare values
        $fareQuery = "SELECT Fare FROM Schedules WHERE ScheduleID = ?";
        $fareStmt = $conn->prepare($fareQuery);
        $fareStmt->bind_param("i", $row['ScheduleID']);
        $fareStmt->execute();
        $fareResult = $fareStmt->get_result();
        $fareData = $fareResult->fetch_assoc();
        
        if ($fareData && $fareData['Fare'] > 0) {
            // Use the fare set in the admin panel
            $row['Fare'] = $fareData['Fare'];
        } else {
            // Fallback to calculation if no fare is set (should not happen)
            $baseRate = 10; // Base rate per kilometer
            $fleetTypeMultiplier = [
                'Economy' => 1,
                'Reclining Aircon' => 1.5,
                'Family Sleeper' => 2,
                'Executive Sleeper' => 2.5,
                'Regular' => 1
            ];
            $row['Fare'] = $row['Distance'] * $baseRate * $fleetTypeMultiplier[$row['FleetType']];
        }
        
        // Close the fare statement
        $fareStmt->close();
        
        $schedules[] = $row;
    }
    
    // Debug logging
    error_log("Schedules found: " . count($schedules));
    
    echo json_encode($schedules);
} else {
    // Debug logging
    error_log("Error executing query: " . $conn->error);
    
    echo json_encode([]);
}

$stmt->close();
$conn->close();
?>