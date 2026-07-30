@echo off
SETLOCAL EnableDelayedExpansion
cd /d "%~dp0"

set "PHP_EXE="
where php >nul 2>&1 && set "PHP_EXE=php"

if not defined PHP_EXE (
    for /d %%i in ("C:\laragon\bin\php\php-*") do set "PHP_DIR=%%i"
    if defined PHP_DIR set "PATH=!PHP_DIR!;%PATH%" && set "PHP_EXE=php"
)

if not defined PHP_EXE (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
        set "PHP_EXE=C:\xampp\php\php.exe"
    )
)

if not defined PHP_EXE exit /b 1

set "TUNNEL_MODE=1"
php artisan config:clear >nul 2>&1
php artisan serve --host=127.0.0.1 --port=8000
