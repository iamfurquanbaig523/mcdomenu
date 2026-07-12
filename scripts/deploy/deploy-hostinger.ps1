param(
    [string]$ReleaseId = "",
    [switch]$PrepareOnly,
    [switch]$AllowLivePathReplace,
    [switch]$SeedSharedFromLive,
    [switch]$SkipCachePurge
)

$ErrorActionPreference = "Stop"

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$Worker = Join-Path $ScriptDir "deploy-hostinger.py"
$Python = Join-Path $env:LOCALAPPDATA "Python\bin\python.exe"

if (-not (Test-Path -LiteralPath $Python)) {
    $Python = "python"
}

if (-not $env:HOSTINGER_SSH_PASSWORD) {
    $securePassword = Read-Host "Hostinger SSH password" -AsSecureString
    $bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
    try {
        $env:HOSTINGER_SSH_PASSWORD = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr)
    }
    finally {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr)
    }
}

$ArgsList = @($Worker)

if ($ReleaseId) {
    $ArgsList += @("--release-id", $ReleaseId)
}

if ($PrepareOnly) {
    $ArgsList += "--prepare-only"
}

if ($AllowLivePathReplace) {
    $ArgsList += "--allow-live-path-replace"
}

if ($SeedSharedFromLive) {
    $ArgsList += "--seed-shared-from-live"
}

if ($SkipCachePurge) {
    $ArgsList += "--skip-cache-purge"
}

& $Python @ArgsList
