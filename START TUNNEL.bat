@echo off
title Laravel + Cloudflare Tunnel
color 0A

echo.
echo  ============================================
echo   Laravel + Cloudflare Tunnel - Demarrage
echo  ============================================
echo.

:: Aller dans le dossier du script
cd /d "%~dp0"

:: Verifier si PowerShell est disponible
where powershell >nul 2>&1
if errorlevel 1 (
    echo [ERREUR] PowerShell n'est pas installe.
    pause
    exit /b 1
)

:: Lancer le script PowerShell avec les bons droits d'execution
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0start-tunnel.ps1"

:: Si le script se termine (fermeture manuelle), on pause
echo.
echo  Le tunnel est arrete.
pause
