<?php
session_start();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login to view booking details');
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "bicol_express_online_ticketing_system");
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8"); // Set character set

    // Get and validate booking ID
    $bookingId = $_GET['booking_id'] ?? '';
    if (empty($bookingId)) {
        throw new Exception("Booking ID is required");
    }

    $bookingId = str_replace('PNR-', '', $bookingId);
    $bookingId = intval($bookingId);
    $userId = $_SESSION['user_id'];

    // Main booking query
    $bookingQuery = "
        SELECT 
            t.TicketID,
            t.BookDate,
            t.DateTravel,
            t.TicketStatus,
            s.DepartureTime,
            s.ArrivalTime,
            ts_dep.StationName as DepartureStation,
            l_dep.City as DepartureCity,
            l_dep.Province as DepartureProvince,
            ts_arr.StationName as ArrivalStation,
            l_arr.City as ArrivalCity,
            l_arr.Province as ArrivalProvince,
            tr.TrainName,
            tr.TrainNumber,
            tr.FleetType,
            r.Distance,
            p.Amount,
            p.PaymentStatus,
            p.ModeOfPayment,
            p.DateOfPayment,
            u.FirstName,
            u.LastName,
            u.Email,
            u.PhoneNumber
        FROM Tickets t
        JOIN Schedules s ON t.ScheduleID = s.ScheduleID
        JOIN Routes r ON s.RouteID = r.RouteID
        JOIN Trains tr ON s.TrainID = tr.TrainID
        JOIN TrainStations ts_dep ON r.DepartureStationID = ts_dep.StationID
        JOIN TrainStations ts_arr ON r.ArrivalStationID = ts_arr.StationID
        JOIN Locations l_dep ON ts_dep.LocationID = l_dep.LocationID
        JOIN Locations l_arr ON ts_arr.LocationID = l_arr.LocationID
        JOIN Users u ON t.UserID = u.UserID
        LEFT JOIN Payments p ON t.TicketID = p.TicketID
        WHERE t.TicketID = ? AND t.UserID = ?";

    $stmt = $conn->prepare($bookingQuery);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $bookingId, $userId);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Booking not found or unauthorized access");
    }

    $booking = $result->fetch_assoc();
    $stmt->close();

    // Fetch cancellation request data
    $cancellationQuery = "
        SELECT 
            RequestDate,
            CancellationReason as reason,
            Status,
            AdminResponse,
            AdminActionDate
        FROM cancellation_requests 
        WHERE TicketID = ?";
    
    $cancellationStmt = $conn->prepare($cancellationQuery);
    $cancellationData = null;

    if ($cancellationStmt) {
        $cancellationStmt->bind_param("i", $bookingId);
        if ($cancellationStmt->execute()) {
            $cancellationResult = $cancellationStmt->get_result();
            if ($cancellationResult->num_rows > 0) {
                $cancellationData = $cancellationResult->fetch_assoc();
            }
        }
        $cancellationStmt->close();
    }

    // Format the response data
    $formattedData = [
        'booking_id' => 'PNR-' . str_pad($booking['TicketID'], 6, '0', STR_PAD_LEFT),
        'passenger' => [
            'name' => $booking['FirstName'] . ' ' . $booking['LastName'],
            'email' => $booking['Email'],
            'phone' => $booking['PhoneNumber']
        ],
        'journey' => [
            'date' => date('F j, Y', strtotime($booking['DateTravel'])),
            'departure' => [
                'station' => $booking['DepartureStation'],
                'city' => $booking['DepartureCity'],
                'province' => $booking['DepartureProvince'],
                'time' => date('h:i A', strtotime($booking['DepartureTime']))
            ],
            'arrival' => [
                'station' => $booking['ArrivalStation'],
                'city' => $booking['ArrivalCity'],
                'province' => $booking['ArrivalProvince'],
                'time' => date('h:i A', strtotime($booking['ArrivalTime']))
            ]
        ],
        'distance' => $booking['Distance'], // ✅ ADD THIS LINE
        'train' => [
            'name' => $booking['TrainName'],
            'number' => $booking['TrainNumber'],
            'type' => $booking['FleetType']
        ],
        'payment' => [
            'amount' => $booking['Amount'] !== null ? number_format($booking['Amount'], 2) : '0.00',
            'status' => $booking['PaymentStatus'] ?? 'Unpaid',
            'method' => $booking['ModeOfPayment'] ?? 'Not specified',
            'date' => $booking['DateOfPayment'] 
                ? date('F j, Y h:i A', strtotime($booking['DateOfPayment'])) 
                : null
        ],
        'status' => $booking['TicketStatus'],
        'book_date' => date('F j, Y h:i A', strtotime($booking['BookDate']))
    ];

    if ($cancellationData) {
        $formattedData['cancellation'] = [
            'request_date' => date('F j, Y, g:i a', strtotime($cancellationData['RequestDate'])),
            'reason' => $cancellationData['reason'],
            'status' => $cancellationData['Status'],
            'admin_response' => $cancellationData['AdminResponse'],
            'action_date' => $cancellationData['AdminActionDate'] 
                ? date('F j, Y, g:i a', strtotime($cancellationData['AdminActionDate'])) 
                : null
        ];
    }

    echo json_encode([
        'success' => true,
        'booking' => $formattedData
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

if (isset($conn)) {
    $conn->close();
}
?>
