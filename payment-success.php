<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Natureland YogChetna</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .success-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 30px;
            animation: bounce 1s ease-in-out;
        }
        .success-title {
            color: #5ba17a;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .booking-details {
            background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            border-left: 5px solid #5ba17a;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        .detail-value {
            color: #5ba17a;
            font-weight: 600;
        }
        .next-steps {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }
        .next-steps h5 {
            color: #856404;
            margin-bottom: 15px;
        }
        .next-steps ul {
            margin-bottom: 0;
            color: #856404;
        }
        .action-buttons {
            margin-top: 40px;
        }
        .btn-home {
            background: linear-gradient(135deg, #5ba17a 0%, #4a9268 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(91, 161, 122, 0.3);
            color: white;
        }
        .btn-contact {
            background: transparent;
            color: #5ba17a;
            border: 2px solid #5ba17a;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        .btn-contact:hover {
            background: #5ba17a;
            color: white;
        }
        @keyframes bounce {
            0%, 20%, 60%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            80% { transform: translateY(-10px); }
        }
        @media (max-width: 768px) {
            .success-card {
                padding: 30px 20px;
            }
            .success-title {
                font-size: 2rem;
            }
            .detail-row {
                flex-direction: column;
                text-align: center;
            }
            .detail-label {
                margin-bottom: 5px;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn-home, .btn-contact {
                margin: 10px 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fa fa-check-circle"></i>
            </div>
            
            <h1 class="success-title">Payment Successful!</h1>
            <p class="lead">Your booking has been confirmed successfully.</p>
            
            <?php
            // Get booking details from URL parameters or session
            $booking_id = $_GET['booking_id'] ?? 'N/A';
            $order_id = $_GET['order_id'] ?? $booking_id;
            $amount = $_GET['amount'] ?? 'N/A';
            $transaction_id = $_GET['transaction_id'] ?? $_GET['cf_payment_id'] ?? 'N/A';
            
            // If we have a booking ID, fetch details from database
            if ($booking_id !== 'N/A') {
                try {
                    $pdo = new PDO("mysql:host=localhost;dbname=yoga_retreat_bookings", "root", "");
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ?");
                    $stmt->execute([$booking_id]);
                    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($booking) {
                        echo '<div class="booking-details">';
                        echo '<h4 style="color: #5ba17a; margin-bottom: 20px;"><i class="fa fa-file-text"></i> Booking Details</h4>';
                        
                        echo '<div class="detail-row">';
                        echo '<span class="detail-label">Booking ID:</span>';
                        echo '<span class="detail-value">' . htmlspecialchars($booking['booking_id']) . '</span>';
                        echo '</div>';
                        
                        echo '<div class="detail-row">';
                        echo '<span class="detail-label">Guest Name:</span>';
                        echo '<span class="detail-value">' . htmlspecialchars($booking['name']) . '</span>';
                        echo '</div>';
                        
                        echo '<div class="detail-row">';
                        echo '<span class="detail-label">Program:</span>';
                        echo '<span class="detail-value">' . htmlspecialchars($booking['program']) . '</span>';
                        echo '</div>';
                        
                        if ($booking['check_in_date']) {
                            echo '<div class="detail-row">';
                            echo '<span class="detail-label">Check-in Date:</span>';
                            echo '<span class="detail-value">' . date('d M Y', strtotime($booking['check_in_date'])) . '</span>';
                            echo '</div>';
                        }
                        
                        if ($booking['check_out_date']) {
                            echo '<div class="detail-row">';
                            echo '<span class="detail-label">Check-out Date:</span>';
                            echo '<span class="detail-value">' . date('d M Y', strtotime($booking['check_out_date'])) . '</span>';
                            echo '</div>';
                        }
                        
                        echo '<div class="detail-row">';
                        echo '<span class="detail-label">Amount Paid:</span>';
                        echo '<span class="detail-value">₹' . number_format($booking['amount']) . '</span>';
                        echo '</div>';
                        
                        if ($transaction_id !== 'N/A') {
                            echo '<div class="detail-row">';
                            echo '<span class="detail-label">Transaction ID:</span>';
                            echo '<span class="detail-value">' . htmlspecialchars($transaction_id) . '</span>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }
                } catch (Exception $e) {
                    // If database connection fails, show basic details
                    echo '<div class="booking-details">';
                    echo '<h4 style="color: #5ba17a; margin-bottom: 20px;"><i class="fa fa-file-text"></i> Payment Details</h4>';
                    echo '<div class="detail-row">';
                    echo '<span class="detail-label">Booking ID:</span>';
                    echo '<span class="detail-value">' . htmlspecialchars($booking_id) . '</span>';
                    echo '</div>';
                    if ($amount !== 'N/A') {
                        echo '<div class="detail-row">';
                        echo '<span class="detail-label">Amount Paid:</span>';
                        echo '<span class="detail-value">₹' . htmlspecialchars($amount) . '</span>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
            }
            ?>
            
            <div class="next-steps">
                <h5><i class="fa fa-info-circle"></i> What's Next?</h5>
                <ul>
                    <li>You will receive a confirmation email with detailed itinerary within 24 hours</li>
                    <li>Our team will contact you 2-3 days before your arrival date</li>
                    <li>Please keep your booking ID safe for future reference</li>
                    <li>Bring comfortable yoga clothes and personal care items</li>
                </ul>
            </div>
            
            <div class="action-buttons d-flex justify-content-center">
                <a href="index.html" class="btn btn-home">
                    <i class="fa fa-home"></i> Back to Home
                </a>
                <a href="contact.html" class="btn btn-contact">
                    <i class="fa fa-phone"></i> Contact Us
                </a>
            </div>
            
            <div style="margin-top: 40px; color: #6c757d;">
                <p><strong>Need Help?</strong></p>
                <p>WhatsApp us at <strong>+91-6203517866</strong> or email <strong>naturelandyogchetna@gmail.com</strong></p>
                <a href="https://wa.me/916203517866" target="_blank" class="btn btn-success mt-2">
                    <i class="fa fa-whatsapp"></i> Chat on WhatsApp
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-redirect to home page after 2 minutes
        setTimeout(function() {
            if (confirm('Would you like to return to the home page?')) {
                window.location.href = 'index.html';
            }
        }, 120000); // 2 minutes
        
        // Update payment status via webhook simulation (for testing)
        <?php if ($booking_id !== 'N/A'): ?>
        fetch('booking-system.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'update_payment_status',
                booking_id: '<?php echo htmlspecialchars($booking_id); ?>',
                status: 'success',
                transaction_id: '<?php echo htmlspecialchars($transaction_id); ?>'
            })
        }).catch(error => console.log('Status update error:', error));
        <?php endif; ?>
    </script>
</body>
</html>