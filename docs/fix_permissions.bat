@echo off
echo Fixing BMMS file permissions for Windows...
echo.

REM Check if running as Administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running as Administrator - proceeding with permission fixes...
) else (
    echo ERROR: This script must be run as Administrator
    echo Right-click and select "Run as Administrator"
    pause
    exit /b 1
)

REM Set permissions for config directory
echo Setting permissions for config directory...
icacls "config" /grant "IUSR:(OI)(CI)(F)" /T
icacls "config" /grant "IIS_IUSRS:(OI)(CI)(F)" /T
icacls "config" /grant "apache:(OI)(CI)(F)" /T

REM Set permissions for specific files
echo Setting permissions for database files...
icacls "config\database.php" /grant "IUSR:(F)" 2>nul
icacls "config\database.php" /grant "IIS_IUSRS:(F)" 2>nul
icacls "config\installed.lock" /grant "IUSR:(F)" 2>nul
icacls "config\installed.lock" /grant "IIS_IUSRS:(F)" 2>nul

echo.
echo Permission fixes completed!
echo If you're using XAMPP/WAMP, you may also need to:
echo 1. Right-click on the config folder
echo 2. Select Properties
echo 3. Go to Security tab
echo 4. Click Edit and ensure the web server user has Full Control
echo.
pause
