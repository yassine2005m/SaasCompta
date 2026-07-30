# ============================================================
#  install-cloudflared.ps1
#  Installe cloudflared une seule fois sur la machine
#  Usage : clic droit -> "Exécuter avec PowerShell"
# ============================================================

$ErrorActionPreference = "Stop"

function Write-Step { param($msg) Write-Host "`n[*] $msg" -ForegroundColor Cyan }
function Write-OK   { param($msg) Write-Host "[+] $msg"   -ForegroundColor Green }
function Write-Fail { param($msg) Write-Host "[x] $msg"   -ForegroundColor Red }

Clear-Host
Write-Host "============================================" -ForegroundColor Magenta
Write-Host "   Installation de cloudflared              " -ForegroundColor Magenta
Write-Host "============================================`n" -ForegroundColor Magenta

# ── Vérifier si déjà installé ───────────────────────────────
Write-Step "Vérification..."
if (Get-Command "cloudflared" -ErrorAction SilentlyContinue) {
    $v = cloudflared --version 2>&1
    Write-OK "cloudflared est déjà installé : $v"
    Write-Host "`n  Rien à faire. Tu peux fermer cette fenêtre." -ForegroundColor Gray
    Read-Host "`nAppuie sur Entrée pour quitter"
    exit 0
}

# ── Télécharger cloudflared ──────────────────────────────────
Write-Step "Téléchargement de cloudflared depuis GitHub..."

$installDir = "$env:LOCALAPPDATA\cloudflared"
$exePath    = "$installDir\cloudflared.exe"
$downloadUrl = "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe"

if (-not (Test-Path $installDir)) {
    New-Item -ItemType Directory -Path $installDir | Out-Null
}

try {
    Write-Host "  -> Connexion à GitHub..." -ForegroundColor Gray
    Invoke-WebRequest -Uri $downloadUrl -OutFile $exePath -UseBasicParsing
    Write-OK "Fichier téléchargé : $exePath"
} catch {
    Write-Fail "Échec du téléchargement : $_"
    Write-Host "`n  Télécharge manuellement ici :" -ForegroundColor Yellow
    Write-Host "  https://github.com/cloudflare/cloudflared/releases/latest" -ForegroundColor Yellow
    Write-Host "  Puis place cloudflared.exe dans : $installDir" -ForegroundColor Yellow
    Read-Host "`nAppuie sur Entrée pour quitter"
    exit 1
}

# ── Ajouter au PATH utilisateur ──────────────────────────────
Write-Step "Ajout de cloudflared au PATH..."

$userPath = [Environment]::GetEnvironmentVariable("PATH", "User")
if ($userPath -notlike "*$installDir*") {
    [Environment]::SetEnvironmentVariable("PATH", "$userPath;$installDir", "User")
    Write-OK "PATH mis à jour (redémarre ton terminal pour que ça prenne effet)."
} else {
    Write-OK "Dossier déjà dans le PATH."
}

# Mettre à jour le PATH de la session courante aussi
$env:PATH += ";$installDir"

# ── Vérification finale ──────────────────────────────────────
Write-Step "Vérification de l'installation..."
try {
    $version = & "$exePath" --version 2>&1
    Write-OK "cloudflared installé avec succès : $version"
} catch {
    Write-Fail "Problème lors de la vérification. Essaie de relancer le terminal."
}

Write-Host "`n"
Write-Host "╔══════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║   cloudflared est prêt !                     ║" -ForegroundColor Green
Write-Host "║                                              ║" -ForegroundColor Green
Write-Host "║   Lance maintenant : START TUNNEL.bat        ║" -ForegroundColor Green
Write-Host "╚══════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Read-Host "Appuie sur Entrée pour quitter"
