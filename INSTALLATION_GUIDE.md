# Yoga Retreat Booking System - Installation Guide

## Overview
This improved booking system includes:
- ✅ Responsive pop-up booking form
- ✅ Database integration with payment status tracking
- ✅ Cashfree payment integration
- ✅ Email notifications for users and owners
- ✅ Admin panel for booking management
- ✅ Payment webhook handling

## Installation Steps

### 1. Database Setup
1. Create a MySQL database named `yoga_retreat_bookings`
2. Run the SQL script in `setup-database.sql` to create tables:
   ```sql
   mysql -u root -p yoga_retreat_bookings < setup-database.sql
   ```

### 2. File Upload
Upload all the new files to your web server:
- `booking-system.php` - Main backend API
- `payment-webhook.php` - Handles Cashfree webhooks
- `payment-success.php` - Success page after payment
- `css/booking-modal.css` - Modal styling
- `js/booking-modal.js` - Modal functionality
- `admin-bookings.html` - Admin panel for managing bookings

### 3. Secure Configuration Setup

#### Environment Configuration
1. Copy the environment template:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` file with your actual credentials:
   ```env
   # Database Configuration
   DB_HOST=mysql.hostinger.in
   DB_NAME=your_database_name
   DB_USER=your_database_user
   DB_PASS=your_secure_password

   # Cashfree Payment Gateway
   CASHFREE_APP_ID=your_cashfree_app_id
   CASHFREE_CLIENT_SECRET=your_cashfree_client_secret
   CASHFREE_PG_SECRET=your_cashfree_pg_secret

   # Environment
   APP_ENV=production

   # Email Configuration
   FROM_EMAIL=your_from_email@domain.com
   OWNER_EMAIL=your_owner_email@domain.com
   ```

3. **IMPORTANT**: Never commit the `.env` file to version control!

#### Security Features
- ✅ No hardcoded credentials in code
- ✅ Environment variable configuration
- ✅ Automatic configuration validation
- ✅ Secure credential loading
- ✅ Protected sensitive files

### 4. Webhook Setup
1. In your Cashfree dashboard, set the webhook URL to:
   ```
   https://yourwebsite.com/payment-webhook.php
   ```
2. Set the return URL to:
   ```
   https://yourwebsite.com/payment-success.php
   ```

### 5. File Permissions
Ensure proper file permissions:
```bash
chmod 644 *.php *.html
chmod 755 css/ js/
```

## Features

### For Users:
- **Responsive Booking Modal**: Opens when clicking "Book Now" buttons
- **Program Selection**: Choose from Weekend, 3-Day, 7-Day, or Online programs
- **Accommodation Options**: Garden Cottage or Premium Cottage with occupancy selection
- **Real-time Price Calculator**: Shows total amount based on selections
- **Form Validation**: Ensures all required fields are filled
- **Payment Integration**: Redirects to Cashfree payment page
- **Email Notifications**: Confirmation and payment status updates

### For Admins:
- **Booking Dashboard**: View all bookings with statistics
- **Status Management**: Update payment status manually
- **Filtering**: Filter by status, program, date range
- **Export**: Download bookings as CSV
- **Detailed View**: See complete booking information
- **Real-time Updates**: Automatic status updates via webhooks

## Payment Flow

1. **User fills booking form** → Creates booking with "pending" status
2. **System generates payment link** → Redirects to Cashfree
3. **User completes payment** → Cashfree sends webhook
4. **Webhook updates status** → Sends notifications
5. **User sees success page** → Booking confirmed

## Database Schema

### bookings table:
- `booking_id` - Unique booking identifier
- `name`, `email`, `phone` - Guest details
- `program`, `accommodation`, `occupancy` - Booking details
- `amount` - Total amount
- `payment_status` - pending/success/failed/refunded
- `payment_link` - Generated payment URL
- `transaction_id` - Payment gateway transaction ID
- `created_at`, `updated_at` - Timestamps

### payment_transactions table:
- Detailed payment tracking
- Gateway responses
- Transaction history

### notifications table:
- Email notification log
- Delivery tracking

## Testing

### Test the Booking Flow:
1. Open `courses.html`
2. Click any "Book Now" button
3. Fill the booking form
4. Check database for new booking
5. Test payment with Cashfree test cards

### Test Admin Panel:
1. Open `admin-bookings.html`
2. View booking statistics
3. Filter and search bookings
4. Update payment status
5. Export bookings

## Troubleshooting

### Common Issues:

1. **Database Connection Error**:
   - Check database credentials
   - Ensure MySQL service is running
   - Verify database exists

2. **Payment Webhook Not Working**:
   - Check webhook URL in Cashfree dashboard
   - Verify file permissions
   - Check server logs

3. **Email Not Sending**:
   - Configure PHP mail settings
   - Check SMTP configuration
   - Verify email addresses

4. **Modal Not Opening**:
   - Check JavaScript console for errors
   - Ensure all CSS/JS files are loaded
   - Verify file paths

### Log Files:
- `payment_webhook_log.txt` - Webhook requests log
- Server error logs for PHP errors

## Security Notes

1. **Secure Configuration**: All credentials stored in environment variables
2. **Input Validation**: All user inputs are sanitized
3. **SQL Injection Protection**: Using prepared statements
4. **XSS Protection**: HTML escaping for output
5. **CSRF Protection**: Consider adding CSRF tokens for production
6. **SSL Required**: Use HTTPS for payment processing
7. **File Protection**: Sensitive files automatically ignored by Git
8. **Configuration Validation**: Required settings validated on startup

### Security Checklist:
- [ ] Create `.env` file with production credentials
- [ ] Ensure `.env` file is not web-accessible
- [ ] Set proper file permissions (600 for .env)
- [ ] Use HTTPS for all operations
- [ ] Regularly rotate API keys and passwords
- [ ] Monitor logs for suspicious activity

For detailed security information, see `SECURITY.md`.

## Customization

### Adding New Programs:
Edit `js/booking-modal.js` and update the `programs` object:
```javascript
programs: {
    'new_program': {
        title: 'New Program Name',
        duration: 'X Days',
        prices: {
            'garden_single': 15000,
            'garden_double': 12000,
            // ... other options
        }
    }
}
```

### Styling Changes:
Modify `css/booking-modal.css` to match your brand colors and design.

### Email Templates:
Update email templates in `booking-system.php` and `payment-webhook.php`.

## Support

For technical support or customization requests, contact your development team.

## Version
Version 1.0 - November 2024