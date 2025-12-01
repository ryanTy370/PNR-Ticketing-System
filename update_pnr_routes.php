<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'config/db_connect.php';

// Function to execute SQL and display results
function executeSQL($pdo, $sql, $description) {
    echo "<h3>$description</h3>";
    try {
        $result = $pdo->exec($sql);
        echo "<div style='color: green;'>Success! Affected rows: $result</div>";
        return true;
    } catch (PDOException $e) {
        echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
        return false;
    }
}

// HTML header
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Update PNR Routes and Stations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        h3 { margin-top: 20px; color: #555; }
        .success { color: green; }
        .error { color: red; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Updating PNR Routes and Stations</h1>
    <p>This script will update the database with the official PNR stations and routes.</p>
    <div class='section'>";

// Start transaction
$pdo->beginTransaction();

try {
    // Insert Locations
    $locations = [
        ['Naga', 'Camarines Sur'],
        ['Pili', 'Camarines Sur'],
        ['Baao', 'Camarines Sur'],
        ['Iriga', 'Camarines Sur'],
        ['Lourdes', 'Camarines Sur'],
        ['Bato', 'Camarines Sur'],
        ['Matacon', 'Albay'],
        ['Polangui', 'Albay'],
        ['Oas', 'Albay'],
        ['Ligao', 'Albay'],
        ['Travesia', 'Albay'],
        ['Daraga', 'Albay'],
        ['Bagtang', 'Albay'],
        ['Washington Drive', 'Albay'],
        ['Capantawan', 'Albay'],
        ['Legazpi', 'Albay']
    ];

    echo "<h3>Adding locations if they don't exist</h3>";
    $locationsAdded = 0;
    
    foreach ($locations as $location) {
        $city = $location[0];
        $province = $location[1];
        
        $checkSql = "SELECT COUNT(*) FROM locations WHERE City = ? AND Province = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$city, $province]);
        
        if ($checkStmt->fetchColumn() == 0) {
            $insertSql = "INSERT INTO locations (City, Province) VALUES (?, ?)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([$city, $province]);
            $locationsAdded++;
        }
    }
    
    echo "<div class='success'>$locationsAdded new locations added</div>";

    // Get location IDs
    $locationIds = [];
    foreach ($locations as $location) {
        $city = $location[0];
        $province = $location[1];
        
        $sql = "SELECT LocationID FROM locations WHERE City = ? AND Province = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$city, $province]);
        $locationId = $stmt->fetchColumn();
        
        if ($locationId) {
            $locationIds[strtolower(str_replace(' ', '_', $city))] = $locationId;
        }
    }

    // Add Train Stations
    $stations = [
        ['Naga City Station', $locationIds['naga']],
        ['Pili Station', $locationIds['pili']],
        ['Baao Station', $locationIds['baao']],
        ['Iriga City Station', $locationIds['iriga']],
        ['Lourdes Station', $locationIds['lourdes']],
        ['Bato Station', $locationIds['bato']],
        ['Matacon Station', $locationIds['matacon']],
        ['Polangui Station', $locationIds['polangui']],
        ['Oas Station', $locationIds['oas']],
        ['Ligao City Station', $locationIds['ligao']],
        ['Travesia Station', $locationIds['travesia']],
        ['Daraga Station', $locationIds['daraga']],
        ['Bagtang Station', $locationIds['bagtang']],
        ['Washington Drive Station', $locationIds['washington_drive']],
        ['Capantawan Station', $locationIds['capantawan']],
        ['Legazpi City Station', $locationIds['legazpi']]
    ];

    echo "<h3>Adding train stations if they don't exist</h3>";
    $stationsAdded = 0;
    
    foreach ($stations as $station) {
        $stationName = $station[0];
        $locationId = $station[1];
        
        $checkSql = "SELECT COUNT(*) FROM trainstations WHERE StationName = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$stationName]);
        
        if ($checkStmt->fetchColumn() == 0) {
            $insertSql = "INSERT INTO trainstations (StationName, LocationID) VALUES (?, ?)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([$stationName, $locationId]);
            $stationsAdded++;
        }
    }
    
    echo "<div class='success'>$stationsAdded new stations added</div>";

    // Get station IDs
    $stationIds = [];
    foreach ($stations as $station) {
        $stationName = $station[0];
        
        $sql = "SELECT StationID FROM trainstations WHERE StationName = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$stationName]);
        $stationId = $stmt->fetchColumn();
        
        if ($stationId) {
            $key = strtolower(str_replace([' Station', ' City', ' '], '', $stationName));
            $stationIds[$key] = $stationId;
        }
    }

    // Create routes between adjacent stations
    $routeSegments = [
        [$stationIds['naga'], $stationIds['pili'], 10.0],
        [$stationIds['pili'], $stationIds['baao'], 8.0],
        [$stationIds['baao'], $stationIds['iriga'], 10.0],
        [$stationIds['iriga'], $stationIds['lourdes'], 5.0],
        [$stationIds['lourdes'], $stationIds['bato'], 7.0],
        [$stationIds['bato'], $stationIds['matacon'], 9.0],
        [$stationIds['matacon'], $stationIds['polangui'], 6.0],
        [$stationIds['polangui'], $stationIds['oas'], 8.0],
        [$stationIds['oas'], $stationIds['ligao'], 10.0],
        [$stationIds['ligao'], $stationIds['travesia'], 8.0],
        [$stationIds['travesia'], $stationIds['daraga'], 12.0],
        [$stationIds['daraga'], $stationIds['bagtang'], 5.0],
        [$stationIds['bagtang'], $stationIds['washingtondrive'], 4.0],
        [$stationIds['washingtondrive'], $stationIds['capantawan'], 3.0],
        [$stationIds['capantawan'], $stationIds['legazpi'], 5.0]
    ];

    echo "<h3>Adding route segments between adjacent stations</h3>";
    $segmentsAdded = 0;
    
    foreach ($routeSegments as $segment) {
        $departure = $segment[0];
        $arrival = $segment[1];
        $distance = $segment[2];
        
        $checkSql = "SELECT COUNT(*) FROM routes WHERE DepartureStationID = ? AND ArrivalStationID = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$departure, $arrival]);
        
        if ($checkStmt->fetchColumn() == 0) {
            $insertSql = "INSERT INTO routes (DepartureStationID, ArrivalStationID, Distance) VALUES (?, ?, ?)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([$departure, $arrival, $distance]);
            $segmentsAdded++;
        }
    }
    
    echo "<div class='success'>$segmentsAdded new route segments added</div>";

    // Create the main routes (Naga to Legazpi and vice versa)
    echo "<h3>Adding main routes</h3>";
    $mainRoutesAdded = 0;
    
    $checkSql = "SELECT COUNT(*) FROM routes WHERE DepartureStationID = ? AND ArrivalStationID = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$stationIds['naga'], $stationIds['legazpi']]);
    
    if ($checkStmt->fetchColumn() == 0) {
        $insertSql = "INSERT INTO routes (DepartureStationID, ArrivalStationID, Distance) VALUES (?, ?, ?)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([$stationIds['naga'], $stationIds['legazpi'], 120.0]);
        $mainRoutesAdded++;
    }
    
    $checkStmt->execute([$stationIds['legazpi'], $stationIds['naga']]);
    
    if ($checkStmt->fetchColumn() == 0) {
        $insertSql = "INSERT INTO routes (DepartureStationID, ArrivalStationID, Distance) VALUES (?, ?, ?)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([$stationIds['legazpi'], $stationIds['naga'], 120.0]);
        $mainRoutesAdded++;
    }
    
    echo "<div class='success'>$mainRoutesAdded main routes added</div>";

    // Create trains for the routes if they don't exist
    echo "<h3>Adding trains</h3>";
    $trainsAdded = 0;
    
    $trainNames = [
        'Bicol Express Morning' => [901, 'Regular', 320, 'Active'],
        'Bicol Express Afternoon' => [902, 'Regular', 320, 'Active']
    ];
    
    foreach ($trainNames as $trainName => $trainDetails) {
        $checkSql = "SELECT COUNT(*) FROM trains WHERE TrainName = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$trainName]);
        
        if ($checkStmt->fetchColumn() == 0) {
            $insertSql = "INSERT INTO trains (TrainName, TrainNumber, FleetType, TotalSeats, Status) 
                         VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([$trainName, $trainDetails[0], $trainDetails[1], $trainDetails[2], $trainDetails[3]]);
            $trainsAdded++;
        }
    }
    
    echo "<div class='success'>$trainsAdded new trains added</div>";

    // Get the route IDs for main routes
    $getNagaToLegazpiSql = "SELECT RouteID FROM routes WHERE DepartureStationID = ? AND ArrivalStationID = ?";
    $getLegazpiToNagaSql = "SELECT RouteID FROM routes WHERE DepartureStationID = ? AND ArrivalStationID = ?";
    
    $getNagaToLegazpiStmt = $pdo->prepare($getNagaToLegazpiSql);
    $getLegazpiToNagaStmt = $pdo->prepare($getLegazpiToNagaSql);
    
    $getNagaToLegazpiStmt->execute([$stationIds['naga'], $stationIds['legazpi']]);
    $getLegazpiToNagaStmt->execute([$stationIds['legazpi'], $stationIds['naga']]);
    
    $nagaToLegazpiRouteId = $getNagaToLegazpiStmt->fetchColumn();
    $legazpiToNagaRouteId = $getLegazpiToNagaStmt->fetchColumn();

    // Get train IDs
    $getTrainIdSql = "SELECT TrainID FROM trains WHERE TrainName = ?";
    $getMorningTrainStmt = $pdo->prepare($getTrainIdSql);
    $getAfternoonTrainStmt = $pdo->prepare($getTrainIdSql);
    
    $getMorningTrainStmt->execute(['Bicol Express Morning']);
    $getAfternoonTrainStmt->execute(['Bicol Express Afternoon']);
    
    $morningTrainId = $getMorningTrainStmt->fetchColumn();
    $afternoonTrainId = $getAfternoonTrainStmt->fetchColumn();

    // Create new schedules
    echo "<h3>Adding schedules</h3>";
    $schedulesAdded = 0;
    
    // Morning schedule (5:38 AM to 8:42 AM)
    $checkMorningSql = "SELECT COUNT(*) FROM schedules 
                       WHERE TrainID = ? AND RouteID = ? 
                       AND TIME(DepartureTime) = '05:38:00' AND TIME(ArrivalTime) = '08:42:00'";
    $checkMorningStmt = $pdo->prepare($checkMorningSql);
    $checkMorningStmt->execute([$morningTrainId, $nagaToLegazpiRouteId]);
    
    if ($checkMorningStmt->fetchColumn() == 0) {
        $insertSql = "INSERT INTO schedules (TrainID, RouteID, DepartureTime, ArrivalTime, Fare) 
                     VALUES (?, ?, CONCAT(CURDATE(), ' 05:38:00'), CONCAT(CURDATE(), ' 08:42:00'), ?)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([$morningTrainId, $nagaToLegazpiRouteId, 150.00]);
        $schedulesAdded++;
    }
    
    // Afternoon schedule (2:30 PM to 5:34 PM)
    $checkAfternoonSql = "SELECT COUNT(*) FROM schedules 
                         WHERE TrainID = ? AND RouteID = ? 
                         AND TIME(DepartureTime) = '14:30:00' AND TIME(ArrivalTime) = '17:34:00'";
    $checkAfternoonStmt = $pdo->prepare($checkAfternoonSql);
    $checkAfternoonStmt->execute([$afternoonTrainId, $legazpiToNagaRouteId]);
    
    if ($checkAfternoonStmt->fetchColumn() == 0) {
        $insertSql = "INSERT INTO schedules (TrainID, RouteID, DepartureTime, ArrivalTime, Fare) 
                     VALUES (?, ?, CONCAT(CURDATE(), ' 14:30:00'), CONCAT(CURDATE(), ' 17:34:00'), ?)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([$afternoonTrainId, $legazpiToNagaRouteId, 150.00]);
        $schedulesAdded++;
    }
    
    echo "<div class='success'>$schedulesAdded new schedules added</div>";

    // Commit transaction if everything succeeded
    $pdo->commit();
    echo "<h3 class='success'>All updates completed successfully!</h3>";
    
} catch (PDOException $e) {
    // Roll back the transaction if there was an error
    $pdo->rollBack();
    echo "<h3 class='error'>Error occurred: " . $e->getMessage() . "</h3>";
}

// HTML footer
echo "</div>
    <p>Return to <a href='admin side/admin_dashboard.php'>Admin Dashboard</a> or <a href='admin side/manage_stations.php'>Manage Stations</a>.</p>
</body>
</html>";
?> 