<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to view dashboard']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bicol_express_online_ticketing_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]);
    exit;
}

$userID = $_SESSION['user_id'];

try {
    // Get user's name
    $userQuery = "SELECT FirstName, MiddleInitial, LastName FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $userData = $userResult->fetch_assoc();
    
    // Get active bookings count (Reserved or Confirmed status)
    $activeBookingsQuery = "SELECT COUNT(*) as active_count 
                           FROM Tickets 
                           WHERE UserID = ? 
                           AND TicketStatus IN ('Reserved', 'Confirmed')";
    $stmt = $conn->prepare($activeBookingsQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $activeResult = $stmt->get_result();
    $activeCount = $activeResult->fetch_assoc()['active_count'];

    // Get total trips count (all bookings except cancelled)
    $totalTripsQuery = "SELECT COUNT(*) as total_count 
                       FROM Tickets 
                       WHERE UserID = ? 
                       AND TicketStatus != 'Cancelled'";
    $stmt = $conn->prepare($totalTripsQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $totalCount = $totalResult->fetch_assoc()['total_count'];

    // Get recent bookings
    $recentBookingsQuery = "
        SELECT 
            t.TicketID,
            t.BookDate,
            t.DateTravel,
            t.TicketStatus,
            s.DepartureTime,
            s.ArrivalTime,
            ts_dep.StationName as DepartureStation,
            ts_arr.StationName as ArrivalStation,
            p.Amount,
            p.PaymentStatus,
            p.ModeOfPayment,
            tr.TrainName,
            tr.TrainNumber
        FROM Tickets t
        JOIN Schedules s ON t.ScheduleID = s.ScheduleID
        JOIN Routes r ON s.RouteID = r.RouteID
        JOIN TrainStations ts_dep ON r.DepartureStationID = ts_dep.StationID
        JOIN TrainStations ts_arr ON r.ArrivalStationID = ts_arr.StationID
        JOIN Trains tr ON s.TrainID = tr.TrainID
        LEFT JOIN Payments p ON t.TicketID = p.TicketID
        WHERE t.UserID = ?
        ORDER BY t.BookDate DESC
        LIMIT 5";
    
    $stmt = $conn->prepare($recentBookingsQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $recentResult = $stmt->get_result();
    
    $recentBookings = [];
    while ($row = $recentResult->fetch_assoc()) {
        // Combine the date from DateTravel with the time from DepartureTime
        // This ensures we're using the correct date and time for the booking
        $departureDateTime = new DateTime($row['DateTravel']);
        $scheduledTime = new DateTime($row['DepartureTime']);
        
        // Extract just the time from the scheduled departure
        $timeFormat = $scheduledTime->format('H:i:s');
        
        // Set this time to the departure date
        $departureDateTime->setTime(
            intval(substr($timeFormat, 0, 2)),  // Hours
            intval(substr($timeFormat, 3, 2)),  // Minutes
            intval(substr($timeFormat, 6, 2))   // Seconds
        );
        
        // Determine status display
        $status = $row['TicketStatus'];
        
        // For Reserved tickets, check payment method
        if ($status === 'Reserved') {
            if ($row['ModeOfPayment'] !== 'Gcash' && $row['ModeOfPayment'] !== 'Bank Transfer') {
                $status = 'Pending';
            }
        }
        
        $recentBookings[] = [
            'booking_id' => 'PNR-' . str_pad($row['TicketID'], 6, '0', STR_PAD_LEFT),
            'route' => $row['DepartureStation'] . ' - ' . $row['ArrivalStation'],
            'date' => $departureDateTime->format('F j, Y'),
            'time' => $departureDateTime->format('h:i A'),
            'status' => $status,
            'payment_status' => $row['PaymentStatus'],
            'amount' => $row['Amount'],
            'train' => $row['TrainName'] . ' (' . $row['TrainNumber'] . ')'
        ];
    }

    // Return all data
    echo json_encode([
        'success' => true,
        'user_name' => $userData['FirstName'] . 
                      ($userData['MiddleInitial'] ? ' ' . $userData['MiddleInitial'] . '. ' : ' ') . 
                      $userData['LastName'],
        'active_bookings' => $activeCount,
        'total_trips' => $totalCount,
        'recent_bookings' => $recentBookings
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 