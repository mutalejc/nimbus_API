# Quick Start Guide

Get up and running with the MSSQL API Dashboard in minutes.

## Prerequisites

- Ubuntu 22.04 or similar Linux distribution
- Root or sudo access
- Access to MSSQL Server (BB\Pilot instance, PilotDB database)

## Installation (5 Steps)

### Step 1: Install Dependencies

```bash
# Install PHP 8.2 and extensions
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt-get install -y php8.2 php8.2-cli php8.2-common php8.2-curl \
    php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-tokenizer \
    php8.2-pdo php8.2-dev php-pear unixodbc-dev

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

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Step 2: Setup Project

```bash
# Navigate to project directory
cd /path/to/mssql-api-dashboard

# Install PHP dependencies
composer install

# Install Node dependencies and build assets
npm install
npm run build
```

### Step 3: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env file
nano .env
```

**Update these values in `.env`:**

```env
DB_CONNECTION=sqlsrv
DB_HOST=BB\Pilot
DB_PORT=1433
DB_DATABASE=PilotDB
DB_USERNAME=api_ro
DB_PASSWORD=your_actual_password

MASTER_API_SECRET=change_this_to_a_long_random_string
```

### Step 4: Setup Database

```bash
# Run migrations
php artisan migrate

# Seed admin user
php artisan db:seed
```

### Step 5: Start Server

```bash
# Start development server
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Default Login

- **Email**: admin@micropos.co.zm
- **Password**: password

**⚠️ Change this password immediately after first login!**

## First Steps

1. **Login** to the dashboard at `http://localhost:8000/login`

2. **Generate API Key**:
   - Click "Generate API Key" button
   - Copy the displayed key (shown only once!)
   - Save it securely

3. **Test the API**:
   ```bash
   curl -X GET "http://localhost:8000/api/v1/clients?page=1&pageSize=10" \
     -H "X-API-Key: your_generated_api_key"
   ```

## MSSQL Database Setup

Before using the API, create the required view on your MSSQL server:

```sql
-- Create reporting schema
IF NOT EXISTS (SELECT 1 FROM sys.schemas WHERE name = 'reporting')
    EXEC('CREATE SCHEMA reporting');
GO

-- Create the view
CREATE OR ALTER VIEW reporting.vw_clients_public
AS
SELECT
    CAST(CLIENTNUM AS bigint) AS clientNum,
    PAYROLL AS payroll,
    CLIENTNAME AS clientName,
    NAME AS name,
    TITLE AS title,
    INITIAL AS initial,
    SALUTATION AS salutation,
    ADDRESS1 AS address1,
    ADDRESS2 AS address2,
    ADDRESS3 AS address3,
    CASE
        WHEN TELH IS NULL OR LEN(LTRIM(RTRIM(TELH))) = 0 THEN NULL
        WHEN LEN(TELH) <= 4 THEN TELH
        ELSE REPLICATE('*', LEN(TELH) - 4) + RIGHT(TELH, 4)
    END AS telHMasked,
    CASE
        WHEN TELW IS NULL OR LEN(LTRIM(RTRIM(TELW))) = 0 THEN NULL
        WHEN LEN(TELW) <= 4 THEN TELW
        ELSE REPLICATE('*', LEN(TELW) - 4) + RIGHT(TELW, 4)
    END AS telWMasked,
    CASE
        WHEN EMAIL IS NULL OR CHARINDEX('@', EMAIL) = 0 THEN NULL
        ELSE LEFT(EMAIL, 1) + '*****' + SUBSTRING(EMAIL, CHARINDEX('@', EMAIL), 4000)
    END AS emailMasked,
    CONTACT AS contact,
    ACTIVE AS active,
    DATEADDED AS dateAdded,
    LASTTDATE AS lastTDate,
    PHADD1 AS phAdd1,
    PHADD2 AS phAdd2,
    PHADD3 AS phAdd3,
    PHADD4 AS phAdd4,
    ClientType AS clientType
FROM dbo.Dbtclients;
GO
```

## Common Issues

### 1. MSSQL Connection Failed

**Error**: `could not find driver`

**Solution**:
```bash
# Verify SQL Server extensions are loaded
php -m | grep sqlsrv

# If not listed, reinstall:
sudo pecl install sqlsrv pdo_sqlsrv
echo "extension=sqlsrv.so" | sudo tee /etc/php/8.2/cli/conf.d/20-sqlsrv.ini
echo "extension=pdo_sqlsrv.so" | sudo tee /etc/php/8.2/cli/conf.d/20-pdo_sqlsrv.ini
```

### 2. Permission Denied

**Error**: `Permission denied` when accessing storage

**Solution**:
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

### 3. Assets Not Loading

**Error**: Styles not appearing

**Solution**:
```bash
npm install
npm run build
php artisan view:clear
```

## Next Steps

- Read the [full README](README.md) for detailed information
- Check [API Documentation](API_DOCUMENTATION.md) for API usage
- Review [Deployment Guide](DEPLOYMENT.md) for production setup

## Support

For issues:
1. Check the `storage/logs/laravel.log` file
2. Verify MSSQL connection with `php artisan tinker`
3. Test PHP extensions with `php -m`
