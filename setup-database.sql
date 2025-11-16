-- Database setup for Yoga Retreat Booking System
-- Run this SQL to create the database and tables

CREATE DATABASE IF NOT EXISTS yoga_retreat_bookings;
USE yoga_retreat_bookings;

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    program VARCHAR(100) NOT NULL,
    accommodation VARCHAR(50) NOT NULL,
    occupancy VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    payment_link TEXT,
    transaction_id VARCHAR(100),
    payment_date DATETIME,
    check_in_date DATE,
    check_out_date DATE,
    special_requirements TEXT,
    emergency_contact VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking_id (booking_id),
    INDEX idx_email (email),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created_at (created_at)
);

-- Payment transactions table for detailed tracking
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(20) NOT NULL,
    transaction_id VARCHAR(100),
    payment_method VARCHAR(50),
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
    gateway_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);

-- Notifications table for tracking sent notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(20) NOT NULL,
    type ENUM('booking_confirmation', 'payment_success', 'payment_failed', 'owner_alert') NOT NULL,
    recipient_email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);

-- Insert sample data for testing
INSERT INTO bookings (
    booking_id, name, email, phone, program, accommodation, 
    occupancy, amount, payment_status, check_in_date, check_out_date
) VALUES 
(
    'YR20241116001', 'John Doe', 'john@example.com', '+91-9876543210', 
    'Weekend Wellness Yoga Retreat', 'Garden Cottage', 'Single', 10000.00, 
    'pending', '2024-12-01', '2024-12-02'
),
(
    'YR20241116002', 'Jane Smith', 'jane@example.com', '+91-9876543211', 
    '3-Day Wellness & Retreat', 'Premium Cottage', 'Double', 16000.00, 
    'success', '2024-12-05', '2024-12-07'
);