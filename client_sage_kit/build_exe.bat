@echo off
TITLE Compilation de l'Agent Sage en EXE
COLOR 0A

echo =======================================================
echo    COMPILATION DE L'AGENT SAGE EN FICHIER .EXE
echo =======================================================
echo.
echo Ce script va creer un fichier SageCloudAgent.exe
echo qui pourra fonctionner sur n'importe quel PC Windows
echo SANS avoir besoin d'installer Python.
echo.

:: Vérifier que Python est disponible
where python >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERREUR] Python n'est pas installe.
    echo Installez Python depuis : https://python.org/downloads
    echo Cochez "Add to PATH" lors de l'installation.
    pause
    exit /b
)

:: Vérifier/Installer PyInstaller
echo [1/3] Verification de PyInstaller...
python -m pip show pyinstaller >nul 2>nul
if %errorlevel% neq 0 (
    echo       Installation de PyInstaller...
    python -m pip install pyinstaller --quiet
)
echo       OK

:: Compilation
echo [2/3] Compilation en cours (cela peut prendre 1-2 minutes)...
python -m PyInstaller ^
    --onefile ^
    --windowed ^
    --name "SageCloudAgent" ^
    --add-data "config.ini;." ^
    --clean ^
    --noconfirm ^
    SageCloudAgent.py

echo       OK

:: Copier le config.ini à côté de l'exe
echo [3/3] Preparation du dossier de distribution...
if exist "dist\SageCloudAgent.exe" (
    copy /Y "config.ini" "dist\config.ini" >nul
    
    :: Créer le dossier final
    if exist "SageCloudAgent_Portable" rmdir /s /q "SageCloudAgent_Portable"
    mkdir "SageCloudAgent_Portable"
    copy /Y "dist\SageCloudAgent.exe" "SageCloudAgent_Portable\" >nul
    copy /Y "config.ini" "SageCloudAgent_Portable\" >nul
    copy /Y "..\client_sage_kit\INSTALLER_SAGE_SYNC.bat" "SageCloudAgent_Portable\" >nul 2>nul
    
    echo.
    echo =======================================================
    echo    COMPILATION REUSSIE !
    echo =======================================================
    echo.
    echo Le fichier EXE est dans : dist\SageCloudAgent.exe
    echo.
    echo Le dossier portable est dans : SageCloudAgent_Portable\
    echo Il contient :
    echo   - SageCloudAgent.exe  (l'agent)
    echo   - config.ini          (la configuration)
    echo.
    echo Donnez ce dossier au client.
    echo Le client n'a qu'a :
    echo   1. Modifier config.ini avec l'URL de son site
    echo   2. Double-cliquer sur SageCloudAgent.exe
    echo.
) else (
    echo.
    echo [ERREUR] La compilation a echoue.
    echo Verifiez les messages d'erreur ci-dessus.
)

:: Nettoyage
rmdir /s /q "build" >nul 2>nul
del /f "SageCloudAgent.spec" >nul 2>nul

pause
