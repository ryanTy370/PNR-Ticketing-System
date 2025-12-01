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

// Get parameters
$departureStationID = isset($_GET['departure']) ? $_GET['departure'] : null;
$arrivalStationID = isset($_GET['arrival']) ? $_GET['arrival'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$fleetType = isset($_GET['fleet_type']) ? $_GET['fleet_type'] : null;

// Validate inputs
if (!$departureStationID || !$arrivalStationID) {
    echo json_encode(['success' => false, 'message' => 'Departure and arrival stations are required']);
    exit;
}

try {
    // Create database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Calculate date range for the given month
    $startDate = "$year-$month-01";
    $endDate = date('Y-m-t', strtotime($startDate));
    
    // Base query to find dates with available schedules
    $query = "
        SELECT 
            DATE(s.DepartureTime) as available_date,
            COUNT(s.ScheduleID) as schedule_count
        FROM 
            schedules s
            JOIN routes r ON s.RouteID = r.RouteID
            JOIN trains t ON s.TrainID = t.TrainID
        WHERE 
            r.DepartureStationID = :departureStationID
            AND r.ArrivalStationID = :arrivalStationID
            AND DATE(s.DepartureTime) BETWEEN :startDate AND :endDate
    ";
    
    // Add fleet type filter if provided
    if ($fleetType && $fleetType !== 'all') {
        $query .= " AND t.FleetType = :fleetType";
    }
    
    // Group by date
    $query .= " GROUP BY DATE(s.DepartureTime) ORDER BY DATE(s.DepartureTime)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':departureStationID', $departureStationID);
    $stmt->bindParam(':arrivalStationID', $arrivalStationID);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    
    // Bind fleet type parameter if provided
    if ($fleetType && $fleetType !== 'all') {
        $stmt->bindParam(':fleetType', $fleetType);
    }
    
    $stmt->execute();
    
    // Process results
    $availableDates = [];
    $scheduleCounts = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $availableDates[] = $row['available_date'];
        $scheduleCounts[$row['available_date']] = (int)$row['schedule_count'];
    }
    
    // Return response
    echo json_encode([
        'success' => true,
        'availableDates' => $availableDates,
        'scheduleCounts' => $scheduleCounts
    ]);
    
} catch(PDOException $e) {
    error_log('Database error in check_available_dates.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again later.']);
} catch(Exception $e) {
    error_log('General error in check_available_dates.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?> 