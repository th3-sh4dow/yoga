<?php
// Simple payment return handler
// This page receives users after payment completion from Cashfree

// Get all parameters from Cashfree
$booking_id = $_GET['order_id'] ?? $_GET['booking_id'] ?? '';
$amount = $_GET['order_amount'] ?? $_GET['amount'] ?? '';
$payment_status = $_GET['payment_status'] ?? $_GET['status'] ?? 'success';
$transaction_id = $_GET['cf_payment_id'] ?? $_GET['transaction_id'] ?? $_GET['payment_id'] ?? '';

// Log the return for debugging
error_log("Payment return: " . print_r($_GET, true));

// Redirect based on payment status
if (strtolower($payment_status) === 'success' || strtolower($payment_status) === 'paid') {
    // Success - redirect to success page with details
    $redirect_url = "payment-success.php?booking_id=" . urlencode($booking_id) . 
                   "&amount=" . urlencode($amount) . 
                   "&transaction_id=" . urlencode($transaction_id) . 
                   "&status=success";
} else {
    // Failed - redirect to failure page
    $redirect_url = "payment-failed.html?booking_id=" . urlencode($booking_id) . 
                   "&status=" . urlencode($payment_status);
}

// Redirect
header("Location: " . $redirect_url);
exit();
?>