<?php
require_once 'config/db_connect.php';

$scheduleId = filter_var($_GET['schedule_id'], FILTER_SANITIZE_NUMBER_INT);

// Get train capacity and already booked seats
$sql = "SELECT 
    tr.Capacity,
    GROUP_CONCAT(t.SeatNumber) as BookedSeats
FROM Schedules s
JOIN Trains tr ON s.TrainID = tr.TrainID
LEFT JOIN Tickets t ON s.ScheduleID = t.ScheduleID
WHERE s.ScheduleID = ?
GROUP BY s.ScheduleID";

$stmt = $pdo->prepare($sql);
$stmt->execute([$scheduleId]);
$result = $stmt->fetch();

$capacity = $result['Capacity'];
$bookedSeats = $result['BookedSeats'] ? explode(',', $result['BookedSeats']) : [];

// Generate available seats
$availableSeats = [];
for ($i = 1; $i <= $capacity; $i++) {
    $seatNumber = sprintf("A%03d", $i);
    if (!in_array($seatNumber, $bookedSeats)) {
        $availableSeats[] = $seatNumber;
    }
}

header('Content-Type: application/json');
echo json_encode($availableSeats); 