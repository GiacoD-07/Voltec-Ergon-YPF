# Inicia la aplicación con el servidor integrado de PHP, sin Apache ni XAMPP.
# Ejecutar desde PowerShell con: .\scripts\start-local.ps1

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

# Comprueba que el ejecutable PHP disponible sea compatible con el proyecto.
$phpCommand = Get-Command php -ErrorAction SilentlyContinue
if ($null -eq $phpCommand) {
    throw 'No se encontró PHP en PATH. Instalá PHP 8.1 o superior y reiniciá PowerShell.'
}

$phpVersion = (php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;").Trim()
$versionParts = $phpVersion.Split('.') | ForEach-Object { [int]$_ }
if ($versionParts[0] -lt 8 -or ($versionParts[0] -eq 8 -and $versionParts[1] -lt 1)) {
    throw "PHP $phpVersion no cumple el requisito mínimo 8.1."
}

# Las extensiones son necesarias para que PDO conecte con MySQL y Google Sheets.
$requiredExtensions = @('curl', 'pdo_mysql')
$loadedExtensions = php -m
foreach ($extension in $requiredExtensions) {
    if ($loadedExtensions -notcontains $extension) {
        throw "Falta la extensión PHP '$extension'. Habilitala en php.ini."
    }
}

if (-not (Test-Path '.env')) {
    throw 'No existe .env. Copiá .env.example a .env y configurá la conexión MySQL.'
}

# Escucha en todas las interfaces para que el ESP32 pueda llegar por Wi-Fi.
Write-Host "Servidor disponible en http://localhost:8000/"
Write-Host 'Para el ESP32 usa http://IP_DE_TU_PC:8000/voltec-slim/public/api'
php -S 0.0.0.0:8000 -t public public/router.php