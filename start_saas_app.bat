@echo off
SETLOCAL EnableDelayedExpansion
title UNIVERSAL INVEST STRATEGY
color 0A

echo =======================================================
echo     DEMARRAGE DE LA PLATEFORME UNIVERSAL INVEST STRATEGY
echo =======================================================
echo.
echo Ce script va configurer et lancer le serveur web local.
echo L'application sera accessible depuis votre navigateur web.
echo.
echo Veuillez patienter...
echo.

:: =========================================================
:: ETAPE 1 : TROUVER PHP
:: =========================================================

where php >nul 2>nul
if %errorlevel% equ 0 goto PHP_OK

:: PHP pas dans le PATH, chercher dans Laragon
for /d %%i in ("C:\laragon\bin\php\php-*") do set "PHP_DIR=%%i"
if defined PHP_DIR (
    set "PATH=!PHP_DIR!;%PATH%"
    echo [+] PHP detecte dans Laragon.
    goto PHP_OK
)

:: Chercher dans XAMPP
if exist "C:\xampp\php\php.exe" (
    set "PATH=C:\xampp\php;%PATH%"
    echo [+] PHP detecte dans XAMPP.
    goto PHP_OK
)

echo.
echo [ERREUR] PHP n'est pas installe sur cet ordinateur.
echo.
echo SOLUTION : Installez le logiciel gratuit LARAGON depuis :
echo            https://laragon.org/download/
echo.
echo Puis relancez ce script.
echo.
pause
exit /b

:PHP_OK
echo [OK] PHP est disponible.

:: =========================================================
:: ETAPE 2 : VERIFIER / CREER LE FICHIER .ENV
:: =========================================================

if exist .env goto ENV_OK

echo [+] Premier lancement detecte - Configuration en cours...
echo [+] Creation du fichier de configuration...
copy .env.example .env >nul 2>nul
if not exist .env (
    echo [ERREUR] Fichier .env.example introuvable. Le projet est incomplet.
    pause
    exit /b
)
php artisan key:generate --no-interaction
echo [+] Fichier .env cree et cle generee.

:ENV_OK

:: =========================================================
:: ETAPE 3 : VERIFIER / INSTALLER LES DEPENDANCES COMPOSER
:: =========================================================

if exist vendor goto VENDOR_OK

echo.
echo =======================================================
echo   PREMIER LANCEMENT - INSTALLATION DES DEPENDANCES
echo   Cela prend 1 a 2 minutes, veuillez patienter...
echo =======================================================
echo.

:: Chercher Composer
where composer >nul 2>nul
if %errorlevel% equ 0 goto COMPOSER_OK

:: Chercher dans Laragon
if exist "C:\laragon\bin\composer\composer.bat" (
    set "PATH=C:\laragon\bin\composer;%PATH%"
    goto COMPOSER_OK
)

echo [ERREUR] Composer n'est pas installe.
echo SOLUTION : Installez Laragon qui inclut Composer automatiquement.
echo            https://laragon.org/download/
echo.
pause
exit /b

:COMPOSER_OK
echo [+] Installation des dependances PHP...
call composer install --no-interaction --prefer-dist --quiet

if not exist vendor (
    echo [ERREUR] L'installation des dependances a echoue.
    echo Verifiez votre connexion internet et relancez le script.
    pause
    exit /b
)
echo [+] Dependances installees avec succes.

:: =========================================================
:: ETAPE 4 : CREER LA BASE DE DONNEES ET MIGRER
:: =========================================================

echo [+] Verification de la base de donnees...
if not exist "%~dp0setup_mysql.php" (
    powershell -NoProfile -Command "$c=@'
<?php
$host='127.0.0.1';$port=3306;$user='root';$pass='';$dbname='saas_accounting';
try{$pdo=new PDO(\"mysql:host={$host};port={$port}\",$user,$pass);$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$pdo->exec(\"CREATE DATABASE IF NOT EXISTS ``{$dbname}`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci\");echo \"[+] Base prete\".PHP_EOL;exit(0);}catch(Throwable $e){echo $e->getMessage().PHP_EOL;exit(1);}
'@; Set-Content -LiteralPath '%~dp0setup_mysql.php' -Value $c -Encoding UTF8"
)
php setup_mysql.php
if %errorlevel% neq 0 (
    echo.
    echo SOLUTION : Ouvrez Laragon et cliquez sur "Start All" puis relancez.
    echo.
    pause
    exit /b
)

echo [+] Migration et initialisation des donnees...
php artisan migrate --seed --force
echo [+] Base de donnees initialisee.
echo.

:VENDOR_OK

:: =========================================================
:: ETAPE 5 : PREPARER LE DOSSIER SAGE
:: =========================================================

if exist "C:\Sage_Import" goto SAGE_DIR_OK
echo [+] Creation du dossier C:\Sage_Import...
mkdir "C:\Sage_Import" 2>nul
:SAGE_DIR_OK

:: =========================================================
:: ETAPE 6 : LANCER L'AUTOMATE SAGE
:: =========================================================

where python >nul 2>nul
if %errorlevel% equ 0 (
    echo [+] Lancement de l'automate Sage...
    start "SAGE AUTOMATE" /min python sage_sync_agent.py
) else (
    echo [NOTE] Python non detecte - automate Sage desactive.
)

:: =========================================================
:: ETAPE 7 : DEMARRER LE SERVEUR WEB
:: =========================================================

echo [+] Demarrage du serveur web sur le port 8000...
start "SERVEUR LARAVEL" /min _server.bat

:: Attendre que le serveur soit pret
timeout /t 4 /nobreak >nul

:: Verifier que le serveur ecoute
netstat -ano 2>nul | findstr "127.0.0.1:8000" | findstr "LISTENING" >nul
if %errorlevel% neq 0 goto SERVEUR_ECHEC
goto SERVEUR_OK

:SERVEUR_ECHEC
echo.
echo [ERREUR] Le serveur web n'a pas pu demarrer.
echo.
echo Causes possibles :
echo   - Le port 8000 est deja utilise. Fermez les autres applications web.
echo   - MySQL n'est pas demarre. Ouvrez Laragon et cliquez "Start All".
echo.
pause
exit /b

:SERVEUR_OK
echo.
echo [SUCCES] Le serveur est en marche !
echo.

:: Ouvrir le navigateur
start "" http://127.0.0.1:8000

:: Lancer Sage 100 si present
set "SAGE_EXE=C:\Program Files (x86)\Sage\iComptabilite\Maestria.exe"
if exist "%SAGE_EXE%" (
    echo [+] Ouverture de Sage 100...
    start "" "%SAGE_EXE%" "C:\Users\pc\Desktop\UIS2026.mae"
)

echo =======================================================
echo   L'APPLICATION EST PRETE !
echo.
echo   Votre navigateur va s'ouvrir sur la plateforme.
echo.
echo   NE FERMEZ PAS CETTE FENETRE tant que vous travaillez.
echo   Pour quitter, appuyez sur une touche ci-dessous.
echo =======================================================
echo.
pause >nul

:: Nettoyage a la fermeture
echo.
echo Arret en cours...
taskkill /F /FI "WINDOWTITLE eq SERVEUR LARAVEL*" >nul 2>nul
taskkill /F /FI "WINDOWTITLE eq SAGE AUTOMATE*" >nul 2>nul
taskkill /F /IM php.exe >nul 2>nul
echo Tout est arrete. A bientot !
timeout /t 2 >nul
