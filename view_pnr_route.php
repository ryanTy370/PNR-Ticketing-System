<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'config/db_connect.php';

// Function to get all stations in route order
function getRouteStations($pdo) {
    // This query gets all the stations in the correct order based on the route segments
    $query = "
    WITH RECURSIVE RouteStations AS (
        -- Anchor: Start with Naga (find it by name)
        SELECT 
            ts.StationID, 
            ts.StationName, 
            l.City, 
            l.Province, 
            0 as SegmentOrder
        FROM 
            trainstations ts
        JOIN 
            locations l ON ts.LocationID = l.LocationID
        WHERE 
            ts.StationName = 'Naga City Station'
        
        UNION ALL
        
        -- Recursive part: follow the route segments
        SELECT 
            ts.StationID, 
            ts.StationName, 
            l.City, 
            l.Province, 
            rs.SegmentOrder + 1
        FROM 
            RouteStations rs
        JOIN 
            routes r ON rs.StationID = r.DepartureStationID
        JOIN 
            trainstations ts ON r.ArrivalStationID = ts.StationID
        JOIN 
            locations l ON ts.LocationID = l.LocationID
        WHERE 
            -- Only follow segments with reasonable distances (not the full route)
            r.Distance < 20 AND
            -- Avoid cycles by not revisiting stations
            NOT EXISTS (
                SELECT 1 FROM RouteStations rs2 
                WHERE rs2.StationID = ts.StationID
            )
    )
    
    SELECT * FROM RouteStations
    ORDER BY SegmentOrder;
    ";
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}

// Get train schedule information
function getTrainSchedules($pdo) {
    $query = "
    SELECT 
        s.ScheduleID,
        t.TrainName,
        t.TrainNumber,
        t.FleetType,
        ts_dep.StationName AS DepartureStation,
        l_dep.City AS DepartureCity,
        ts_arr.StationName AS ArrivalStation,
        l_arr.City AS ArrivalCity,
        s.DepartureTime,
        s.ArrivalTime,
        s.Fare,
        r.Distance,
        TIMESTAMPDIFF(MINUTE, s.DepartureTime, s.ArrivalTime) AS DurationMinutes
    FROM 
        schedules s
    JOIN 
        routes r ON s.RouteID = r.RouteID
    JOIN 
        trains t ON s.TrainID = t.TrainID
    JOIN 
        trainstations ts_dep ON r.DepartureStationID = ts_dep.StationID
    JOIN 
        trainstations ts_arr ON r.ArrivalStationID = ts_arr.StationID
    JOIN 
        locations l_dep ON ts_dep.LocationID = l_dep.LocationID
    JOIN 
        locations l_arr ON ts_arr.LocationID = l_arr.LocationID
    WHERE 
        (ts_dep.StationName = 'Naga City Station' AND ts_arr.StationName = 'Legazpi City Station')
        OR 
        (ts_dep.StationName = 'Legazpi City Station' AND ts_arr.StationName = 'Naga City Station')
    ORDER BY 
        s.DepartureTime ASC;
    ";
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}

// Get the stations
$stations = getRouteStations($pdo);
$schedules = getTrainSchedules($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PNR Bicol Express Route</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .route-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .station {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            position: relative;
        }
        .station-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #3498db;
            margin-right: 15px;
            z-index: 2;
        }
        .major-station .station-dot {
            width: 24px;
            height: 24px;
            background-color: #2c3e50;
        }
        .station-info {
            flex: 1;
        }
        .station-name {
            font-weight: bold;
            margin-bottom: 0;
        }
        .station-location {
            color: #666;
            font-size: 0.9em;
        }
        .route-line {
            position: absolute;
            left: 10px;
            top: 20px;
            width: 2px;
            height: calc(100% + 15px);
            background-color: #3498db;
            z-index: 1;
        }
        .station:last-child .route-line {
            display: none;
        }
        .schedule-card {
            margin-bottom: 20px;
            border-left: 5px solid #3498db;
        }
        .schedule-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .time-display {
            font-size: 1.2em;
            font-weight: bold;
        }
        .back-link {
            margin-top: 20px;
        }
        .major-station {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4">PNR Bicol Express Route</h1>
        
        <div class="row">
            <div class="col-md-7">
                <div class="route-container">
                    <h2 class="mb-4">Stations</h2>
                    
                    <?php 
                    // Major stations that should be highlighted
                    $majorStations = ['Naga City Station', 'Pili Station', 'Iriga City Station', 'Polangui Station', 
                                     'Ligao City Station', 'Daraga Station', 'Legazpi City Station'];
                    
                    if (isset($stations['error'])) { 
                        echo '<div class="alert alert-danger">' . $stations['error'] . '</div>';
                    } else {
                        foreach ($stations as $index => $station) { 
                            $isMajor = in_array($station['StationName'], $majorStations);
                            $stationClass = $isMajor ? 'station major-station' : 'station';
                    ?>
                        <div class="<?php echo $stationClass; ?>">
                            <div class="station-dot"></div>
                            <div class="route-line"></div>
                            <div class="station-info">
                                <p class="station-name"><?php echo htmlspecialchars($station['StationName']); ?></p>
                                <p class="station-location"><?php echo htmlspecialchars($station['City']) . ', ' . htmlspecialchars($station['Province']); ?></p>
                            </div>
                        </div>
                    <?php 
                        }
                    } 
                    ?>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="route-container">
                    <h2 class="mb-4">Schedules</h2>
                    
                    <?php 
                    if (isset($schedules['error'])) { 
                        echo '<div class="alert alert-danger">' . $schedules['error'] . '</div>';
                    } else {
                        foreach ($schedules as $schedule) { 
                            $departTime = new DateTime($schedule['DepartureTime']);
                            $arriveTime = new DateTime($schedule['ArrivalTime']);
                    ?>
                        <div class="card schedule-card">
                            <div class="card-body">
                                <h5 class="schedule-title">
                                    <?php echo htmlspecialchars($schedule['TrainName']); ?> 
                                    (Train #<?php echo htmlspecialchars($schedule['TrainNumber']); ?>)
                                </h5>
                                
                                <p><strong>Route:</strong> 
                                    <?php echo htmlspecialchars($schedule['DepartureStation']) . ' → ' . htmlspecialchars($schedule['ArrivalStation']); ?>
                                </p>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <p>Departure</p>
                                        <p class="time-display"><?php echo $departTime->format('h:i A'); ?></p>
                                        <p><?php echo htmlspecialchars($schedule['DepartureCity']); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p>Arrival</p>
                                        <p class="time-display"><?php echo $arriveTime->format('h:i A'); ?></p>
                                        <p><?php echo htmlspecialchars($schedule['ArrivalCity']); ?></p>
                                    </div>
                                </div>
                                
                                <p><strong>Duration:</strong> 
                                    <?php 
                                    $hours = floor($schedule['DurationMinutes'] / 60);
                                    $minutes = $schedule['DurationMinutes'] % 60;
                                    echo "$hours hr $minutes min";
                                    ?>
                                </p>
                                
                                <p><strong>Fare:</strong> ₱<?php echo number_format($schedule['Fare'], 2); ?></p>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($schedule['FleetType']); ?></p>
                            </div>
                        </div>
                    <?php 
                        }
                    } 
                    ?>
                </div>
            </div>
        </div>
        
        <div class="back-link">
            <a href="index.php" class="btn btn-primary">Back to Home</a>
            <a href="admin side/admin_dashboard.php" class="btn btn-secondary">Admin Dashboard</a>
            <a href="update_pnr_routes.php" class="btn btn-success">Update Stations</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 