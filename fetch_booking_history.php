<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to view booking history']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bicol_express_online_ticketing_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]);
    exit;
}

$userID = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Base query using the created view
$query = "SELECT * FROM bookingDetails WHERE UserID = ?";

// Apply filters
if ($filter !== 'all') {
    switch ($filter) {
        case 'upcoming':
            $query .= " AND DateTravel >= CURDATE() AND TicketStatus != 'Cancelled'";
            break;
        case 'completed':
            $query .= " AND DateTravel < CURDATE() AND TicketStatus = 'Confirmed'";
            break;
        case 'cancelled':
            $query .= " AND TicketStatus = 'Cancelled'";
            break;
    }
}

// Apply search filter if provided
if (!empty($search)) {
    $query .= " AND (
        DepartureStation LIKE ? OR 
        ArrivalStation LIKE ? OR 
        TrainName LIKE ? OR 
        CONCAT('PNR-', LPAD(TicketID, 6, '0')) LIKE ? 
    )";
}

$query .= " ORDER BY BookDate DESC";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("issss", $userID, $searchParam, $searchParam, $searchParam, $searchParam);
} else {
    $stmt->bind_param("i", $userID);
}

$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $status = $row['TicketStatus'];
    $statusClass = '';

    if ($status === 'Confirmed') {
        $statusClass = (strtotime($row['DateTravel']) < strtotime('today')) ? 'completed' : 'upcoming';
    } elseif ($status === 'Reserved') {
        if (in_array($row['ModeOfPayment'], ['Gcash', 'Bank Transfer', 'BeepCard'])) {
            $updateQuery = "UPDATE Payments SET PaymentStatus = 'Completed' WHERE TicketID = ?";
            $stmtUpdate = $conn->prepare($updateQuery);
            $stmtUpdate->bind_param("i", $row['TicketID']);
            $stmtUpdate->execute();
            $statusClass = 'upcoming';
        } else {
            $status = 'Pending';
            $statusClass = 'upcoming';
        }
    } else {
        $statusClass = 'cancelled';
    }

    $bookings[] = [
        'booking_id' => 'PNR-' . str_pad($row['TicketID'], 6, '0', STR_PAD_LEFT),
        'book_date' => date('M j, Y', strtotime($row['BookDate'])),
        'travel_date' => date('M j, Y', strtotime($row['DateTravel'])),
        'departure_time' => date('h:i A', strtotime($row['DepartureTime'])),
        'arrival_time' => date('h:i A', strtotime($row['ArrivalTime'])),
        'from_station' => $row['DepartureStation'],
        'to_station' => $row['ArrivalStation'],
        'train_name' => $row['TrainName'],
        'train_number' => $row['TrainNumber'],
        'fleet_type' => $row['FleetType'],
        'amount' => $row['Amount'],
        'payment_status' => $row['PaymentStatus'],
        'payment_method' => $row['ModeOfPayment'],
        'status' => $status,
        'status_class' => $statusClass
    ];
}

echo json_encode([
    'success' => true,
    'bookings' => $bookings
]);

$conn->close();
?>