@echo off
REM Fix Laravel Cache Path Error on Windows
REM Run this script from your project root directory

echo ================================================
echo Laravel Cache Path Fix for Windows
echo ================================================
echo.

echo Creating required cache directories...

REM Create storage directories if they don't exist
if not exist "storage\framework\cache" mkdir "storage\framework\cache"
if not exist "storage\framework\cache\data" mkdir "storage\framework\cache\data"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"
if not exist "storage\framework\views" mkdir "storage\framework\views"
if not exist "storage\logs" mkdir "storage\logs"
if not exist "bootstrap\cache" mkdir "bootstrap\cache"

echo.
echo Clearing existing cache...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo.
echo Setting proper permissions (if needed)...
REM Windows doesn't need chmod, but we'll create .gitkeep files
echo. > storage\framework\cache\.gitkeep
echo. > storage\framework\cache\data\.gitkeep
echo. > storage\framework\sessions\.gitkeep
echo. > storage\framework\views\.gitkeep
echo. > storage\logs\.gitkeep
echo. > bootstrap\cache\.gitkeep

echo.
echo Rebuilding cache...
php artisan config:cache

echo.
echo ================================================
echo Fix completed!
echo ================================================
echo.
echo You can now run: php artisan serve
echo.
pause
