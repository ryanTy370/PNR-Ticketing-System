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

// Get query parameters
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// Validate month and year
if ($month < 1 || $month > 12) {
    echo json_encode(['success' => false, 'message' => 'Invalid month']);
    exit;
}

if ($year < 2000 || $year > 2100) {
    echo json_encode(['success' => false, 'message' => 'Invalid year']);
    exit;
}

try {
    // Create database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get the first and last day of the month
    $firstDay = sprintf('%04d-%02d-01', $year, $month);
    $lastDay = date('Y-m-t', strtotime($firstDay));
    
    // Query to get all dates with schedules
    $query = "
        SELECT 
            DATE(s.DepartureTime) as date,
            COUNT(*) as schedule_count
        FROM 
            schedules s
        WHERE 
            s.DepartureTime BETWEEN :firstDay AND :lastDay
        GROUP BY 
            DATE(s.DepartureTime)
        ORDER BY 
            date ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':firstDay', $firstDay);
    $stmt->bindParam(':lastDay', $lastDay);
    $stmt->execute();
    
    $availableDates = [];
    $scheduleCounts = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $availableDates[] = $row['date'];
        $scheduleCounts[$row['date']] = intval($row['schedule_count']);
    }
    
    // Return the result
    echo json_encode([
        'success' => true,
        'availableDates' => $availableDates,
        'scheduleCounts' => $scheduleCounts
    ]);
    
} catch(PDOException $e) {
    // Log error to file instead of showing to user
    error_log('Database error in get_all_available_dates.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again later.']);
} catch(Exception $e) {
    error_log('General error in get_all_available_dates.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?> 