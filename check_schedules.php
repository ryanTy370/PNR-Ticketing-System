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
    die("Connection failed: " . $conn->connect_error);
}

// Query to check all schedules in the table
$query = "SELECT 
    s.ScheduleID,
    s.TrainID,
    s.RouteID,
    s.DepartureTime,
    s.ArrivalTime,
    t.TrainName,
    t.TrainNumber,
    ts_dep.StationName as DepartureStation,
    ts_arr.StationName as ArrivalStation
FROM Schedules s
JOIN Routes r ON s.RouteID = r.RouteID
JOIN Trains t ON s.TrainID = t.TrainID
JOIN TrainStations ts_dep ON r.DepartureStationID = ts_dep.StationID
JOIN TrainStations ts_arr ON r.ArrivalStationID = ts_arr.StationID
ORDER BY s.DepartureTime";

$result = $conn->query($query);

if ($result) {
    echo "<h2>Schedule Records Check</h2>";
    
    if ($result->num_rows > 0) {
        echo "<p>Found " . $result->num_rows . " schedule records.</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Train</th><th>Route</th><th>Departure Station</th><th>Arrival Station</th><th>Departure Time</th><th>Arrival Time</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['ScheduleID'] . "</td>";
            echo "<td>" . $row['TrainName'] . " (" . $row['TrainNumber'] . ")</td>";
            echo "<td>" . $row['RouteID'] . "</td>";
            echo "<td>" . $row['DepartureStation'] . "</td>";
            echo "<td>" . $row['ArrivalStation'] . "</td>";
            echo "<td>" . $row['DepartureTime'] . "</td>";
            echo "<td>" . $row['ArrivalTime'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No schedule records found in the database.</p>";
        
        // Check if there are trains in the database
        $trainQuery = "SELECT COUNT(*) as count FROM Trains";
        $trainResult = $conn->query($trainQuery);
        $trainCount = $trainResult->fetch_assoc()['count'];
        
        echo "<p>Trains in database: " . $trainCount . "</p>";
        
        // Check if there are routes in the database
        $routeQuery = "SELECT COUNT(*) as count FROM Routes";
        $routeResult = $conn->query($routeQuery);
        $routeCount = $routeResult->fetch_assoc()['count'];
        
        echo "<p>Routes in database: " . $routeCount . "</p>";
    }
} else {
    echo "Error executing query: " . $conn->error;
}

$conn->close();
?> 