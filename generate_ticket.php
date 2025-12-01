<?php
session_start();

// Set the default timezone to Manila (Philippines)
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to download ticket']);
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
$bookingId = $_GET['booking_id'] ?? '';
$bookingId = str_replace('PNR-', '', $bookingId); // Remove PNR- prefix
$bookingId = intval($bookingId);
$userId = $_SESSION['user_id'];

try {
    // Fetch ticket information
    $query = "
        SELECT 
            t.TicketID,
            t.DateTravel,
            t.TicketStatus,
            s.DepartureTime,
            s.ArrivalTime,
            ts_dep.StationName as DepartureStation,
            l_dep.City as DepartureCity,
            ts_arr.StationName as ArrivalStation,
            l_arr.City as ArrivalCity,
            tr.TrainName,
            tr.TrainNumber,
            tr.FleetType,
            p.Amount,
            p.PaymentStatus,
            u.FirstName,
            u.MiddleInitial,
            u.LastName
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

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $bookingId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Ticket not found or unauthorized access");
    }

    $ticket = $result->fetch_assoc();

    // Check if ticket can be downloaded
    if ($ticket['TicketStatus'] === 'Cancelled') {
        throw new Exception("Cannot download cancelled ticket");
    }

    // Calculate journey duration in minutes
    $departureTime = strtotime($ticket['DepartureTime']);
    $arrivalTime = strtotime($ticket['ArrivalTime']);
    $durationMinutes = ($arrivalTime - $departureTime) / 60;
    $durationHours = floor($durationMinutes / 60);
    $durationMins = $durationMinutes % 60;
    $durationFormatted = $durationHours . 'h ' . $durationMins . 'm';

    // Get current date and time for ticket generation timestamp
    $generatedDateTime = date('F j, Y h:i A'); // Full date and time (e.g., March 23, 2025 3:24 PM)

    $ticketHtml = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PNR Ticket #' . str_pad($ticket['TicketID'], 6, '0', STR_PAD_LEFT) . '</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
          <style>
            @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");
            
            :root {
                --primary-color: #00adb5;
                --primary-dark: #008a91;
                --secondary-color: #393e46;
                --text-dark: #222831;
                --text-light: #eeeeee;
                --text-gray: #777;
                --bg-light: #f8f9fa;
                --bg-white: #ffffff;
                --border-color: #ddd;
                --success-color: #1db954;
                --success-bg: rgba(29, 185, 84, 0.1);
                --warning-color: #f1c40f;
                --warning-bg: rgba(241, 196, 15, 0.1);
                --danger-color: #e74c3c;
                --danger-bg: rgba(231, 76, 60, 0.1);
                --border-radius-sm: 4px;
                --border-radius-md: 8px;
                --border-radius-lg: 16px;
                --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
                --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
                --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            }
            
            .dark-mode {
                --primary-color: #00adb5;
                --primary-dark: #00c2cc;
                --secondary-color: #393e46;
                --text-dark: #eeeeee;
                --text-light: #222831;
                --text-gray: #aaa;
                --bg-light: #222831;
                --bg-white: #393e46;
                --border-color: #555;
                --success-color: #1db954;
                --success-bg: rgba(29, 185, 84, 0.15);
                --warning-color: #f1c40f;
                --warning-bg: rgba(241, 196, 15, 0.15);
                --danger-color: #e74c3c;
                --danger-bg: rgba(231, 76, 60, 0.15);
                --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.2);
                --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.3);
                --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.3);
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: "Poppins", sans-serif;
            }
            
            body {
                background-color: var(--bg-light);
                color: var(--text-dark);
                padding: 40px 20px;
                transition: all 0.3s ease;
            }
            
            .ticket-container {
                max-width: 1200px; /* Increased from 900px to give more space */
                margin: 0 auto;
                background: var(--bg-white);
                border-radius: var(--border-radius-lg);
                overflow: hidden;
                box-shadow: var(--shadow-lg);
                transition: all 0.3s ease;
            }
            
            .ticket-header {
                background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
                color: white;
                padding: 20px 30px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-top-left-radius: var(--border-radius-lg);
                border-top-right-radius: var(--border-radius-lg);
                margin: -30px -30px 30px -30px;
            }
            
            .ticket-logo {
                display: flex;
                align-items: center;
                gap: 10px;
                font-size: 24px;
                font-weight: 600;
            }
            
            .ticket-logo i {
                font-size: 28px;
            }
            
            .ticket-id {
                font-size: 14px;
                opacity: 0.9;
            }
            
            .ticket-body {
                display: flex;
                flex-direction: row;
            }
            
            .ticket-main {
                flex: 1;
                padding: 30px;
                border-right: 2px dashed var(--border-color);
                position: relative;
            }
            
            .ticket-sidebar {
                width: 300px; /* Increased from 250px for better spacing */
                min-width: 300px; /* Prevent shrinking */
                padding: 30px;
                background: var(--bg-light);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            
            /* Sidebar ticket header styling - matches main header visually */
            .ticket-sidebar .ticket-header {
                margin: -30px -30px 30px -30px;
                border-top-right-radius: var(--border-radius-lg);
                border-bottom-right-radius: 0;
                border-bottom-left-radius: 0;
            }
            
            /* Custom styling for the sidebar header version */
            .ticket-sidebar .ticket-logo {
                width: 100%;
                justify-content: center;
            }
            
            /* Specific adjustment for the sidebar logo (no icon) */
            .ticket-sidebar .ticket-logo span {
                font-size: 24px;
            }
            
            .journey-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
                margin-bottom: 30px;
            }
            
            .stations {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 30px;
                margin-bottom: 30px;
                background: var(--bg-light);
                border-radius: var(--border-radius-md);
                padding: 30px;
                box-shadow: var(--shadow-sm);
                position: relative;
                overflow: hidden;
            }
            
            .stations::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 5px;
                height: 100%;
                background: linear-gradient(to bottom, var(--primary-color), var(--primary-dark));
            }
            
            .stations::after {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 1px;
                height: 70%;
                background: rgba(0, 173, 181, 0.2);
                border-radius: 1px;
            }
            
            .station-details {
                flex: 1;
                position: relative;
                padding: 20px;
                border-radius: var(--border-radius-sm);
                transition: all 0.3s ease;
                position: relative;
                width: 50%;
                min-width: 0;
            }
            
            .station-details > div {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .station-details:first-child::after {
                content: "DEPARTURE";
                position: absolute;
                top: -12px;
                left: 20px;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 1px;
                color: var(--primary-color);
                background: var(--bg-light);
                padding: 3px 8px;
                border-radius: 4px;
                box-shadow: var(--shadow-sm);
                border: 1px solid rgba(0, 173, 181, 0.2);
            }
            
            .station-details:last-child::after {
                content: "ARRIVAL";
                position: absolute;
                top: -12px;
                left: 20px;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 1px;
                color: var(--primary-dark);
                background: var(--bg-light);
                padding: 3px 8px;
                border-radius: 4px;
                box-shadow: var(--shadow-sm);
                border: 1px solid rgba(0, 173, 181, 0.2);
            }
            
            .station-details:first-child {
                background: rgba(0, 173, 181, 0.05);
                border-top-left-radius: 12px;
                border-bottom-left-radius: 12px;
            }
            
            .station-details:last-child {
                background: rgba(0, 173, 181, 0.1);
                border-top-right-radius: 12px;
                border-bottom-right-radius: 12px;
            }
            
            .station-route {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: space-between;
                position: relative;
                padding: 15px 0;
                height: 140px;
                margin: 0 25px;
            }
            
            @keyframes pulse {
                0% { opacity: 0.6; }
                50% { opacity: 1; }
                100% { opacity: 0.6; }
            }
            
            .station-route::before {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                width: 4px;
                height: 80%;
                background: linear-gradient(to bottom, var(--primary-color) 0%, var(--primary-dark) 100%);
                transform: translate(-50%, -50%);
                z-index: 0;
                border-radius: 4px;
                animation: pulse 2s infinite;
            }
            
            .station-icon {
                width: 45px;
                height: 45px;
                border-radius: 50%;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                z-index: 1;
                box-shadow: 0 0 0 5px var(--bg-light), 0 0 0 7px rgba(0, 173, 181, 0.2);
                transition: all 0.3s ease;
            }
            
            .station-icon:first-child {
                background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            }
            
            .station-icon:last-child {
                background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            }
            
            .station-icon i {
                font-size: 16px;
                transition: all 0.3s ease;
            }
            
            .stations .station-route .station-icon:first-child i:before {
                content: "\\f3c5"; /* fa-map-marker-alt */
            }
            
            .stations .station-route .station-icon:last-child i:before {
                content: "\\f041"; /* fa-map-pin */
            }
            
            .station-icon:hover {
                transform: scale(1.1);
                box-shadow: 0 0 0 5px var(--bg-light), 0 0 0 7px rgba(0, 173, 181, 0.4);
            }
            
            .time {
                font-size: 24px;
                font-weight: 700;
                color: var(--text-dark);
                margin-bottom: 5px;
                letter-spacing: -0.5px;
                min-width: 110px;
                display: inline-block;
            }
            
            .date {
                font-size: 14px;
                color: var(--text-gray);
                margin-bottom: 18px;
                display: inline-block;
                padding: 2px 10px;
                border-radius: 12px;
                background: rgba(0, 0, 0, 0.04);
            }
            
            .station-name {
                font-size: 18px;
                font-weight: 600;
                color: var(--text-dark);
                margin-bottom: 8px;
                display: flex;
                align-items: center;
                line-height: 1.3;
            }
            
            .station-details:first-child .station-name::before {
                content: "\\f3c5";
                font-family: "Font Awesome 6 Free";
                font-weight: 900;
                margin-right: 8px;
                color: var(--primary-color);
                font-size: 14px;
            }
            
            .station-details:last-child .station-name::before {
                content: "\\f041";
                font-family: "Font Awesome 6 Free";
                font-weight: 900;
                margin-right: 8px;
                color: var(--primary-color);
                font-size: 14px;
            }
            
            .city {
                font-size: 14px;
                color: var(--text-gray);
                padding-left: 22px;
            }
            
            .travel-details {
                background: var(--bg-light);
                border-radius: var(--border-radius-md);
                padding: 20px;
                margin-bottom: 30px;
                box-shadow: var(--shadow-sm);
            }
            
            .travel-detail {
                display: flex;
                justify-content: space-between;
                margin-bottom: 15px;
                font-size: 14px;
            }
            
            .travel-detail:last-child {
                margin-bottom: 0;
            }
            
            .detail-label {
                color: var(--text-gray);
            }
            
            .detail-value {
                font-weight: 500;
                color: var(--text-dark);
            }
            
            .passenger-details {
                margin-bottom: 30px;
                background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
                border-radius: var(--border-radius-md);
                padding: 20px;
                color: white;
                position: relative;
                overflow: hidden;
                box-shadow: var(--shadow-md);
            }
             .passenger-details1 {
                margin-bottom: 30px;
                background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
                border-radius: var(--border-radius-md);
                padding: 20px;
                color: white;
                position: relative;
                overflow: hidden;
                box-shadow: var(--shadow-md);
                margin-top: 20px;
            }
            
            .passenger-details h3 {
                font-size: 16px;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 12px;
                opacity: 0.9;
            }
            
            .passenger-name {
                font-size: 22px;
                font-weight: 600;
                margin-bottom: 10px;
                color: white;
            }
            .ticket-status {
                display: inline-flex;
                align-items: center;
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 500;
                text-transform: uppercase;
                margin-top: 5px;
            }
            
            .status-confirmed {
                background: var(--success-bg);
                color: var(--success-color);
            }
            
            .status-reserved {
                background: var(--warning-bg);
                color: var(--warning-color);
            }
            
            .status-pending {
                background: var(--warning-bg);
                color: var(--warning-color);
            }
            
            .status-cancelled {
                background: var(--danger-bg);
                color: var(--danger-color);
            }
            
            .qr-section {
                text-align: center;
                margin-top: auto;
            }
            
            .qr-code {
                width: 150px;
                height: 150px;
                margin: 0 auto;
                background: white;
                padding: 5px;
                border-radius: var(--border-radius-sm);
                box-shadow: var(--shadow-sm);
            }
            
            .qr-code img {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }
            
            .qr-label {
                font-size: 12px;
                color: var(--text-gray);
                margin-top: 10px;
            }
            
            .price-section {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .price-label {
                font-size: 13px;
                color: var(--text-gray);
                margin-bottom: 5px;
            }
            
            .price-value {
                font-size: 28px;
                font-weight: 700;
                color: var(--primary-color);
            }
            
            .ticket-footer {
                padding: 20px 30px;
                background: var(--bg-light);
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 12px;
                color: var(--text-gray);
                margin: 30px -30px -30px -30px;
                border-bottom-left-radius: var(--border-radius-lg);
            }
            
            .generation-time {
                font-style: italic;
            }
            
            .mode-toggle {
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--bg-white);
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: var(--shadow-md);
                color: var(--text-dark);
                transition: all 0.3s ease;
            }
            
            .mode-toggle:hover {
                transform: scale(1.1);
            }
            
            .ticket-notice {
                font-size: 12px;
                color: var(--text-gray);
                margin-top: 20px;
                line-height: 1.6;
            }
            
            .validation-mark {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 10px;
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid var(--border-color);
                font-size: 12px;
                color: var(--text-gray);
            }
            
            @media (max-width: 768px) {
                .ticket-body {
                    flex-direction: column;
                }
                
                .ticket-main {
                    border-right: none;
                    border-bottom: 2px dashed var(--border-color);
                }
                
                .ticket-sidebar {
                    width: 100%;
                    min-width: 100%;
                }
                
                .journey-details {
                    grid-template-columns: 1fr;
                    gap: 15px;
                }
                
                .stations {
                    flex-direction: column;
                    padding: 25px 20px;
                    gap: 40px;
                }
                
                .stations::before {
                    width: 100%;
                    height: 5px;
                    background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
                }
                
                .station-details:first-child::after,
                .station-details:last-child::after {
                    top: -10px;
                    left: 50%;
                    transform: translateX(-50%);
                }
                
                .station-details:first-child,
                .station-details:last-child {
                    border-radius: 12px;
                    text-align: center;
                    padding: 15px;
                    width: 100%;
                    max-width: 100%;
                }
                
                .city {
                    padding-left: 0;
                }
                
                .station-details .station-name::before {
                    display: none;
                }
                
                .time {
                    min-width: 0;
                }
                
                .station-details .station-name {
                    justify-content: center;
                }
                
                .ticket-header, 
                .ticket-footer {
                    margin-left: -20px;
                    margin-right: -20px;
                    padding-left: 20px;
                    padding-right: 20px;
                }
                
                .ticket-header {
                    margin-top: -20px;
                }
                
                .ticket-footer {
                    margin-bottom: -20px;
                }
            }
            
            @media print {
                body {
                    padding: 0;
                    background: white;
                }
                
                .ticket-container {
                    box-shadow: none;
                    border: 1px solid #ddd;
                    width: 100%;
                    max-width: none;
                }
                
                .mode-toggle {
                    display: none;
                }
                
                @page {
                    margin: 0.5cm;
                }
            }
                /* Sidebar adjustments */
                .ticket-sidebar .ticket-header {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    padding: 10px 20px;
                }

                .ticket-sidebar .ticket-logo span {
                    font-size: 24px;
                    font-weight: 600;
                }
                .passenger-details1 {
                    margin-top: 30;
                    margin-bottom: 20px;
                }

                .price-section {
                    margin-top: auto;
                    margin-bottom: 100px;
                }

                .payment-status {
                    margin-top: 20px;
                    margin-bottom: 20px;
                }
        </style>
    </head>
    <body>
        <div class="ticket-container">
            <div class="ticket-body">
                <div class="ticket-main">
                    <div class="ticket-header">
                        <div class="ticket-logo">
                            <img src="logo-white.png" alt="Train Logo" style="width: 40px; height: 40px;">
                            <span>Bicol Express</span>
                        </div>
                        <div class="ticket-id">
                            Booking #PNR-' . str_pad($ticket['TicketID'], 6, '0', STR_PAD_LEFT) . '
                        </div>
                    </div>
                    
                    <div class="passenger-details">
                        <h3>Passenger Information</h3>
                        <div class="passenger-name">' . $ticket['FirstName'] . ' ' . (!empty($ticket['MiddleInitial']) ? $ticket['MiddleInitial'] . '. ' : '') . $ticket['LastName'] . '</div>
                        <div class="ticket-status status-' . strtolower($ticket['TicketStatus']) . '">
                            <i class="fas fa-check-circle"></i> ' . $ticket['TicketStatus'] . '
                        </div>
                    </div>
                    
                    <div class="stations">
                        <div class="station-details">
                            <div class="time">' . date('h:i A', strtotime($ticket['DepartureTime'])) . '</div>
                            <div class="date">' . date('D, M j, Y', strtotime($ticket['DateTravel'])) . '</div>
                            <div class="station-name">' . $ticket['DepartureStation'] . '</div>
                            <div class="city">' . $ticket['DepartureCity'] . '</div>
                        </div>
                        <div class="qr-section">
                            <div class="qr-code">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=PNR-' . str_pad($ticket['TicketID'], 6, '0', STR_PAD_LEFT) . '" 
                                     alt="Ticket QR Code">
                            </div>
                            <div class="qr-label">Scan for verification</div>
                        </div>
                        <div class="station-details">
                            <div class="time">' . date('h:i A', strtotime($ticket['ArrivalTime'])) . '</div>
                            <div class="date">' . date('D, M j, Y', strtotime($ticket['DateTravel'])) . '</div>
                            <div class="station-name">' . $ticket['ArrivalStation'] . '</div>
                            <div class="city">' . $ticket['ArrivalCity'] . '</div>
                        </div>
                    </div>
                    
                    <div class="travel-details">
                        <div class="travel-detail">
                            <span class="detail-label">Train</span>
                            <span class="detail-value">' . $ticket['TrainName'] . ' (' . $ticket['TrainNumber'] . ')</span>
                        </div>
                        <div class="travel-detail">
                            <span class="detail-label">Class</span>
                            <span class="detail-value">' . $ticket['FleetType'] . '</span>
                        </div>
                        <div class="travel-detail">
                            <span class="detail-label">Duration</span>
                            <span class="detail-value">' . $durationFormatted . '</span>
                        </div>
                    </div>
                    
                    <div class="ticket-notice">
                        <p><strong>Important:</strong> Please arrive at the station at least 30 minutes before departure time.</p>
                        <p>Present this ticket along with a valid ID during your journey for verification.</p>
                    </div>
                    
                    <div class="validation-mark">
                        <i class="fas fa-shield-alt"></i>
                        <span>This is an electronically verified ticket. No signature required.</span>
                    </div>
                    
                    <div class="ticket-footer">
                        <div>Bicol Express © ' . date('Y') . '</div>
                        <div class="generation-time">Generated: ' . $generatedDateTime . '</div>
                    </div>
                </div>
                
                <div class="ticket-sidebar">
                    <!-- Sidebar ticket header - matches main design but without icon -->
                    <div class="ticket-header">
                        <div class="ticket-logo">
                            <span>Bicol Express</span>
                        </div>
                    </div>
                    <div class="passenger-details1">
                        <h3>Passenger Information</h3>
                        <div class="passenger-name1">' . $ticket['FirstName'] . ' ' . (!empty($ticket['MiddleInitial']) ? $ticket['MiddleInitial'] . '. ' : '') . $ticket['LastName'] . '</div>
                        <div class="ticket-status status-' . strtolower($ticket['TicketStatus']) . '">
                            <i class="fas fa-check-circle"></i> ' . $ticket['TicketStatus'] . '
                        </div>
                    </div>
                    <div>
                        <div class="detail-label" style="margin-top: 20px">Payment Status</div>
                        <div class="detail-value" style="margin-top: 10px; font-weight: 600;">' . $ticket['PaymentStatus'] . '</div>
                    </div>
                    
                    <div class="price-section">
                        <div class="price-label">Total Fare</div>
                        <div class="price-value">₱' . number_format($ticket['Amount'], 2) . '</div>
                    </div>
                </div>
            </div>
        </div>
        
        <button class="mode-toggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
            <i class="fas fa-moon"></i>
        </button>
        
        <script>
            function toggleDarkMode() {
                document.body.classList.toggle("dark-mode");
                
                const icon = document.querySelector(".mode-toggle i");
                if (document.body.classList.contains("dark-mode")) {
                    icon.classList.remove("fa-moon");
                    icon.classList.add("fa-sun");
                } else {
                    icon.classList.remove("fa-sun");
                    icon.classList.add("fa-moon");
                }
            }
            
            // Check if user prefers dark mode
            if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
                toggleDarkMode();
            }
            
            // Optional: Check localStorage for saved preference
            if (localStorage.getItem("darkMode") === "true") {
                if (!document.body.classList.contains("dark-mode")) {
                    toggleDarkMode();
                }
            }
        </script>
    </body>
    </html>';

    // Set headers for HTML download
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="PNR-Ticket-' . str_pad($ticket['TicketID'], 6, '0', STR_PAD_LEFT) . '.html"');

    // Output the ticket
    echo $ticketHtml;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>