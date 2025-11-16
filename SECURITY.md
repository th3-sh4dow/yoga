# Security Configuration Guide

## Overview
This application now uses a secure configuration system that eliminates hardcoded credentials and sensitive information from the codebase.

## Configuration Setup

### 1. Environment Variables
Create a `.env` file in the root directory (copy from `.env.example`):

```bash
cp .env.example .env
```

### 2. Required Configuration
Fill in your actual values in the `.env` file:

```env
# Database Configuration
DB_HOST=your_database_host
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password

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

## Security Features

### 1. No Hardcoded Credentials
- All sensitive information is loaded from environment variables
- Database credentials are never stored in code
- Payment gateway secrets are externalized

### 2. Configuration Validation
- Required configuration is validated on startup
- Missing configuration causes graceful failure
- Clear error messages for configuration issues

### 3. Secure Database Connections
- Uses prepared statements to prevent SQL injection
- Connection pooling disabled for security
- Proper error handling without exposing sensitive data

### 4. Webhook Security
- Signature verification for payment webhooks
- Timing-attack-safe signature comparison
- Secure logging that masks sensitive data

## File Security

### Protected Files
These files are automatically ignored by Git:
- `.env` - Contains actual credentials
- `*.log` - May contain sensitive data
- `dbpass` - Old credential file (deleted)

### Configuration Files
- `config.php` - Secure configuration loader
- `.env.example` - Template for environment variables
- `.gitignore` - Prevents committing sensitive files

## Deployment Security

### Production Checklist
1. ✅ Create `.env` file with production values
2. ✅ Ensure `.env` is not accessible via web
3. ✅ Set proper file permissions (600 for .env)
4. ✅ Use HTTPS for all payment operations
5. ✅ Enable error logging but disable display_errors
6. ✅ Regularly rotate API keys and secrets

### Server Configuration
```apache
# Add to .htaccess to protect .env file
<Files ".env">
    Order allow,deny
    Deny from all
</Files>
```

## Migration from Old System

### What Was Removed
- `dbpass` file containing plain text credentials
- Hardcoded database credentials in PHP files
- Hardcoded email addresses and API keys
- Direct credential references in all files

### What Was Added
- Secure configuration system (`config.php`)
- Environment variable support
- Configuration validation
- Secure credential loading
- Git ignore rules for sensitive files

## Troubleshooting

### Configuration Errors
If you see "Configuration error" messages:
1. Check that `.env` file exists
2. Verify all required variables are set
3. Ensure no syntax errors in `.env` file
4. Check file permissions

### Database Connection Issues
1. Verify database credentials in `.env`
2. Check database server accessibility
3. Ensure database user has proper permissions
4. Test connection with `test-db.php`

## Best Practices

1. **Never commit `.env` files**
2. **Use different credentials for different environments**
3. **Regularly rotate API keys and passwords**
4. **Monitor access logs for suspicious activity**
5. **Keep configuration files outside web root when possible**
6. **Use strong, unique passwords for all services**

## Support
For security-related issues, please check the configuration first and ensure all environment variables are properly set.