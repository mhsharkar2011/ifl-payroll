@echo off
setlocal enabledelayedexpansion

:: Get local IP
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4 Address"') do (
    set IP=%%a
    set IP=!IP:~1!
)

echo.
echo ========================================
echo   Inventory Management System
echo ========================================
echo.
echo Local Access:    http://localhost:8080
echo Network Access:  http://!IP!:8080
echo.
echo ========================================
echo.

start "Laravel" cmd /k "php artisan serve --host=0.0.0.0 --port=8080"
start "Vite" cmd /k "npm run dev -- --host=0.0.0.0"

timeout /t 5
@REM start http://localhost:8080
start http://!IP!:8080

echo.
echo Servers are running!
echo To stop, close the terminal windows.
echo.
pause
