# MSSQL API Dashboard

A Laravel-based REST API with Tailwind CSS admin dashboard that connects to Microsoft SQL Server and provides read-only data access through authenticated endpoints.

## Features

- **MSSQL Database Integration**: Connects to Microsoft SQL Server (BB\Pilot instance, PilotDB database)
- **Read-Only REST API**: Secure, read-only access to the `reporting.vw_clients_public` view
- **Static API Key Authentication**: Simple X-API-Key header authentication
- **Admin Dashboard**: Beautiful Tailwind CSS dashboard for API key management
- **Email/Password Authentication**: Secure admin login with Laravel Breeze
- **Filtering & Pagination**: Comprehensive query parameters for data filtering
- **Rate Limiting**: Built-in Laravel rate limiting support

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm/pnpm
- Microsoft SQL Server 2019+
- Microsoft ODBC Driver 18 for SQL Server
- PHP extensions: pdo_sqlsrv, sqlsrv

## Installation

### 1. Install PHP and Extensions

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update

# Install PHP 8.2 and required extensions
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
```

### 2. Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3. Clone and Setup Project

```bash
# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit `.env` file with your MSSQL credentials:

```env
DB_CONNECTION=sqlsrv
DB_HOST=BB\Pilot
DB_PORT=1433
DB_DATABASE=PilotDB
DB_USERNAME=api_ro
DB_PASSWORD=your_password_here

MASTER_API_SECRET=long_random_root_secret_change_in_production
```

### 5. Run Migrations and Seeders

```bash
# Run migrations (creates api_keys table)
php artisan migrate

# Seed admin user (admin@micropos.co.zm / password)
php artisan db:seed
```

### 6. Build Assets

```bash
npm run build
```

### 7. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## Default Admin Credentials

- **Email**: admin@micropos.co.zm
- **Password**: password

**⚠️ IMPORTANT**: Change the default password immediately after first login!

## Database Setup (MSSQL)

Run the following SQL script on your MSSQL server to create the required view:

```sql
-- Create reporting schema
IF NOT EXISTS (SELECT 1 FROM sys.schemas WHERE name = 'reporting')
    EXEC('CREATE SCHEMA reporting');
GO

-- Public, read-only view (masked & API-friendly aliases)
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
    
    -- Phone masking: keep last 4
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
    
    -- Email masking: first char + ***** + domain
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

-- Create read-only user (optional)
-- CREATE LOGIN api_ro WITH PASSWORD = 'Strong#Password#Here';
-- USE PilotDB;
-- CREATE USER api_ro FOR LOGIN api_ro;
-- GRANT SELECT ON OBJECT::reporting.vw_clients_public TO api_ro;
```

## API Usage

### Authentication

All API requests require an API key in the header:

```bash
X-API-Key: your_api_key_here
```

### Base URL

```
http://your-domain.com/api/v1
```

### Endpoints

#### 1. List Clients

```bash
GET /api/v1/clients
```

**Query Parameters:**

- `clientNum` - Filter by client number (exact match)
- `payroll` - Filter by payroll (exact match)
- `active` - Filter by active status (true/false)
- `nameContains` - Search in name field
- `clientNameContains` - Search in client name field
- `dateAddedFrom` - Filter by date added (from) - ISO8601 format
- `dateAddedTo` - Filter by date added (to) - ISO8601 format
- `lastTxFrom` - Filter by last transaction date (from)
- `lastTxTo` - Filter by last transaction date (to)
- `sort` - Sort results (clientNum, dateAdded, lastTDate, prefix with `-` for DESC)
- `page` - Page number (default: 1)
- `pageSize` - Results per page (default: 50, max: 500)

**Example Request:**

```bash
curl -X GET "http://localhost:8000/api/v1/clients?page=1&pageSize=50&active=true" \
  -H "X-API-Key: your_api_key_here"
```

**Example Response:**

```json
{
  "data": [
    {
      "clientNum": 1234567890,
      "payroll": "MOE",
      "clientName": "CHANDA LIMITED",
      "name": "CHANDA",
      "title": "MR",
      "initial": "C",
      "salutation": "Mr Chanda",
      "address1": "Plot 10",
      "address2": "Street Name",
      "address3": "Town",
      "telHMasked": "*******1234",
      "telWMasked": "*******4321",
      "emailMasked": "c*****@example.com",
      "contact": "Office",
      "active": true,
      "dateAdded": "2023-04-25T10:30:00Z",
      "lastTDate": "2025-07-02T14:05:00Z",
      "phAdd1": "...",
      "phAdd2": "...",
      "phAdd3": "...",
      "phAdd4": "...",
      "clientType": 1
    }
  ],
  "meta": {
    "page": 1,
    "pageSize": 50,
    "total": 3471
  }
}
```

#### 2. Get Single Client

```bash
GET /api/v1/clients/{clientNum}
```

**Example Request:**

```bash
curl -X GET "http://localhost:8000/api/v1/clients/1234567890" \
  -H "X-API-Key: your_api_key_here"
```

### Error Responses

- `400` - Invalid/blocked parameter
- `401` - Missing/invalid API key
- `404` - Resource not found
- `500` - Internal server error

## Admin Dashboard

Access the admin dashboard at `/dashboard` after logging in.

### Features:

1. **API Key Management**
   - Generate new API key
   - Regenerate existing key (invalidates old key)
   - Disable API key

2. **API Documentation**
   - View base URL
   - See available endpoints
   - Copy API key and URLs to clipboard

3. **Table Information**
   - View exposed tables
   - See available endpoints
   - Check field descriptions

## Security Features

- **Read-Only Access**: API only supports GET requests
- **Hashed API Keys**: Keys are stored as SHA-256 hashes
- **Masked PII**: Phone numbers and emails are masked in the view
- **View-Only Permissions**: Database user has SELECT-only access
- **Rate Limiting**: Built-in Laravel throttling
- **HTTPS Ready**: Configure SSL in production

## Deployment

### Production Checklist

- [ ] Change default admin password
- [ ] Update `MASTER_API_SECRET` in `.env`
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure proper MSSQL credentials
- [ ] Set up HTTPS/SSL certificates
- [ ] Configure rate limiting
- [ ] Set up proper logging
- [ ] Enable caching (`php artisan config:cache`, `php artisan route:cache`)
- [ ] Set up queue workers if needed
- [ ] Configure backup strategy

### Web Server Configuration

#### Apache

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/mssql-api-dashboard/public

    <Directory /path/to/mssql-api-dashboard/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/api-error.log
    CustomLog ${APACHE_LOG_DIR}/api-access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/mssql-api-dashboard/public;

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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Troubleshooting

### MSSQL Connection Issues

1. Verify ODBC driver is installed:
   ```bash
   odbcinst -q -d
   ```

2. Test connection:
   ```bash
   php artisan tinker
   DB::connection('sqlsrv')->getPdo();
   ```

3. Check PHP extensions:
   ```bash
   php -m | grep sqlsrv
   ```

### Permission Issues

Ensure the web server user has write access to:
- `storage/`
- `bootstrap/cache/`

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## License

This project is open-sourced software licensed under the MIT license.

## Support

For issues or questions, please contact your system administrator.
