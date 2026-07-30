# ============================================================
#  start-tunnel.ps1
#  Lance automatiquement Laravel + Cloudflare Tunnel
#  Usage : clic droit -> "Exécuter avec PowerShell"
# ============================================================

$ErrorActionPreference = "Stop"
$ProjectDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$EnvFile    = Join-Path $ProjectDir ".env"
$Port       = 8000

# ── Couleurs helpers ─────────────────────────────────────────
function Write-Step  { param($msg) Write-Host "`n[*] $msg" -ForegroundColor Cyan }
function Write-OK    { param($msg) Write-Host "[+] $msg"   -ForegroundColor Green }
function Write-Warn  { param($msg) Write-Host "[!] $msg"   -ForegroundColor Yellow }
function Write-Fail  { param($msg) Write-Host "[x] $msg"   -ForegroundColor Red }

Clear-Host
Write-Host "============================================" -ForegroundColor Magenta
Write-Host "   Laravel + Cloudflare Tunnel - Auto Start " -ForegroundColor Magenta
Write-Host "============================================`n" -ForegroundColor Magenta

# ── 1. Vérifier cloudflared ──────────────────────────────────
Write-Step "Vérification de cloudflared..."
if (-not (Get-Command "cloudflared" -ErrorAction SilentlyContinue)) {
    Write-Warn "cloudflared n'est pas installé. Téléchargement en cours..."

    $cfDir    = "$env:LOCALAPPDATA\cloudflared"
    $cfExe    = "$cfDir\cloudflared.exe"
    $cfUrl    = "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe"

    if (-not (Test-Path $cfDir)) {
        New-Item -ItemType Directory -Path $cfDir | Out-Null
    }

    Write-Host "  -> Téléchargement depuis GitHub..." -ForegroundColor Gray
    Invoke-WebRequest -Uri $cfUrl -OutFile $cfExe -UseBasicParsing

    # Ajouter au PATH de l'utilisateur (session courante + permanent)
    $currentPath = [Environment]::GetEnvironmentVariable("PATH", "User")
    if ($currentPath -notlike "*$cfDir*") {
        [Environment]::SetEnvironmentVariable("PATH", "$currentPath;$cfDir", "User")
    }
    $env:PATH += ";$cfDir"

    Write-OK "cloudflared installé dans $cfDir"
    $cfVersion = & "$cfExe" --version 2>&1
    Write-OK "Version : $cfVersion"
} else {
    $cfVersion = cloudflared --version 2>&1
    Write-OK "cloudflared trouvé : $cfVersion"
}

# ── 2. Vérifier PHP ─────────────────────────────────────────
Write-Step "Vérification de PHP..."
if (-not (Get-Command "php" -ErrorAction SilentlyContinue)) {
    Write-Fail "PHP n'est pas trouvé dans le PATH. Installe PHP et relance ce script."
    Read-Host "Appuie sur Entrée pour quitter"
    exit 1
}
$phpVersion = php --version | Select-Object -First 1
Write-OK "PHP trouvé : $phpVersion"

# ── 3. Vérifier le port libre ───────────────────────────────
Write-Step "Vérification du port $Port..."
$portUsed = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
if ($portUsed) {
    Write-Warn "Le port $Port est déjà utilisé. Tentative de libération..."
    $procId = (Get-NetTCPConnection -LocalPort $Port -State Listen).OwningProcess | Select-Object -First 1
    Stop-Process -Id $procId -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 1
    Write-OK "Port $Port libéré."
} else {
    Write-OK "Port $Port disponible."
}

# ── 4. Préparer Laravel ─────────────────────────────────────
Write-Step "Préparation de Laravel..."
Set-Location $ProjectDir

# Vider les caches
php artisan config:clear  | Out-Null
php artisan cache:clear   | Out-Null
php artisan route:clear   | Out-Null
Write-OK "Caches Laravel vidés."

# ── 5. Démarrer Laravel en arrière-plan ─────────────────────
Write-Step "Démarrage du serveur Laravel sur le port $Port..."
$laravelJob = Start-Job -ScriptBlock {
    param($dir, $port)
    Set-Location $dir
    php artisan serve --host=127.0.0.1 --port=$port 2>&1
} -ArgumentList $ProjectDir, $Port

Start-Sleep -Seconds 3

# Vérifier que Laravel répond
try {
    $resp = Invoke-WebRequest -Uri "http://127.0.0.1:$Port" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    Write-OK "Laravel actif sur http://127.0.0.1:$Port (HTTP $($resp.StatusCode))"
} catch {
    # Un 302/redirect est aussi valide
    Write-OK "Laravel actif sur http://127.0.0.1:$Port"
}

# ── 6. Démarrer le tunnel Cloudflare ────────────────────────
Write-Step "Ouverture du tunnel Cloudflare..."

$logFile = Join-Path $ProjectDir "cloudflare-tunnel.log"
$cfArgs  = "tunnel --url http://127.0.0.1:$Port --logfile `"$logFile`""

$cfProcess = Start-Process -FilePath "cloudflared" `
    -ArgumentList $cfArgs `
    -PassThru -NoNewWindow `
    -RedirectStandardOutput "$ProjectDir\cf-stdout.log" `
    -RedirectStandardError  "$ProjectDir\cf-stderr.log"

Write-Host "`n  En attente de l'URL Cloudflare..." -ForegroundColor Gray

# ── 7. Lire l'URL générée ───────────────────────────────────
$tunnelUrl = $null
$attempts  = 0
$maxWait   = 30   # secondes max

while (-not $tunnelUrl -and $attempts -lt $maxWait) {
    Start-Sleep -Seconds 1
    $attempts++

    # Chercher l'URL dans les deux fichiers de log
    foreach ($logPath in @("$ProjectDir\cf-stdout.log", "$ProjectDir\cf-stderr.log", $logFile)) {
        if (Test-Path $logPath) {
            $content = Get-Content $logPath -Raw -ErrorAction SilentlyContinue
            if ($content -match 'https://[a-z0-9\-]+\.trycloudflare\.com') {
                $tunnelUrl = $Matches[0]
                break
            }
        }
    }

    if (-not $tunnelUrl) {
        Write-Host "  [$attempts/$maxWait] Attente de l'URL..." -ForegroundColor Gray -NoNewline
        Write-Host "`r" -NoNewline
    }
}

if (-not $tunnelUrl) {
    Write-Warn "URL non détectée automatiquement. Vérifie le fichier : $logFile"
    Write-Host "`n  Contenu du log :" -ForegroundColor Gray
    if (Test-Path "$ProjectDir\cf-stderr.log") {
        Get-Content "$ProjectDir\cf-stderr.log" | Select-Object -Last 20
    }
} else {
    # ── 8. Mettre à jour le .env ─────────────────────────────
    Write-Step "Mise à jour du .env avec l'URL du tunnel..."
    $envContent = Get-Content $EnvFile -Raw
    $envContent = $envContent -replace 'APP_URL=.*', "APP_URL=$tunnelUrl"
    Set-Content $EnvFile $envContent -NoNewline
    Write-OK ".env mis à jour : APP_URL=$tunnelUrl"

    # Vider le cache config après modification du .env
    Set-Location $ProjectDir
    php artisan config:clear | Out-Null

    # ── 9. Afficher le résultat ──────────────────────────────
    Write-Host "`n" 
    Write-Host "╔══════════════════════════════════════════════════════╗" -ForegroundColor Green
    Write-Host "║         TUNNEL CLOUDFLARE ACTIF !                    ║" -ForegroundColor Green
    Write-Host "╠══════════════════════════════════════════════════════╣" -ForegroundColor Green
    Write-Host "║                                                      ║" -ForegroundColor Green
    Write-Host "║  URL CLIENT : $tunnelUrl" -ForegroundColor Yellow
    Write-Host "║                                                      ║" -ForegroundColor Green
    Write-Host "╚══════════════════════════════════════════════════════╝" -ForegroundColor Green
    Write-Host ""
    Write-Host "  -> Copie cette URL et envoie-la a ton client !" -ForegroundColor Cyan

    # Copier automatiquement dans le presse-papier
    try {
        $tunnelUrl | Set-Clipboard
        Write-OK "URL copiée dans le presse-papier !"
    } catch {}

    # Ouvrir dans le navigateur
    Start-Process $tunnelUrl
}

# ── 10. Attendre (garder les processus actifs) ───────────────
Write-Host "`n  Appuie sur CTRL+C pour tout arrêter." -ForegroundColor Gray
Write-Host "  (Ferme cette fenêtre pour stopper Laravel et le tunnel)`n" -ForegroundColor Gray

try {
    # Boucle de surveillance
    while ($true) {
        Start-Sleep -Seconds 10

        # Vérifier que Laravel est toujours vivant
        $jobState = (Get-Job -Id $laravelJob.Id -ErrorAction SilentlyContinue).State
        if ($jobState -eq "Failed" -or $jobState -eq "Stopped") {
            Write-Warn "Laravel s'est arrêté ! Redémarrage..."
            $laravelJob = Start-Job -ScriptBlock {
                param($dir, $port)
                Set-Location $dir
                php artisan serve --host=127.0.0.1 --port=$port 2>&1
            } -ArgumentList $ProjectDir, $Port
            Write-OK "Laravel redémarré."
        }

        # Vérifier que cloudflared est vivant
        if ($cfProcess.HasExited) {
            Write-Warn "cloudflared s'est arrêté ! Relance le script pour un nouveau tunnel."
            break
        }
    }
} finally {
    # Nettoyage à la fermeture
    Write-Host "`n[*] Arrêt en cours..." -ForegroundColor Yellow
    Stop-Job  -Id $laravelJob.Id -ErrorAction SilentlyContinue
    Remove-Job -Id $laravelJob.Id -ErrorAction SilentlyContinue
    if (-not $cfProcess.HasExited) {
        $cfProcess.Kill()
    }
    # Restaurer APP_URL en localhost
    $envContent = Get-Content $EnvFile -Raw
    $envContent = $envContent -replace 'APP_URL=.*', "APP_URL=http://localhost:$Port"
    Set-Content $EnvFile $envContent -NoNewline
    Write-OK "APP_URL restauré à http://localhost:$Port"
    Write-Host "[+] Tout est arrêté proprement.`n" -ForegroundColor Green
}
