<?php
// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Set headers for JSON response
header('Content-Type: application/json');

// Check if config file exists
if (!file_exists('config.php')) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found']);
    exit;
}

require_once 'config.php';

// Get the date from query parameters
$date = isset($_GET['date']) ? $_GET['date'] : null;
$fleetType = isset($_GET['fleet_type']) ? $_GET['fleet_type'] : null;

// Validate date
if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

try {
    // Create database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Base query to get all schedules for the specified date
    $query = "
        SELECT 
            s.ScheduleID as id,
            s.DepartureTime as departureTime,
            s.ArrivalTime as arrivalTime,
            s.Fare as fare,
            t.TrainID as trainId,
            t.TrainName as trainName,
            t.TrainNumber as trainNumber,
            t.FleetType as fleetType,
            t.Capacity as totalSeats,
            COALESCE(t.Capacity - COUNT(tk.TicketID), t.Capacity) as seatsAvailable,
            dep.StationName as departureStation,
            arr.StationName as arrivalStation,
            dep_loc.City as departureCity,
            arr_loc.City as arrivalCity
        FROM 
            schedules s
            JOIN trains t ON s.TrainID = t.TrainID
            JOIN routes r ON s.RouteID = r.RouteID
            JOIN trainstations dep ON r.DepartureStationID = dep.StationID
            JOIN trainstations arr ON r.ArrivalStationID = arr.StationID
            JOIN locations dep_loc ON dep.LocationID = dep_loc.LocationID
            JOIN locations arr_loc ON arr.LocationID = arr_loc.LocationID
            LEFT JOIN tickets tk ON s.ScheduleID = tk.ScheduleID AND tk.TicketStatus != 'Cancelled'
        WHERE 
            DATE(s.DepartureTime) = :date";
    
    // Add fleet type filter if provided
    if ($fleetType) {
        $query .= " AND t.FleetType = :fleetType";
    }
    
    // Add group by and order by
    $query .= "
        GROUP BY 
            s.ScheduleID
        ORDER BY 
            s.DepartureTime ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':date', $date);
    
    // Bind fleet type parameter if provided
    if ($fleetType) {
        $stmt->bindParam(':fleetType', $fleetType);
    }
    
    $stmt->execute();
    
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Clean up the data to ensure proper JSON encoding
    foreach ($schedules as &$schedule) {
        $schedule['id'] = intval($schedule['id']);
        $schedule['trainId'] = intval($schedule['trainId']);
        $schedule['trainNumber'] = intval($schedule['trainNumber']);
        $schedule['totalSeats'] = intval($schedule['totalSeats']);
        $schedule['seatsAvailable'] = intval($schedule['seatsAvailable']);
        $schedule['fare'] = floatval($schedule['fare']);
    }
    
    // Return the result
    echo json_encode([
        'success' => true,
        'schedules' => $schedules,
        'count' => count($schedules)
    ]);
    
} catch(PDOException $e) {
    // Log error to file instead of showing to user
    error_log('Database error in get_all_schedules_by_date.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again later.']);
} catch(Exception $e) {
    error_log('General error in get_all_schedules_by_date.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?> 