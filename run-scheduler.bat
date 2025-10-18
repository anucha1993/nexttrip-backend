@echo off
REM NextTrip API Scheduler Runner
REM This batch file runs the scheduled API synchronizations

cd /d "c:\laragon\www\nexttrip-backend"

echo [%date% %time%] Starting API Scheduler...
php artisan api:sync-scheduled

if %errorlevel% equ 0 (
    echo [%date% %time%] Scheduler completed successfully.
) else (
    echo [%date% %time%] Scheduler completed with errors (Exit code: %errorlevel%).
)

REM Uncomment the line below if you want to see output (for debugging)
REM pause