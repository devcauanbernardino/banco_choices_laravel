# Gera pacote para upload na HostGator (FTP / Gerenciador de arquivos).
# Uso: .\scripts\build-hostgator-deploy.ps1

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

Write-Host '>> composer install --no-dev...'
composer install --no-dev --optimize-autoloader --no-interaction

$outDir = Join-Path $root 'deploy-package'
$zipPath = Join-Path $root 'deploy-bancodechoices.zip'

if (Test-Path $outDir) { Remove-Item $outDir -Recurse -Force }
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
New-Item -ItemType Directory -Path $outDir | Out-Null

$excludeDirs = @(
    '.git', 'node_modules', 'deploy-package', '.claude', '.idea', '.vscode',
    '.fleet', '.nova', '.zed', 'tests', 'docs\layouts'
)
$excludeFiles = @('.env', '.env.backup', '.env.production', 'deploy-bancodechoices.zip', 'Homestead.json', 'Homestead.yaml', 'phpunit.xml', 'phpunit.result.cache')

function Should-Skip($rel) {
    foreach ($d in $excludeDirs) {
        if ($rel -like "$d*" -or $rel -like "*\$d\*") { return $true }
    }
    $name = Split-Path -Leaf $rel
    if ($excludeFiles -contains $name) { return $true }
    if ($name -like '*.log') { return $true }
    return $false
}

Write-Host '>> Copiando ficheiros...'
Get-ChildItem -Path $root -Recurse -Force | ForEach-Object {
    $rel = $_.FullName.Substring($root.Length + 1)
    if (Should-Skip $rel) { return }
    if ($_.PSIsContainer) {
        $dest = Join-Path $outDir $rel
        if (-not (Test-Path $dest)) { New-Item -ItemType Directory -Path $dest -Force | Out-Null }
    } else {
        $dest = Join-Path $outDir $rel
        $destParent = Split-Path -Parent $dest
        if (-not (Test-Path $destParent)) { New-Item -ItemType Directory -Path $destParent -Force | Out-Null }
        Copy-Item $_.FullName $dest -Force
    }
}

# .env de produção (não commitar deploy/.env.hostgator — só local)
$envOut = Join-Path $outDir '.env'
$envCustom = Join-Path $root 'deploy\.env.hostgator'
$envExample = Join-Path $root 'deploy\.env.hostgator.example'
if (Test-Path $envCustom) {
    Copy-Item $envCustom $envOut -Force
} else {
    Copy-Item $envExample $envOut -Force
    $localEnv = Join-Path $root '.env'
    if (Test-Path $localEnv) {
        $local = Get-Content $localEnv -Raw
        foreach ($key in @('APP_KEY', 'MP_ACCESS_TOKEN', 'MP_PUBLIC_KEY', 'MP_WEBHOOK_SECRET', 'MP_CURRENCY_ID')) {
            if ($local -match "(?m)^$key=(.+)$") {
                $val = $Matches[1].Trim()
                (Get-Content $envOut) -replace "(?m)^$key=.*", "$key=$val" | Set-Content $envOut
            }
        }
    }
}
Write-Host '>> .env incluído — edita DB_* e MAIL_PASSWORD antes do upload (ou no servidor)'

# Limpar caches de dev
@(
    (Join-Path $outDir 'bootstrap\cache\config.php'),
    (Join-Path $outDir 'bootstrap\cache\routes-v7.php'),
    (Join-Path $outDir 'bootstrap\cache\events.php')
) | ForEach-Object { if (Test-Path $_) { Remove-Item $_ -Force } }

Write-Host '>> A criar ZIP...'
Compress-Archive -Path (Join-Path $outDir '*') -DestinationPath $zipPath -Force

$sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 1)
Write-Host "OK: $zipPath ($sizeMb MB)"
Write-Host 'Próximo: upload na HostGator + docs/deploy/HOSTGATOR.md'
