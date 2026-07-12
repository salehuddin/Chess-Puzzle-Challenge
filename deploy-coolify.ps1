#Requires -Version 5.1
<#
.SYNOPSIS
    Deploys Chess Puzzle Challenge to a VPS via the Coolify API.
.DESCRIPTION
    Creates a project, MySQL database, and application in Coolify,
    configures environment variables, and triggers the first deployment.
.NOTES
    Run from the project root: .\deploy-coolify.ps1
#>

param(
    [switch]$DryRun
)

$ErrorActionPreference = "Stop"

# ──────────────────────────────────────────────────────────────────────
# Configuration — edit these values
# ──────────────────────────────────────────────────────────────────────
$COOLIFY_URL  = "http://109.123.239.88:8000/api/v1"
$COOLIFY_TOKEN = "1|Nl88hWEMFSqkiu7kgVXJyXREJ04MMC2lhlT4nKQf6d0a9828"

$PROJECT_NAME        = "chess-puzzle-challenge"
$PROJECT_DESCRIPTION = "Chess Puzzle Challenge - Laravel + Filament app"

$APP_NAME         = "chess-puzzle-challenge"
$APP_DOMAIN       = "chesspuzzlechallenge.com"
$GIT_REPOSITORY   = "https://github.com/salehuddin/Chess-Puzzle-Challenge"
$GIT_BRANCH       = "main"
$BUILD_PACK       = "dockerfile"
$PORTS_EXPOSES    = "9000"

$DB_NAME     = "chess-puzzle-mysql"
$DB_DATABASE = "chess_puzzle"
$DB_USER     = "chess_app"
$DB_PASSWORD = ""  # Leave empty to auto-generate

$IMAGE = "mysql:8"

# ──────────────────────────────────────────────────────────────────────
# Helpers
# ──────────────────────────────────────────────────────────────────────
function Invoke-Coolify {
    param(
        [string]$Method,
        [string]$Path,
        [object]$Body = $null
    )
    $uri = "$COOLIFY_URL$Path"
    $headers = @{
        "Authorization" = "Bearer $COOLIFY_TOKEN"
        "Content-Type"  = "application/json"
        "Accept"        = "application/json"
    }
    $params = @{
        Method  = $Method
        Uri     = $uri
        Headers = $headers
    }
    if ($Body -and $Method -ne "GET") {
        $params.Body = ($Body | ConvertTo-Json -Depth 20)
    }
    try {
        $response = Invoke-RestMethod @params
        return $response
    } catch {
        Write-Host "  ERROR: $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.Response) {
            $reader = [System.IO.StreamReader]::new($_.Exception.Response.GetResponseStream())
            $responseBody = $reader.ReadToEnd()
            Write-Host "  Response: $responseBody" -ForegroundColor Red
        }
        throw
    }
}

function Generate-RandomPassword {
    param([int]$Length = 32)
    $rng = New-Object System.Security.Cryptography.RNGCryptoServiceProvider
    $bytes = New-Object byte[] $Length
    $rng.GetBytes($bytes)
    return [Convert]::ToBase64String($bytes).Substring(0, $Length) -replace '[/+=]','A'
}

function Generate-AppKey {
    $rng = New-Object System.Security.Cryptography.RNGCryptoServiceProvider
    $bytes = New-Object byte[] 32
    $rng.GetBytes($bytes)
    return "base64:" + [Convert]::ToBase64String($bytes)
}

# ──────────────────────────────────────────────────────────────────────
# Main
# ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Coolify Deployment - Chess Puzzle" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

if ($DryRun) {
    Write-Host "[DRY RUN] No API calls will be made." -ForegroundColor Yellow
    Write-Host ""
}

# Auto-generate DB password if not set
if (-not $DB_PASSWORD) {
    $DB_PASSWORD = Generate-RandomPassword
    Write-Host "[*] Generated database password" -ForegroundColor Gray
}

# Generate APP_KEY
$APP_KEY = Generate-AppKey
Write-Host "[*] Generated APP_KEY" -ForegroundColor Gray

# ──────────────────────────────────────────────────────────────────────
# Step 1: Get team UUID
# ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "[1/8] Fetching team info..." -ForegroundColor Yellow
$team = Invoke-Coolify -Method "GET" -Path "/teams/current"
$TEAM_ID = $team.id
Write-Host "  Team: $($team.name) (ID: $TEAM_ID)" -ForegroundColor Green

# ──────────────────────────────────────────────────────────────────────
# Step 2: Get server UUID
# ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "[2/8] Fetching server UUID..." -ForegroundColor Yellow
$servers = Invoke-Coolify -Method "GET" -Path "/servers"
$server = $servers | Select-Object -First 1
$SERVER_UUID = $server.uuid
Write-Host "  Server UUID: $SERVER_UUID" -ForegroundColor Green
Write-Host "  Server name: $($server.name)" -ForegroundColor Gray

# ──────────────────────────────────────────────────────────────────────
# Step 3: Create project
# ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "[3/8] Creating project '$PROJECT_NAME'..." -ForegroundColor Yellow

if ($DryRun) {
    Write-Host "  [DRY RUN] Would create project" -ForegroundColor Yellow
    $PROJECT_UUID = "dry-run-project-uuid"
} else {
    $projectBody = @{
        name        = $PROJECT_NAME
        description = $PROJECT_DESCRIPTION
    }
    $project = Invoke-Coolify -Method "POST" -Path "/projects" -Body $projectBody
    $PROJECT_UUID = $project.uuid
    Write-Host "  Project UUID: $PROJECT_UUID" -ForegroundColor Green
}

# ──────────────────────────────────────────────────────────────────────
# Step 4: Create MySQL database
# ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "[4/8] Creating MySQL 8 database '$DB_NAME'..." -ForegroundColor Yellow

if ($DryRun) {
    Write-Host "  [DRY RUN] Would create MySQL database" -ForegroundColor Yellow
    $DB_UUID = "dry-run-db-uuid"
} else {
    $dbBody = @{
        server_uuid      = $SERVER_UUID
        project_uuid     = $PROJECT_UUID
        environment_name = "production"
        name             = $DB_NAME
        image            = $IMAGE
        mysql_database   = $DB_DATABASE
        mysql_user       = $DB_USER
        mysql_password   = $DB_PASSWORD
        mysql_root_password = (Generate-RandomPassword)
        instant_deploy   = $false
    }
    $db = Invoke-Coolify -Method "POST" -Path "/databases/mysql" -Body $dbBody
    $DB_UUID = $db.uuid
    Write-Host "  Database UUID: $DB_UUID" -ForegroundColor Green
    Write-Host "  Database name: $DB_DATABASE" -ForegroundColor Gray
    Write-Host "  Database user: $DB_USER" -ForegroundColor Gray
}

# ──────────────────────────────────────────────────────────────────────
# Step 5: Create application
# ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "[5/8] Creating application '$APP_NAME' from $GIT_REPOSITORY..." -ForegroundColor Yellow

if ($DryRun) {
    Write-Host "  [DRY RUN] Would create application" -ForegroundColor Yellow
    $APP_UUID = "dry-run-app-uuid"
} else {
    $appBody = @{
        project_uuid     = $PROJECT_UUID
        server_uuid      = $SERVER_UUID
        environment_name = "production"
        git_repository   = $GIT_REPOSITORY
        git_branch       = $GIT_BRANCH
        build_pack       = $BUILD_PACK
        ports_exposes    = $PORTS_EXPOSES
        name             = $APP_NAME
        description      = "Chess Puzzle Challenge Laravel application"
        dockerfile_location = "/Dockerfile"
        is_auto_deploy_enabled = $true
        is_force_https_enabled = $true
        instant_deploy   = $false
    }
    $app = Invoke-Coolify -Method "POST" -Path "/applications/public" -Body $appBody
    $APP_UUID = $app.uuid
    Write-Host "  Application UUID: $APP_UUID" -ForegroundColor Green
}

# ──────────────────────────────────────────────────────────────────────
# Step 6: Set environment variables (bulk)
# ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "[6/8] Setting environment variables..." -ForegroundColor Yellow

# The DB hostname inside Coolify's Docker network is the database container name.
# Coolify typically uses the database name as the container hostname.
$DB_HOST = $DB_NAME

$envVars = @(
    @{ key = "APP_NAME";                  value = "Chess Puzzle Challenge" }
    @{ key = "APP_ENV";                   value = "production" }
    @{ key = "APP_KEY";                   value = $APP_KEY }
    @{ key = "APP_DEBUG";                 value = "false" }
    @{ key = "APP_URL";                   value = "https://$APP_DOMAIN" }
    @{ key = "APP_LOCALE";                value = "en" }
    @{ key = "APP_FALLBACK_LOCALE";       value = "en" }
    @{ key = "APP_MAINTENANCE_DRIVER";    value = "file" }
    @{ key = "BCRYPT_ROUNDS";             value = "12" }
    @{ key = "LOG_CHANNEL";              value = "stack" }
    @{ key = "LOG_STACK";                value = "single" }
    @{ key = "LOG_LEVEL";                value = "error" }
    @{ key = "DB_CONNECTION";            value = "mysql" }
    @{ key = "DB_HOST";                  value = $DB_HOST }
    @{ key = "DB_PORT";                  value = "3306" }
    @{ key = "DB_DATABASE";              value = $DB_DATABASE }
    @{ key = "DB_USERNAME";              value = $DB_USER }
    @{ key = "DB_PASSWORD";              value = $DB_PASSWORD }
    @{ key = "SESSION_DRIVER";           value = "database" }
    @{ key = "SESSION_LIFETIME";         value = "120" }
    @{ key = "SESSION_ENCRYPT";          value = "false" }
    @{ key = "SESSION_PATH";             value = "/" }
    @{ key = "BROADCAST_CONNECTION";     value = "log" }
    @{ key = "FILESYSTEM_DISK";          value = "local" }
    @{ key = "LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK"; value = "livewire-tmp" }
    @{ key = "QUEUE_CONNECTION";         value = "database" }
    @{ key = "CACHE_STORE";              value = "database" }
    @{ key = "MAIL_MAILER";              value = "log" }
    @{ key = "SANDBOX_PAYMENT_MODE";     value = "true" }
    @{ key = "STRIPE_KEY";               value = "" }
    @{ key = "STRIPE_SECRET";            value = "" }
    @{ key = "STRIPE_WEBHOOK_SECRET";    value = "" }
)

if ($DryRun) {
    Write-Host "  [DRY RUN] Would set $($envVars.Count) environment variables" -ForegroundColor Yellow
} else {
    $envBody = @{ data = $envVars }
    Invoke-Coolify -Method "PATCH" -Path "/applications/$APP_UUID/envs/bulk" -Body $envBody | Out-Null
    Write-Host "  Set $($envVars.Count) environment variables" -ForegroundColor Green
}

# ──────────────────────────────────────────────────────────────────────
# Step 7: Configure post-deploy command & domain
# ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "[7/8] Setting post-deploy command and domain..." -ForegroundColor Yellow

$postDeployCmd = "php artisan migrate --force && php artisan storage:link && php artisan db:seed --class=RolesSeeder --force && php artisan db:seed --class=SettingsSeeder --force && php artisan config:cache && php artisan route:cache && php artisan view:cache"

if ($DryRun) {
    Write-Host "  [DRY RUN] Would set post-deploy command and domain" -ForegroundColor Yellow
} else {
    $updateBody = @{
        post_deployment_command = $postDeployCmd
        domains                 = "https://$APP_DOMAIN"
    }
    Invoke-Coolify -Method "PATCH" -Path "/applications/$APP_UUID" -Body $updateBody | Out-Null
    Write-Host "  Post-deploy command set" -ForegroundColor Green
    Write-Host "  Domain set to: $APP_DOMAIN" -ForegroundColor Green
}

# ──────────────────────────────────────────────────────────────────────
# Step 8: Trigger deployment
# ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "[8/8] Triggering deployment..." -ForegroundColor Yellow

if ($DryRun) {
    Write-Host "  [DRY RUN] Would trigger deployment" -ForegroundColor Yellow
} else {
    $deploy = Invoke-Coolify -Method "GET" -Path "/deploy?uuid=$APP_UUID"
    Write-Host "  Deployment triggered!" -ForegroundColor Green
    if ($deploy.deployments) {
        foreach ($d in $deploy.deployments) {
            Write-Host "  Deployment UUID: $($d.deployment_uuid)" -ForegroundColor Gray
        }
    }
}

# ──────────────────────────────────────────────────────────────────────
# Summary
# ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Deployment Summary" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Coolify Dashboard: http://109.123.239.88:8000" -ForegroundColor White
Write-Host "  Project:           $PROJECT_NAME" -ForegroundColor White
Write-Host "  Application:       $APP_NAME" -ForegroundColor White
Write-Host "  Domain:            https://$APP_DOMAIN" -ForegroundColor White
Write-Host "  Git Repo:          $GIT_REPOSITORY" -ForegroundColor White
Write-Host "  Build Pack:        $BUILD_PACK" -ForegroundColor White
Write-Host ""
Write-Host "  Database:" -ForegroundColor White
Write-Host "    Host:     $DB_HOST (internal Docker hostname)" -ForegroundColor Gray
Write-Host "    Port:     3306" -ForegroundColor Gray
Write-Host "    Database: $DB_DATABASE" -ForegroundColor Gray
Write-Host "    Username: $DB_USER" -ForegroundColor Gray
Write-Host "    Password: $DB_PASSWORD" -ForegroundColor Gray
Write-Host ""
Write-Host "  APP_KEY: $APP_KEY" -ForegroundColor Gray
Write-Host ""

if (-not $DryRun) {
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "  1. Wait for build to complete in Coolify dashboard" -ForegroundColor White
    Write-Host "  2. Point DNS A record: $APP_DOMAIN -> 109.123.239.88" -ForegroundColor White
    Write-Host "  3. Add domain '$APP_DOMAIN' in Coolify app settings for auto-SSL" -ForegroundColor White
    Write-Host "  4. Create admin user via Coolify terminal:" -ForegroundColor White
    Write-Host "     php artisan tinker (then create user and assignRole super_admin)" -ForegroundColor Gray
    Write-Host "  5. Upload lichess_db_puzzle.csv via docker cp if needed" -ForegroundColor White
    Write-Host ""
}

Write-Host "UUIDs for reference:" -ForegroundColor DarkGray
Write-Host "  Team:  $TEAM_ID ($($team.name))" -ForegroundColor DarkGray
Write-Host "  Server: $SERVER_UUID" -ForegroundColor DarkGray
Write-Host "  Project: $PROJECT_UUID" -ForegroundColor DarkGray
Write-Host "  Database: $DB_UUID" -ForegroundColor DarkGray
Write-Host "  Application: $APP_UUID" -ForegroundColor DarkGray
Write-Host ""
