@echo off
echo ==========================================
echo  MHC Parish Management System
echo  Starting development server...
echo ==========================================
echo.

:: Remove Vite hot file to ensure CSS loads correctly
if exist "public\hot" (
    del /f /q "public\hot"
    echo [OK] Removed stale Vite hot file
)

:: Clear caches
php artisan view:clear > nul 2>&1
php artisan cache:clear > nul 2>&1
echo [OK] Cleared caches

:: Start server
echo.
echo [INFO] Server starting at http://127.0.0.1:8000
echo [INFO] Press Ctrl+C to stop
echo.
php artisan serve
