<?php
// Booking System Backend
// Suppress PHP warnings to prevent JSON corruption
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Load secure configuration
require_once 'config.php';

try {
    Config::validate();
    $dbConfig = Config::getDatabase();
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'Configuration error: ' . $e->getMessage()]));
}

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'create_booking':
        createBooking();
        break;
    case 'update_payment_status':
        updatePaymentStatus();
        break;
    case 'get_bookings':
        getBookings();
        break;
    case 'send_notification':
        sendNotification();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function createBooking() {
    global $pdo;
    
    // Debug: Log received data
    error_log("Received POST data: " . print_r($_POST, true));
    
    // Validate required fields
    $required_fields = ['name', 'email', 'phone', 'program', 'accommodation', 'occupancy', 'amount'];
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field $field is required. Received: " . (isset($_POST[$field]) ? $_POST[$field] : 'not set')]);
            return;
        }
    }
    
    // Generate unique booking ID
    $booking_id = 'YR' . date('Ymd') . rand(1000, 9999);
    
    // Generate payment link (using Cashfree test URL format)
    $payment_link = generatePaymentLink($_POST['amount'], $booking_id, $_POST['name'], $_POST['email']);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO bookings (
                booking_id, name, email, phone, program, accommodation, 
                occupancy, amount, payment_status, payment_link, 
                created_at, check_in_date, check_out_date, 
                special_requirements, emergency_contact
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW(), ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $booking_id,
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['program'],
            $_POST['accommodation'],
            $_POST['occupancy'],
            $_POST['amount'],
            $payment_link,
            $_POST['check_in_date'] ?? null,
            $_POST['check_out_date'] ?? null,
            $_POST['special_requirements'] ?? '',
            $_POST['emergency_contact'] ?? ''
        ]);
        
        // Email notifications disabled for now (XAMPP mail server not configured)
        // sendBookingConfirmation($_POST['email'], $booking_id, $payment_link);
        // sendOwnerNotification($booking_id, $_POST['name'], $_POST['program']);
        
        echo json_encode([
            'success' => true, 
            'booking_id' => $booking_id,
            'payment_link' => $payment_link,
            'message' => 'Booking created successfully'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updatePaymentStatus() {
    global $pdo;
    
    $booking_id = $_POST['booking_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $transaction_id = $_POST['transaction_id'] ?? '';
    
    if(empty($booking_id) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Booking ID and status are required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET payment_status = ?, transaction_id = ?, payment_date = NOW() 
            WHERE booking_id = ?
        ");
        
        $stmt->execute([$status, $transaction_id, $booking_id]);
        
        // Get booking details for notifications
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($booking) {
            if($status === 'success') {
                // Send success notification to user
                sendPaymentSuccessNotification($booking['email'], $booking_id, $booking['name']);
                
                // Send success notification to owner
                sendOwnerPaymentNotification($booking_id, $booking['name'], $booking['program'], 'success');
            } elseif($status === 'failed') {
                // Send failure notification
                sendPaymentFailureNotification($booking['email'], $booking_id, $booking['name']);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getBookings() {
    global $pdo;
    
    $status = $_GET['status'] ?? 'all';
    
    try {
        if($status === 'all') {
            $stmt = $pdo->prepare("SELECT * FROM bookings ORDER BY created_at DESC");
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE payment_status = ? ORDER BY created_at DESC");
            $stmt->execute([$status]);
        }
        
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'bookings' => $bookings]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function generatePaymentLink($amount, $booking_id, $name, $email) {
    // Using Cashfree test URL
    $base_url = "https://payments-test.cashfree.com/forms/sh4dow";
    
    // Get current domain for return URLs
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_domain = $protocol . '://' . $host;
    
    // Add parameters for tracking
    $params = [
        'amount' => $amount,
        'order_id' => $booking_id,
        'customer_name' => $name,
        'customer_email' => $email,
        'return_url' => $base_domain . '/payment-return.php',
        'notify_url' => $base_domain . '/payment-webhook.php'
    ];
    
    return $base_url . '?' . http_build_query($params);
}

function sendBookingConfirmation($email, $booking_id, $payment_link) {
    $emailConfig = Config::getEmail();
    $subject = "Booking Confirmation - Natureland YogChetna";
    $message = "
    <html>
    <body>
        <h2>Booking Confirmation</h2>
        <p>Dear Guest,</p>
        <p>Thank you for booking with Natureland YogChetna!</p>
        <p><strong>Booking ID:</strong> $booking_id</p>
        <p>To complete your booking, please make the payment using the link below:</p>
        <p><a href='$payment_link' style='background: #5ba17a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Complete Payment</a></p>
        <p>Best regards,<br>Natureland YogChetna Team</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . $emailConfig['from_email'] . "\r\n";
    
    mail($email, $subject, $message, $headers);
}

function sendOwnerNotification($booking_id, $name, $program) {
    $emailConfig = Config::getEmail();
    $owner_email = $emailConfig['owner_email'];
    $subject = "New Booking Received - $booking_id";
    $message = "
    <html>
    <body>
        <h2>New Booking Alert</h2>
        <p><strong>Booking ID:</strong> $booking_id</p>
        <p><strong>Guest Name:</strong> $name</p>
        <p><strong>Program:</strong> $program</p>
        <p><strong>Status:</strong> Payment Pending</p>
        <p>Please check the admin panel for full details.</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . $emailConfig['from_email'] . "\r\n";
    
    mail($owner_email, $subject, $message, $headers);
}

function sendPaymentSuccessNotification($email, $booking_id, $name) {
    $emailConfig = Config::getEmail();
    $subject = "Payment Successful - Booking Confirmed";
    $message = "
    <html>
    <body>
        <h2>Payment Successful!</h2>
        <p>Dear $name,</p>
        <p>Your payment has been successfully processed.</p>
        <p><strong>Booking ID:</strong> $booking_id</p>
        <p><strong>Status:</strong> Confirmed</p>
        <p>We look forward to welcoming you to Natureland YogChetna!</p>
        <p>Best regards,<br>Natureland YogChetna Team</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . $emailConfig['from_email'] . "\r\n";
    
    mail($email, $subject, $message, $headers);
}

function sendOwnerPaymentNotification($booking_id, $name, $program, $status) {
    $emailConfig = Config::getEmail();
    $owner_email = $emailConfig['owner_email'];
    $subject = "Payment Update - $booking_id";
    $message = "
    <html>
    <body>
        <h2>Payment Status Update</h2>
        <p><strong>Booking ID:</strong> $booking_id</p>
        <p><strong>Guest Name:</strong> $name</p>
        <p><strong>Program:</strong> $program</p>
        <p><strong>Payment Status:</strong> " . ucfirst($status) . "</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . $emailConfig['from_email'] . "\r\n";
    
    mail($owner_email, $subject, $message, $headers);
}

function sendPaymentFailureNotification($email, $booking_id, $name) {
    $emailConfig = Config::getEmail();
    $subject = "Payment Failed - Please Try Again";
    $message = "
    <html>
    <body>
        <h2>Payment Failed</h2>
        <p>Dear $name,</p>
        <p>Unfortunately, your payment could not be processed.</p>
        <p><strong>Booking ID:</strong> $booking_id</p>
        <p>Please try again or contact us for assistance.</p>
        <p>Best regards,<br>Natureland YogChetna Team</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . $emailConfig['from_email'] . "\r\n";
    
    mail($email, $subject, $message, $headers);
}
?>