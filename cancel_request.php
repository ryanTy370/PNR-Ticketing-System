<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to access this feature'
    ]);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Get and validate input data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['booking_id']) || !isset($data['reason'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required information'
    ]);
    exit;
}

$ticketId = $data['booking_id'];
$reason = trim($data['reason']);

// Extract numeric part if the booking ID is in the format "PNR-000099"
if (preg_match('/PNR-0*(\d+)/', $ticketId, $matches)) {
    $ticketId = $matches[1];
}

// Log the extracted ticket ID for debugging
error_log("Processing cancellation for ticket ID: " . $ticketId);

// Validate reason
if (strlen($reason) < 10) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a more detailed reason (minimum 10 characters)'
    ]);
    exit;
}

// Connect to database
require_once 'config/database.php';

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Check if ticket belongs to user and can be cancelled
    $checkTicket = $conn->prepare("
        SELECT t.TicketID, t.TicketStatus, t.DateTravel 
        FROM tickets t
        WHERE t.TicketID = ? AND t.UserID = ? 
        AND t.TicketStatus NOT IN ('Cancelled', 'Pending Cancellation')
    ");
    
    $checkTicket->execute([$ticketId, $userId]);
    
    if ($checkTicket->rowCount() === 0) {
        throw new Exception('Invalid booking or booking cannot be cancelled');
    }
    
    $ticketData = $checkTicket->fetch(PDO::FETCH_ASSOC);
    
    // Check if travel date has passed
    $travelDate = new DateTime($ticketData['DateTravel']);
    $now = new DateTime();
    
    if ($now > $travelDate) {
        throw new Exception('Cannot cancel past bookings');
    }
    
    // Update ticket status to 'Pending Cancellation'
    $updateTicket = $conn->prepare("
        UPDATE tickets 
        SET TicketStatus = 'Pending Cancellation' 
        WHERE TicketID = ?
    ");
    
    $updateTicket->execute([$ticketId]);
    
    // Insert cancellation request
    $insertRequest = $conn->prepare("
        INSERT INTO cancellation_requests 
        (TicketID, RequestDate, CancellationReason, Status) 
        VALUES (?, NOW(), ?, 'Pending')
    ");
    
    $insertRequest->execute([$ticketId, $reason]);
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Your cancellation request has been submitted and is pending approval'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    // Log the error for debugging
    error_log("Cancellation request error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 