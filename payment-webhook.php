<?php
/**
 * Simple Cashfree Webhook Handler
 * This version works without complex configuration system
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

// Simple logging function
function simple_log($message, $data = null) {
    $log_entry = date('Y-m-d H:i:s') . " - " . $message;
    if ($data) {
        $log_entry .= " - " . json_encode($data);
    }
    $log_entry .= PHP_EOL;
    file_put_contents('webhook_debug.log', $log_entry, FILE_APPEND | LOCK_EX);
}

// Handle test requests
if (isset($_GET['test']) && $_GET['test'] === '1') {
    simple_log("Test request received");
    echo json_encode([
        'status' => 'ok',
        'message' => 'Webhook endpoint is accessible',
        'timestamp' => date('c'),
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
    ]);
    exit;
}

simple_log("Webhook request started");

// Read raw payload
$rawPayload = file_get_contents('php://input');
simple_log("Raw payload received", ['length' => strlen($rawPayload), 'preview' => substr($rawPayload, 0, 200)]);

// Get headers
$headers = function_exists('getallheaders') ? getallheaders() : [];
if (empty($headers)) {
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $header = str_replace('_', '-', substr($key, 5));
            $headers[$header] = $value;
        }
    }
}

simple_log("Headers received", $headers);

// Basic database configuration (hardcoded for testing)
$db_config = [
    'host' => 'mysql.hostinger.in',
    'name' => 'u686650017_yoga_retreat',
    'user' => 'u686650017_natureyog',
    'pass' => 'Naturelandyogchetna@mydbsql0987'
];

// Skip signature verification for testing
simple_log("Skipping signature verification for testing");

// Decode payload
$webhook_data = json_decode($rawPayload, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    simple_log("JSON decode error", ['error' => json_last_error_msg()]);
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
    exit;
}

simple_log("Webhook data decoded", $webhook_data);

// Extract order information based on Cashfree Payment Form format
$webhook_type = $webhook_data['type'] ?? '';
$order_id = null;
$payment_status = '';
$amount = 0;
$transaction_id = '';

if ($webhook_type === 'PAYMENT_FORM_ORDER_WEBHOOK' && isset($webhook_data['data']['order'])) {
    $order_data = $webhook_data['data']['order'];
    $order_id = $order_data['order_id'] ?? null;
    $payment_status = strtolower($order_data['order_status'] ?? '');
    $amount = $order_data['order_amount'] ?? 0;
    $transaction_id = $order_data['transaction_id'] ?? '';
    
    simple_log("Payment Form webhook detected", [
        'order_id' => $order_id,
        'status' => $payment_status,
        'amount' => $amount,
        'transaction_id' => $transaction_id
    ]);
} else {
    // Fallback for other formats
    $order_id = $webhook_data['order_id'] ?? null;
    $payment_status = strtolower($webhook_data['payment_status'] ?? $webhook_data['status'] ?? '');
    $amount = $webhook_data['order_amount'] ?? $webhook_data['amount'] ?? 0;
    $transaction_id = $webhook_data['transaction_id'] ?? '';
    
    simple_log("Standard webhook format", [
        'order_id' => $order_id,
        'status' => $payment_status,
        'amount' => $amount
    ]);
}

if (!$order_id) {
    simple_log("Missing order_id");
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing order_id']);
    exit;
}

// Map payment status
$status_mapping = [
    'paid' => 'success',
    'success' => 'success',
    'completed' => 'success',
    'failed' => 'failed',
    'cancelled' => 'failed',
    'pending' => 'pending',
    'created' => 'pending',
];

$mapped_status = $status_mapping[$payment_status] ?? 'pending';
simple_log("Status mapped", ['original' => $payment_status, 'mapped' => $mapped_status]);

// Database operations
try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => false,
    ]);
    
    simple_log("Database connected successfully");
    
    $pdo->beginTransaction();
    
    // Check if booking exists
    $check = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE booking_id = :booking_id");
    $check->execute([':booking_id' => $order_id]);
    $booking_exists = $check->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    simple_log("Booking check", ['order_id' => $order_id, 'exists' => $booking_exists]);
    
    if ($booking_exists) {
        // Update existing booking
        $update = $pdo->prepare("
            UPDATE bookings 
            SET payment_status = :status, transaction_id = :txn, payment_date = NOW()
            WHERE booking_id = :booking_id
        ");
        $update->execute([
            ':status' => $mapped_status,
            ':txn' => $transaction_id,
            ':booking_id' => $order_id
        ]);
        
        simple_log("Booking updated", ['order_id' => $order_id, 'status' => $mapped_status]);
    } else {
        // Create a test booking record for webhook testing
        $insert_booking = $pdo->prepare("
            INSERT INTO bookings 
            (booking_id, name, email, phone, program, accommodation, occupancy, amount, payment_status, transaction_id, payment_date, created_at)
            VALUES (:booking_id, :name, :email, :phone, :program, :accommodation, :occupancy, :amount, :status, :txn, NOW(), NOW())
        ");
        
        // Extract customer details from webhook if available
        $customer_name = 'Test User';
        $customer_email = 'test@example.com';
        $customer_phone = '9999999999';
        
        if (isset($webhook_data['data']['order']['customer_details'])) {
            $customer = $webhook_data['data']['order']['customer_details'];
            $customer_name = $customer['customer_name'] ?? $customer_name;
            $customer_email = $customer['customer_email'] ?? $customer_email;
            $customer_phone = $customer['customer_phone'] ?? $customer_phone;
        }
        
        $insert_booking->execute([
            ':booking_id' => $order_id,
            ':name' => $customer_name,
            ':email' => $customer_email,
            ':phone' => $customer_phone,
            ':program' => 'Test Program',
            ':accommodation' => 'Test Accommodation',
            ':occupancy' => 'single',
            ':amount' => $amount,
            ':status' => $mapped_status,
            ':txn' => $transaction_id
        ]);
        
        simple_log("Test booking created", ['order_id' => $order_id, 'customer' => $customer_name]);
    }
    
    // Insert transaction log
    $insert_txn = $pdo->prepare("
        INSERT INTO payment_transactions
        (booking_id, transaction_id, payment_method, amount, status, gateway_response, created_at)
        VALUES (:booking_id, :txn, :method, :amount, :status, :gateway_response, NOW())
    ");
    
    $insert_txn->execute([
        ':booking_id' => $order_id,
        ':txn' => $transaction_id,
        ':method' => 'online',
        ':amount' => $amount,
        ':status' => $mapped_status,
        ':gateway_response' => json_encode($webhook_data)
    ]);
    
    simple_log("Transaction logged");
    
    $pdo->commit();
    
    // Success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Webhook processed successfully',
        'order_id' => $order_id,
        'payment_status' => $mapped_status,
        'amount' => $amount
    ]);
    
    simple_log("Webhook processed successfully", [
        'order_id' => $order_id,
        'status' => $mapped_status,
        'amount' => $amount
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    simple_log("Database error", ['error' => $e->getMessage()]);
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    simple_log("General error", ['error' => $e->getMessage()]);
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Processing error',
        'error' => $e->getMessage()
    ]);
}

simple_log("Webhook request completed");
?>