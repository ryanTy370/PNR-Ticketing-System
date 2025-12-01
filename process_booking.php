<?php
// Start session to access user information
session_start();

// Set JSON response header
header('Content-Type: application/json');

// Enable error reporting but prevent display
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to book tickets']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "bicol_express_online_ticketing_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get form data
$userID = $_SESSION['user_id'];
$scheduleID = $_POST['schedule'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? '';
$travelDate = $_POST['travel_date'] ?? '';
$trainID = $_POST['train'] ?? '';

// Convert payment method format to match database enum format
if ($paymentMethod === 'bank-transfer') {
    $paymentMethod = 'Bank Transfer';
} elseif ($paymentMethod === 'over-the-counter') {
    $paymentMethod = 'Over-the-counter';
} elseif ($paymentMethod === 'beep-card') {
    $paymentMethod = 'Beep Card';
}

// Validate required fields
if (empty($scheduleID) || empty($paymentMethod) || empty($travelDate) || empty($trainID)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get schedule details including train information
    $scheduleQuery = "SELECT s.DepartureTime, s.ArrivalTime, r.Distance, t.FleetType,
                     r.DepartureStationID, r.ArrivalStationID, s.Fare
                     FROM Schedules s 
                     JOIN Routes r ON s.RouteID = r.RouteID 
                     JOIN Trains t ON s.TrainID = t.TrainID 
                     WHERE s.ScheduleID = ?";
    
    $stmt = $conn->prepare($scheduleQuery);
    $stmt->bind_param("i", $scheduleID);
    $stmt->execute();
    $result = $stmt->get_result();
    $scheduleData = $result->fetch_assoc();

    if (!$scheduleData) {
        throw new Exception("Invalid schedule selected");
    }

    // Get the fare from the schedule
    $amount = $scheduleData['Fare']; // Use the fare set in the schedule

    // Insert ticket
    $ticketQuery = "INSERT INTO Tickets (UserID, ScheduleID, DateTravel, TicketStatus) 
                    VALUES (?, ?, ?, 'Reserved')";
    
    $stmt = $conn->prepare($ticketQuery);
    $stmt->bind_param("iis", $userID, $scheduleID, $travelDate);
    $stmt->execute();
    $ticketID = $conn->insert_id;

    // Insert payment
    $paymentQuery = "INSERT INTO Payments (TicketID, Amount, PaymentStatus, ModeOfPayment) 
                     VALUES (?, ?, 'Pending', ?)";
    
    $stmt = $conn->prepare($paymentQuery);
    $stmt->bind_param("ids", $ticketID, $amount, $paymentMethod);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Return success response with redirect URL
    echo json_encode([
        'success' => true,
        'message' => 'Booking successful! Your ticket has been reserved.',
        'ticket_id' => $ticketID,
        'amount' => $amount,
        'redirect_url' => 'booking-history.html'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Close connection
$conn->close();
?>