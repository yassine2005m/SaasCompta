@echo off
TITLE Installation de Sage Cloud Agent - Universal Invest Strategy
COLOR 0A

echo =======================================================
echo    INSTALLATION DE L'AGENT DE SYNCHRONISATION SAGE
echo    Universal Invest Strategy
echo =======================================================
echo.
echo Cet installateur va :
echo   1. Copier l'agent dans C:\SageSyncAgent\
echo   2. Creer le dossier C:\Sage_Import\
echo   3. Creer un raccourci sur votre Bureau
echo   4. Ajouter l'agent au demarrage automatique de Windows
echo.
echo =======================================================
echo.

set "INSTALL_DIR=C:\SageSyncAgent"
set "IMPORT_DIR=C:\Sage_Import"

:: 1. Créer le dossier d'installation
echo [1/4] Creation du dossier d'installation...
if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"
if not exist "%INSTALL_DIR%\logs" mkdir "%INSTALL_DIR%\logs"
echo       OK - %INSTALL_DIR%

:: 2. Copier les fichiers
echo [2/4] Copie des fichiers...
copy /Y "SageCloudAgent.exe" "%INSTALL_DIR%\" >nul 2>&1
if %errorlevel% neq 0 (
    echo       [ERREUR] SageCloudAgent.exe introuvable dans ce dossier !
    echo       Placez ce script dans le meme dossier que SageCloudAgent.exe
    pause
    exit /b
)
copy /Y "config.ini" "%INSTALL_DIR%\" >nul 2>&1
echo       OK

:: 3. Créer le dossier d'import Sage
echo [3/4] Creation du dossier Sage Import...
if not exist "%IMPORT_DIR%" mkdir "%IMPORT_DIR%"
echo       OK - %IMPORT_DIR%

:: 4. Créer un raccourci sur le Bureau
echo [4/4] Creation du raccourci Bureau et demarrage auto...

:: Raccourci Bureau via PowerShell
powershell -NoProfile -Command "$ws = New-Object -ComObject WScript.Shell; $sc = $ws.CreateShortcut([System.IO.Path]::Combine([Environment]::GetFolderPath('Desktop'), 'Sage Cloud Agent.lnk')); $sc.TargetPath = 'C:\SageSyncAgent\SageCloudAgent.exe'; $sc.WorkingDirectory = 'C:\SageSyncAgent'; $sc.Description = 'Agent de synchronisation Sage - Universal Invest Strategy'; $sc.Save()"

:: Ajout au démarrage automatique de Windows (dans le dossier Startup)
powershell -NoProfile -Command "$ws = New-Object -ComObject WScript.Shell; $startup = $ws.SpecialFolders('Startup'); $sc = $ws.CreateShortcut([System.IO.Path]::Combine($startup, 'Sage Cloud Agent.lnk')); $sc.TargetPath = 'C:\SageSyncAgent\SageCloudAgent.exe'; $sc.WorkingDirectory = 'C:\SageSyncAgent'; $sc.WindowStyle = 7; $sc.Save()"

echo       OK

echo.
echo =======================================================
echo    INSTALLATION TERMINEE !
echo =======================================================
echo.
echo L'agent a ete installe dans : %INSTALL_DIR%
echo Un raccourci a ete cree sur votre Bureau.
echo L'agent demarrera automatiquement avec Windows.
echo.
echo IMPORTANT : Avant de lancer l'agent, modifiez le fichier
echo             %INSTALL_DIR%\config.ini
echo             avec l'URL de votre site web.
echo.
echo =======================================================
echo.

:: Ouvrir le config.ini pour que l'utilisateur puisse le modifier
echo Voulez-vous modifier la configuration maintenant ? (O/N)
set /p EDIT="> "
if /i "%EDIT%"=="O" (
    notepad "%INSTALL_DIR%\config.ini"
)

echo.
echo Voulez-vous lancer l'agent maintenant ? (O/N)
set /p LAUNCH="> "
if /i "%LAUNCH%"=="O" (
    start "" "%INSTALL_DIR%\SageCloudAgent.exe"
    echo Agent lance !
)

echo.
pause
