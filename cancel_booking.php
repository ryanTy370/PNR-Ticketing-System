<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to cancel booking']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bicol_express_online_ticketing_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get booking ID from request
$bookingId = $_POST['booking_id'] ?? '';
$bookingId = str_replace('PNR-', '', $bookingId); // Remove PNR- prefix
$bookingId = intval($bookingId);
$userId = $_SESSION['user_id'];

// Prepare to call the stored procedure
$out_success = false;
$out_message = '';

// Call stored procedure to cancel the booking
$stmt = $conn->prepare("CALL cancelBookingProcedure(?, ?, @out_success, @out_message)");
$stmt->bind_param("ii", $bookingId, $userId);

// Execute the procedure
$stmt->execute();

// Fetch the output variables
$result = $conn->query("SELECT @out_success AS success, @out_message AS message");
$row = $result->fetch_assoc();

// Return the response from the stored procedure
echo json_encode([
    'success' => $row['success'],
    'message' => $row['message']
]);

$conn->close();
?>
