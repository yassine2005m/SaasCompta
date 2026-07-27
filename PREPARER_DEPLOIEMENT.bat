@echo off
SETLOCAL EnableDelayedExpansion
title PREPARATION DU DEPLOIEMENT - UNIVERSAL INVEST STRATEGY
color 0B

echo =======================================================
echo    PREPARATION DU PROJET POUR HEBERGEMENT GRATUIT
echo    (InfinityFree / 000WebHost / Tout hebergeur PHP)
echo =======================================================
echo.

:: Vérifier que PHP est disponible
where php >nul 2>nul
if %errorlevel% neq 0 (
    for /d %%i in ("C:\laragon\bin\php\php-*") do set "PHP_DIR=%%i"
    if defined PHP_DIR (
        set "PATH=!PHP_DIR!;%PATH%"
    ) else (
        echo [ERREUR] PHP non trouve. Installez Laragon ou XAMPP.
        pause
        exit /b
    )
)

echo [1/6] Nettoyage du cache Laravel...
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan view:clear >nul 2>&1
echo       OK

echo [2/6] Optimisation pour la production...
php artisan config:cache >nul 2>&1
php artisan route:cache >nul 2>&1
php artisan view:cache >nul 2>&1
echo       OK

echo [3/6] Preparation du .env de production...
if exist ".env.production" (
    echo       .env.production detecte - il sera inclus dans le ZIP.
) else (
    echo       [ATTENTION] .env.production introuvable !
    echo       Creez-le a partir de .env.production.example
)

echo [4/6] Nettoyage des fichiers inutiles pour le serveur...

:: Créer un dossier temporaire pour le déploiement
if exist "DEPLOY_TEMP" rmdir /s /q "DEPLOY_TEMP"
mkdir "DEPLOY_TEMP"

:: Copier tout sauf les fichiers inutiles
echo       Copie des fichiers necessaires...
xcopy /E /I /Q /Y "app" "DEPLOY_TEMP\app" >nul 2>&1
xcopy /E /I /Q /Y "bootstrap" "DEPLOY_TEMP\bootstrap" >nul 2>&1
xcopy /E /I /Q /Y "config" "DEPLOY_TEMP\config" >nul 2>&1
xcopy /E /I /Q /Y "database" "DEPLOY_TEMP\database" >nul 2>&1
xcopy /E /I /Q /Y "public" "DEPLOY_TEMP\public" >nul 2>&1
xcopy /E /I /Q /Y "resources" "DEPLOY_TEMP\resources" >nul 2>&1
xcopy /E /I /Q /Y "routes" "DEPLOY_TEMP\routes" >nul 2>&1
xcopy /E /I /Q /Y "storage" "DEPLOY_TEMP\storage" >nul 2>&1
xcopy /E /I /Q /Y "vendor" "DEPLOY_TEMP\vendor" >nul 2>&1

:: Copier les fichiers racine essentiels
copy /Y "artisan" "DEPLOY_TEMP\" >nul 2>&1
copy /Y "composer.json" "DEPLOY_TEMP\" >nul 2>&1
copy /Y "composer.lock" "DEPLOY_TEMP\" >nul 2>&1
copy /Y "server.php" "DEPLOY_TEMP\" >nul 2>&1
copy /Y ".env.production" "DEPLOY_TEMP\.env" >nul 2>&1
copy /Y "vite.config.js" "DEPLOY_TEMP\" >nul 2>&1
copy /Y "package.json" "DEPLOY_TEMP\" >nul 2>&1

:: Le .htaccess à la racine redirige vers public/
echo ^<IfModule mod_rewrite.c^> > "DEPLOY_TEMP\.htaccess"
echo     RewriteEngine On >> "DEPLOY_TEMP\.htaccess"
echo     RewriteRule ^^(.*)$ public/$1 [L] >> "DEPLOY_TEMP\.htaccess"
echo ^</IfModule^> >> "DEPLOY_TEMP\.htaccess"

echo       OK

echo [5/6] Creation du fichier ZIP pour upload...
:: Utiliser PowerShell pour créer le ZIP
if exist "UPLOAD_HEBERGEUR.zip" del /f "UPLOAD_HEBERGEUR.zip"
powershell -NoProfile -Command "Compress-Archive -Path 'DEPLOY_TEMP\*' -DestinationPath 'UPLOAD_HEBERGEUR.zip' -Force"
echo       OK - Fichier cree : UPLOAD_HEBERGEUR.zip

echo [6/6] Nettoyage...
rmdir /s /q "DEPLOY_TEMP" >nul 2>&1
echo       OK

echo.
echo =======================================================
echo    DEPLOIEMENT PRET !
echo =======================================================
echo.
echo Le fichier "UPLOAD_HEBERGEUR.zip" est pret.
echo.
echo ETAPES SUIVANTES :
echo.
echo 1. Allez sur https://www.infinityfree.com et creez un compte GRATUIT
echo 2. Creez un site (vous recevrez un sous-domaine gratuit)
echo 3. Allez dans "Bases de donnees MySQL" et creez une base
echo 4. Notez : nom_base, utilisateur, mot_de_passe, serveur_sql
echo 5. Modifiez le fichier .env dans le ZIP avec ces informations
echo 6. Uploadez le contenu du ZIP via le Gestionnaire de Fichiers
echo    dans le dossier "htdocs" de votre site
echo 7. Importez votre base de donnees via phpMyAdmin
echo    (fichier: base_de_donnees_saas.sql)
echo.
echo IMPORTANT : Le dossier "public" doit etre la racine du site.
echo Sur InfinityFree, uploadez tout dans htdocs/ et configurez
echo le "Document Root" vers le sous-dossier "public".
echo.
echo =======================================================
pause
