<?php
// Payment Webhook Handler for Cashfree
// This file handles payment status updates from Cashfree

header('Content-Type: application/json');

// Log all incoming requests for debugging
$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'post_data' => $_POST,
    'raw_input' => file_get_contents('php://input')
];

file_put_contents('payment_webhook_log.txt', json_encode($log_data) . "\n", FILE_APPEND);

// Database configuration
$host = 'localhost';
$dbname = 'yoga_retreat_bookings';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Get webhook data
$webhook_data = json_decode(file_get_contents('php://input'), true);

// If JSON data is not available, try POST data
if (!$webhook_data) {
    $webhook_data = $_POST;
}

// Validate required fields
if (!isset($webhook_data['order_id']) || !isset($webhook_data['payment_status'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$booking_id = $webhook_data['order_id'];
$payment_status = strtolower($webhook_data['payment_status']);
$transaction_id = $webhook_data['transaction_id'] ?? $webhook_data['cf_payment_id'] ?? '';
$payment_method = $webhook_data['payment_method'] ?? '';
$gateway_response = json_encode($webhook_data);

// Map Cashfree status to our status
$status_mapping = [
    'success' => 'success',
    'paid' => 'success',
    'completed' => 'success',
    'failed' => 'failed',
    'cancelled' => 'failed',
    'pending' => 'pending'
];

$mapped_status = $status_mapping[$payment_status] ?? 'pending';

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Update booking status
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET payment_status = ?, transaction_id = ?, payment_date = NOW() 
        WHERE booking_id = ?
    ");
    $stmt->execute([$mapped_status, $transaction_id, $booking_id]);
    
    // Insert payment transaction record
    $stmt = $pdo->prepare("
        INSERT INTO payment_transactions 
        (booking_id, transaction_id, payment_method, amount, status, gateway_response) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $amount = $webhook_data['order_amount'] ?? $webhook_data['amount'] ?? 0;
    $stmt->execute([$booking_id, $transaction_id, $payment_method, $amount, $mapped_status, $gateway_response]);
    
    // Get booking details for notifications
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($booking) {
        if ($mapped_status === 'success') {
            // Send success notifications
            sendPaymentSuccessNotification($booking['email'], $booking_id, $booking['name'], $booking['program']);
            sendOwnerPaymentNotification($booking_id, $booking['name'], $booking['program'], 'success', $amount);
            
            // Log notification
            $stmt = $pdo->prepare("
                INSERT INTO notifications (booking_id, type, recipient_email, subject, message) 
                VALUES (?, 'payment_success', ?, 'Payment Successful', 'Payment completed successfully')
            ");
            $stmt->execute([$booking_id, $booking['email']]);
            
        } elseif ($mapped_status === 'failed') {
            // Send failure notifications
            sendPaymentFailureNotification($booking['email'], $booking_id, $booking['name']);
            sendOwnerPaymentNotification($booking_id, $booking['name'], $booking['program'], 'failed', $amount);
            
            // Log notification
            $stmt = $pdo->prepare("
                INSERT INTO notifications (booking_id, type, recipient_email, subject, message) 
                VALUES (?, 'payment_failed', ?, 'Payment Failed', 'Payment could not be processed')
            ");
            $stmt->execute([$booking_id, $booking['email']]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Return success response to Cashfree
    echo json_encode(['status' => 'success', 'message' => 'Webhook processed successfully']);
    
} catch (Exception $e) {
    // Rollback transaction
    $pdo->rollback();
    
    // Log error
    error_log("Webhook processing error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Processing failed']);
}

function sendPaymentSuccessNotification($email, $booking_id, $name, $program) {
    $subject = "Payment Successful - Booking Confirmed | Natureland YogChetna";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #5ba17a 0%, #4a9268 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .success-icon { font-size: 48px; color: #28a745; margin-bottom: 20px; }
            .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #5ba17a; }
            .footer { text-align: center; margin-top: 30px; color: #6c757d; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Payment Successful!</h1>
                <p>Your retreat booking is confirmed</p>
            </div>
            <div class='content'>
                <div style='text-align: center;'>
                    <div class='success-icon'>✅</div>
                </div>
                
                <p>Dear $name,</p>
                
                <p>Congratulations! Your payment has been successfully processed and your booking is now confirmed.</p>
                
                <div class='booking-details'>
                    <h3>Booking Details</h3>
                    <p><strong>Booking ID:</strong> $booking_id</p>
                    <p><strong>Program:</strong> $program</p>
                    <p><strong>Status:</strong> <span style='color: #28a745; font-weight: bold;'>CONFIRMED</span></p>
                </div>
                
                <p>We are excited to welcome you to Natureland YogChetna for your wellness journey!</p>
                
                <h4>What's Next?</h4>
                <ul>
                    <li>You will receive a detailed itinerary via email within 24 hours</li>
                    <li>Our team will contact you 2-3 days before your arrival</li>
                    <li>Please bring comfortable yoga clothes and personal items</li>
                </ul>
                
                <p>If you have any questions, feel free to contact us at +91-6203517866 or reply to this email.</p>
                
                <div class='footer'>
                    <p>With gratitude and excitement,<br>
                    <strong>Natureland YogChetna Team</strong><br>
                    Village Cholagora, Jamshedpur–Galudih Road<br>
                    Phone: +91-6203517866 | Email: naturelandyogchetna@gmail.com</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Natureland YogChetna <naturelandyogchetna@gmail.com>' . "\r\n";
    
    mail($email, $subject, $message, $headers);
}

function sendOwnerPaymentNotification($booking_id, $name, $program, $status, $amount) {
    $owner_email = "naturelandyogchetna@gmail.com";
    $subject = "Payment Update - Booking $booking_id";
    
    $status_color = $status === 'success' ? '#28a745' : '#dc3545';
    $status_text = $status === 'success' ? 'SUCCESSFUL' : 'FAILED';
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #343a40; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            .booking-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Payment Status Update</h2>
            </div>
            <div class='content'>
                <div class='booking-details'>
                    <h3>Booking Information</h3>
                    <p><strong>Booking ID:</strong> $booking_id</p>
                    <p><strong>Guest Name:</strong> $name</p>
                    <p><strong>Program:</strong> $program</p>
                    <p><strong>Amount:</strong> ₹" . number_format($amount) . "</p>
                    <p><strong>Payment Status:</strong> <span style='color: $status_color; font-weight: bold;'>$status_text</span></p>
                    <p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>
                </div>
                
                <p>Please check the admin panel for complete booking details.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Booking System <system@naturelandyogchetna.com>' . "\r\n";
    
    mail($owner_email, $subject, $message, $headers);
}

function sendPaymentFailureNotification($email, $booking_id, $name) {
    $subject = "Payment Failed - Please Try Again | Natureland YogChetna";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .retry-button { background: #5ba17a; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>❌ Payment Failed</h1>
                <p>We couldn't process your payment</p>
            </div>
            <div class='content'>
                <p>Dear $name,</p>
                
                <p>Unfortunately, we were unable to process your payment for booking ID: <strong>$booking_id</strong></p>
                
                <p>This could be due to:</p>
                <ul>
                    <li>Insufficient funds in your account</li>
                    <li>Network connectivity issues</li>
                    <li>Bank security restrictions</li>
                    <li>Incorrect payment details</li>
                </ul>
                
                <p>Don't worry! Your booking is still reserved for the next 24 hours.</p>
                
                <p><strong>What you can do:</strong></p>
                <ul>
                    <li>Try the payment again with a different card</li>
                    <li>Contact your bank to ensure online payments are enabled</li>
                    <li>Call us at +91-6203517866 for assistance</li>
                </ul>
                
                <p>We're here to help you complete your booking successfully!</p>
                
                <p>Best regards,<br>
                <strong>Natureland YogChetna Team</strong><br>
                Phone: +91-6203517866 | Email: naturelandyogchetna@gmail.com</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Natureland YogChetna <naturelandyogchetna@gmail.com>' . "\r\n";
    
    mail($email, $subject, $message, $headers);
}
?>