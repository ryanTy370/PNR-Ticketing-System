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
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

// Get parameters
$departureStationID = $_GET['departure'] ?? 0;
$arrivalStationID = $_GET['arrival'] ?? 0;
$travelDate = $_GET['date'] ?? '';
$fleetType = $_GET['fleet_type'] ?? '';

// Debug logging
error_log("Departure Station ID: " . $departureStationID);
error_log("Arrival Station ID: " . $arrivalStationID);
error_log("Travel Date: " . $travelDate);
error_log("Fleet Type: " . $fleetType);

// Validate inputs
if (empty($departureStationID) || empty($arrivalStationID) || empty($travelDate)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

// Format date for query
$formattedDate = date('Y-m-d', strtotime($travelDate));

// Base query to find schedules
$query = "SELECT 
    s.ScheduleID,
    s.DepartureTime,
    s.ArrivalTime,
    s.Fare,
    t.TrainID,
    t.TrainName,
    t.TrainNumber,
    t.FleetType,
    t.Capacity,
    ts_dep.StationName as DepartureStation,
    ts_arr.StationName as ArrivalStation,
    r.Distance,
    (SELECT COUNT(*) FROM Tickets WHERE ScheduleID = s.ScheduleID AND TicketStatus != 'Cancelled') as BookedSeats
FROM Schedules s
JOIN Routes r ON s.RouteID = r.RouteID
JOIN Trains t ON s.TrainID = t.TrainID
JOIN TrainStations ts_dep ON r.DepartureStationID = ts_dep.StationID
JOIN TrainStations ts_arr ON r.ArrivalStationID = ts_arr.StationID
WHERE r.DepartureStationID = ? 
AND r.ArrivalStationID = ?
AND DATE(s.DepartureTime) = ?";

// Add fleet type filter if provided
$params = [$departureStationID, $arrivalStationID, $formattedDate];
$types = "iis";

if (!empty($fleetType)) {
    $query .= " AND t.FleetType = ?";
    $params[] = $fleetType;
    $types .= "s";
}

// Add order by
$query .= " ORDER BY s.DepartureTime ASC";

// Debug logging
error_log("Query: " . $query);
error_log("Parameters: " . implode(", ", $params));

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Debug logging
error_log("Number of rows found: " . $result->num_rows);

if ($result) {
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        // Format times for display
        $departureTime = date('h:i A', strtotime($row['DepartureTime']));
        $arrivalTime = date('h:i A', strtotime($row['ArrivalTime']));
        
        // Calculate available seats
        $availableSeats = max(0, $row['Capacity'] - $row['BookedSeats']);
        $availabilityPercent = $row['Capacity'] > 0 ? round(($availableSeats / $row['Capacity']) * 100) : 0;
        
        // Calculate journey duration
        $departure = new DateTime($row['DepartureTime']);
        $arrival = new DateTime($row['ArrivalTime']);
        $interval = $departure->diff($arrival);
        $durationMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
        
        // Create a schedule object
        $schedules[] = [
            'id' => $row['ScheduleID'],
            'trainId' => $row['TrainID'],
            'departureTime' => $row['DepartureTime'],
            'arrivalTime' => $row['ArrivalTime'],
            'departureTimeFormatted' => $departureTime,
            'arrivalTimeFormatted' => $arrivalTime,
            'trainName' => $row['TrainName'],
            'trainNumber' => $row['TrainNumber'],
            'fleetType' => $row['FleetType'],
            'departureStation' => $row['DepartureStation'],
            'arrivalStation' => $row['ArrivalStation'],
            'distance' => $row['Distance'],
            'fare' => $row['Fare'],
            'totalSeats' => (int)$row['Capacity'],
            'seatsAvailable' => (int)$availableSeats,
            'capacity' => (int)$row['Capacity'],
            'availableSeats' => (int)$availableSeats,
            'bookedSeats' => (int)$row['BookedSeats'],
            'availabilityPercent' => (int)$availabilityPercent,
            'durationMinutes' => $durationMinutes,
            'departureTimestamp' => strtotime($row['DepartureTime']),
            'arrivalTimestamp' => strtotime($row['ArrivalTime'])
        ];
    }
    
    // Sort schedules by departure time
    usort($schedules, function($a, $b) {
        return $a['departureTimestamp'] - $b['departureTimestamp'];
    });
    
    // Debug logging
    error_log("Schedules found: " . json_encode($schedules));
    
    echo json_encode(['success' => true, 'schedules' => $schedules]);
} else {
    echo json_encode(['success' => false, 'message' => "Error: " . $conn->error]);
}

$stmt->close();
$conn->close();

// Log the actual SQL query with parameters for debugging
error_log("SCHEDULE QUERY: " . $query . " - Params: " . implode(", ", $params));
?>