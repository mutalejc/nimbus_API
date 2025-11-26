#!/bin/bash

# MSSQL API Dashboard - Installation Verification Script
# This script checks if all required components are properly installed

echo "=========================================="
echo "MSSQL API Dashboard - Installation Check"
echo "=========================================="
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check function
check_command() {
    if command -v $1 &> /dev/null; then
        echo -e "${GREEN}✓${NC} $2 is installed"
        return 0
    else
        echo -e "${RED}✗${NC} $2 is NOT installed"
        return 1
    fi
}

check_php_extension() {
    if php -m | grep -q $1; then
        echo -e "${GREEN}✓${NC} PHP extension $1 is loaded"
        return 0
    else
        echo -e "${RED}✗${NC} PHP extension $1 is NOT loaded"
        return 1
    fi
}

# Check PHP
echo "Checking PHP..."
if check_command php "PHP"; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2)
    echo "  Version: $PHP_VERSION"
    
    # Check PHP version is 8.2 or higher
    if [[ $(echo "$PHP_VERSION" | cut -d. -f1) -ge 8 ]] && [[ $(echo "$PHP_VERSION" | cut -d. -f2) -ge 2 ]]; then
        echo -e "  ${GREEN}✓${NC} PHP version is 8.2 or higher"
    else
        echo -e "  ${RED}✗${NC} PHP version must be 8.2 or higher"
    fi
fi
echo ""

# Check Composer
echo "Checking Composer..."
if check_command composer "Composer"; then
    COMPOSER_VERSION=$(composer --version | cut -d " " -f 3)
    echo "  Version: $COMPOSER_VERSION"
fi
echo ""

# Check Node.js
echo "Checking Node.js..."
if check_command node "Node.js"; then
    NODE_VERSION=$(node -v)
    echo "  Version: $NODE_VERSION"
fi
echo ""

# Check npm
echo "Checking npm..."
if check_command npm "npm"; then
    NPM_VERSION=$(npm -v)
    echo "  Version: $NPM_VERSION"
fi
echo ""

# Check PHP Extensions
echo "Checking PHP Extensions..."
check_php_extension "pdo_sqlsrv"
check_php_extension "sqlsrv"
check_php_extension "mbstring"
check_php_extension "xml"
check_php_extension "curl"
check_php_extension "zip"
check_php_extension "bcmath"
check_php_extension "tokenizer"
echo ""

# Check ODBC Driver
echo "Checking ODBC Driver..."
if odbcinst -q -d | grep -q "ODBC Driver"; then
    echo -e "${GREEN}✓${NC} Microsoft ODBC Driver is installed"
    odbcinst -q -d | grep "ODBC Driver"
else
    echo -e "${RED}✗${NC} Microsoft ODBC Driver is NOT installed"
fi
echo ""

# Check if .env file exists
echo "Checking Laravel Configuration..."
if [ -f ".env" ]; then
    echo -e "${GREEN}✓${NC} .env file exists"
    
    # Check if APP_KEY is set
    if grep -q "APP_KEY=base64:" .env; then
        echo -e "${GREEN}✓${NC} Application key is set"
    else
        echo -e "${RED}✗${NC} Application key is NOT set (run: php artisan key:generate)"
    fi
    
    # Check database configuration
    if grep -q "DB_CONNECTION=sqlsrv" .env; then
        echo -e "${GREEN}✓${NC} Database connection is set to sqlsrv"
    else
        echo -e "${YELLOW}!${NC} Database connection is not set to sqlsrv"
    fi
else
    echo -e "${RED}✗${NC} .env file does NOT exist (copy from .env.example)"
fi
echo ""

# Check if vendor directory exists
echo "Checking Dependencies..."
if [ -d "vendor" ]; then
    echo -e "${GREEN}✓${NC} Composer dependencies are installed"
else
    echo -e "${RED}✗${NC} Composer dependencies are NOT installed (run: composer install)"
fi

if [ -d "node_modules" ]; then
    echo -e "${GREEN}✓${NC} Node dependencies are installed"
else
    echo -e "${RED}✗${NC} Node dependencies are NOT installed (run: npm install)"
fi
echo ""

# Check if public/build exists (assets compiled)
echo "Checking Compiled Assets..."
if [ -d "public/build" ]; then
    echo -e "${GREEN}✓${NC} Assets are compiled"
else
    echo -e "${RED}✗${NC} Assets are NOT compiled (run: npm run build)"
fi
echo ""

# Check storage permissions
echo "Checking Permissions..."
if [ -w "storage" ]; then
    echo -e "${GREEN}✓${NC} storage/ directory is writable"
else
    echo -e "${RED}✗${NC} storage/ directory is NOT writable"
fi

if [ -w "bootstrap/cache" ]; then
    echo -e "${GREEN}✓${NC} bootstrap/cache/ directory is writable"
else
    echo -e "${RED}✗${NC} bootstrap/cache/ directory is NOT writable"
fi
echo ""

# Test database connection
echo "Testing Database Connection..."
if [ -f ".env" ]; then
    php artisan tinker --execute="try { DB::connection('sqlsrv')->getPdo(); echo 'Database connection successful'; } catch (Exception \$e) { echo 'Database connection failed: ' . \$e->getMessage(); }" 2>&1 | grep -v "^>"
else
    echo -e "${YELLOW}!${NC} Cannot test database connection (.env file missing)"
fi
echo ""

echo "=========================================="
echo "Verification Complete"
echo "=========================================="
echo ""
echo "If any checks failed, please refer to:"
echo "  - README.md for detailed installation instructions"
echo "  - QUICKSTART.md for quick setup guide"
echo ""
