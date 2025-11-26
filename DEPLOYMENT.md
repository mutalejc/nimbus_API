# Deployment Guide

This guide provides step-by-step instructions for deploying the MSSQL API Dashboard to a production environment.

## Pre-Deployment Checklist

Before deploying to production, ensure you have:

- [ ] A server with PHP 8.2+ installed
- [ ] Composer installed
- [ ] Node.js 18+ and npm installed
- [ ] Microsoft ODBC Driver 18 for SQL Server installed
- [ ] Access to the MSSQL database (BB\Pilot instance)
- [ ] A domain name (optional but recommended)
- [ ] SSL certificate (recommended for production)

## Step 1: Server Preparation

### Install Required Software

```bash
# Update system packages
sudo apt-get update && sudo apt-get upgrade -y

# Install PHP 8.2 and extensions
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt-get install -y php8.2 php8.2-cli php8.2-fpm php8.2-common \
    php8.2-curl php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath \
    php8.2-tokenizer php8.2-pdo php8.2-dev php-pear unixodbc-dev

# Install Microsoft ODBC Driver
curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
curl https://packages.microsoft.com/config/ubuntu/22.04/prod.list | \
    sudo tee /etc/apt/sources.list.d/mssql-release.list
sudo apt-get update
sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18

# Install SQL Server PHP extensions
sudo pecl install sqlsrv pdo_sqlsrv
echo "extension=sqlsrv.so" | sudo tee /etc/php/8.2/cli/conf.d/20-sqlsrv.ini
echo "extension=pdo_sqlsrv.so" | sudo tee /etc/php/8.2/cli/conf.d/20-pdo_sqlsrv.ini
echo "extension=sqlsrv.so" | sudo tee /etc/php/8.2/fpm/conf.d/20-sqlsrv.ini
echo "extension=pdo_sqlsrv.so" | sudo tee /etc/php/8.2/fpm/conf.d/20-pdo_sqlsrv.ini

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install Nginx (or Apache)
sudo apt-get install -y nginx
```

## Step 2: Deploy Application

### Clone or Upload Project

```bash
# Create directory
sudo mkdir -p /var/www/mssql-api-dashboard
sudo chown -R $USER:$USER /var/www/mssql-api-dashboard

# Upload your project files to /var/www/mssql-api-dashboard
# Or clone from repository:
# git clone <your-repo-url> /var/www/mssql-api-dashboard

cd /var/www/mssql-api-dashboard
```

### Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies and build assets
npm install
npm run build
```

### Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env file with production settings
nano .env
```

**Important `.env` settings:**

```env
APP_NAME="MSSQL API Dashboard"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=sqlsrv
DB_HOST=BB\Pilot
DB_PORT=1433
DB_DATABASE=PilotDB
DB_USERNAME=api_ro
DB_PASSWORD=your_secure_password

MASTER_API_SECRET=generate_a_long_random_string_here

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Set Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/mssql-api-dashboard

# Set directory permissions
sudo find /var/www/mssql-api-dashboard -type d -exec chmod 755 {} \;
sudo find /var/www/mssql-api-dashboard -type f -exec chmod 644 {} \;

# Set storage and cache permissions
sudo chmod -R 775 /var/www/mssql-api-dashboard/storage
sudo chmod -R 775 /var/www/mssql-api-dashboard/bootstrap/cache
```

## Step 3: Database Setup

### Run Migrations

```bash
cd /var/www/mssql-api-dashboard
php artisan migrate --force
```

### Seed Admin User

```bash
php artisan db:seed --class=AdminUserSeeder --force
```

**Default credentials:**
- Email: admin@micropos.co.zm
- Password: password

**⚠️ IMPORTANT**: Change this password immediately after first login!

## Step 4: Web Server Configuration

### Nginx Configuration

Create a new Nginx configuration file:

```bash
sudo nano /etc/nginx/sites-available/mssql-api-dashboard
```

Add the following configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/mssql-api-dashboard/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/mssql-api-dashboard /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Apache Configuration (Alternative)

If using Apache instead of Nginx:

```bash
sudo nano /etc/apache2/sites-available/mssql-api-dashboard.conf
```

Add:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/mssql-api-dashboard/public

    <Directory /var/www/mssql-api-dashboard/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/mssql-api-error.log
    CustomLog ${APACHE_LOG_DIR}/mssql-api-access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite mssql-api-dashboard
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## Step 5: SSL Certificate (Recommended)

### Using Let's Encrypt (Certbot)

```bash
# Install Certbot
sudo apt-get install -y certbot python3-certbot-nginx

# Obtain and install certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal is set up automatically
```

For Apache:

```bash
sudo apt-get install -y certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

## Step 6: Optimization

### Cache Configuration

```bash
cd /var/www/mssql-api-dashboard

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

### PHP-FPM Optimization

Edit PHP-FPM pool configuration:

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Adjust these settings based on your server resources:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
```

## Step 7: Monitoring and Logging

### Set Up Log Rotation

Create a log rotation configuration:

```bash
sudo nano /etc/logrotate.d/laravel
```

Add:

```
/var/www/mssql-api-dashboard/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### Monitor Application

```bash
# View Laravel logs
tail -f /var/www/mssql-api-dashboard/storage/logs/laravel.log

# View Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# View PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

## Step 8: Security Hardening

### Firewall Configuration

```bash
# Allow SSH, HTTP, and HTTPS
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

### Disable Directory Listing

Already handled in the Nginx/Apache configuration above.

### Hide PHP Version

Edit PHP-FPM configuration:

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Set:

```ini
expose_php = Off
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
```

## Step 9: Backup Strategy

### Database Backup

Create a backup script:

```bash
sudo nano /usr/local/bin/backup-api-keys.sh
```

Add:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/mssql-api-dashboard"
mkdir -p $BACKUP_DIR

# Backup Laravel database (api_keys table)
cd /var/www/mssql-api-dashboard
php artisan db:backup --path=$BACKUP_DIR/api_keys_$DATE.sql

# Keep only last 30 days of backups
find $BACKUP_DIR -name "api_keys_*.sql" -mtime +30 -delete
```

Make it executable:

```bash
sudo chmod +x /usr/local/bin/backup-api-keys.sh
```

Schedule with cron:

```bash
sudo crontab -e
```

Add:

```
0 2 * * * /usr/local/bin/backup-api-keys.sh
```

## Step 10: Post-Deployment Tasks

### Change Default Password

1. Visit `https://your-domain.com/login`
2. Log in with admin@micropos.co.zm / password
3. Go to Profile and change password

### Generate API Key

1. Go to Dashboard
2. Click "Generate API Key"
3. Copy and save the key securely
4. Test the API with the new key

### Test API Endpoints

```bash
# Test with your generated API key
curl -X GET "https://your-domain.com/api/v1/clients?page=1&pageSize=10" \
  -H "X-API-Key: your_generated_api_key"
```

## Maintenance

### Update Application

```bash
cd /var/www/mssql-api-dashboard

# Pull latest changes (if using git)
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### Clear Cache

```bash
cd /var/www/mssql-api-dashboard

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Troubleshooting

### Check PHP Extensions

```bash
php -m | grep sqlsrv
```

### Test Database Connection

```bash
cd /var/www/mssql-api-dashboard
php artisan tinker
>>> DB::connection('sqlsrv')->getPdo();
```

### Check Permissions

```bash
ls -la /var/www/mssql-api-dashboard/storage
ls -la /var/www/mssql-api-dashboard/bootstrap/cache
```

### View Error Logs

```bash
tail -f /var/www/mssql-api-dashboard/storage/logs/laravel.log
```

## Support

For issues or questions during deployment, refer to:
- Laravel Documentation: https://laravel.com/docs
- Project README: /var/www/mssql-api-dashboard/README.md
