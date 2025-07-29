@echo off
REM Advanced Analytics Dashboard - Production Queue Workers
REM This script starts optimized queue workers for analytics processing

echo Starting HDTickets Analytics Queue Workers...
echo ============================================

REM High Priority Analytics Queue (Critical ML processing)
echo Starting High Priority Analytics Worker...
start "Analytics-High" cmd /c "php artisan queue:work --queue=analytics-high --memory=256 --timeout=300 --tries=3 --sleep=1"

REM Medium Priority Analytics Queue (Dashboard updates)
echo Starting Medium Priority Analytics Worker...
start "Analytics-Medium" cmd /c "php artisan queue:work --queue=analytics-medium --memory=128 --timeout=120 --tries=2 --sleep=2"

REM Notifications Queue (Alert processing)
echo Starting Notifications Worker...
start "Notifications" cmd /c "php artisan queue:work --queue=notifications --memory=64 --timeout=60 --tries=3 --sleep=1"

REM Default Queue (General processing)
echo Starting Default Queue Worker...
start "Default-Queue" cmd /c "php artisan queue:work --queue=default --memory=128 --timeout=60 --tries=2 --sleep=3"

echo.
echo ✅ All Analytics Queue Workers Started Successfully!
echo.
echo Active Workers:
echo - Analytics High Priority (Memory: 256MB, Timeout: 5min)
echo - Analytics Medium Priority (Memory: 128MB, Timeout: 2min)  
echo - Notifications (Memory: 64MB, Timeout: 1min)
echo - Default Queue (Memory: 128MB, Timeout: 1min)
echo.
echo Monitor worker status with: php artisan queue:monitor
echo.
pause
