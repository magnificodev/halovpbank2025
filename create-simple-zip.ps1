# Simple PowerShell script to create DirectAdmin-compatible ZIP file
Write-Host "Creating DirectAdmin deployment package..." -ForegroundColor Green

# Define the project name and output file
$projectName = "halovpbank2025"
$outputFile = "$projectName-directadmin.zip"

# Remove existing ZIP if it exists
if (Test-Path $outputFile) {
    Remove-Item $outputFile -Force
    Write-Host "Removed existing ZIP file" -ForegroundColor Yellow
}

# Files and folders to include
$includeItems = @(
    "admin",
    "api",
    "assets",
    "database",
    "config-directadmin.php",
    "database-setup-directadmin.sql",
    "DEPLOYMENT-GUIDE.md",
    "game.php",
    "index.php",
    "reward.php"
)

Write-Host "Including files and folders:" -ForegroundColor Cyan
$includeItems | ForEach-Object { Write-Host "  - $_" -ForegroundColor White }

# Create temporary directory
$tempDir = "temp-deploy"
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $tempDir | Out-Null

Write-Host "`nCopying files to temporary directory..." -ForegroundColor Yellow

# Copy included items
foreach ($item in $includeItems) {
    if (Test-Path $item) {
        if ((Get-Item $item) -is [System.IO.DirectoryInfo]) {
            # Copy directory
            Copy-Item $item -Destination $tempDir -Recurse
            Write-Host "  Copied directory: $item" -ForegroundColor Green
        } else {
            # Copy file
            Copy-Item $item -Destination $tempDir
            Write-Host "  Copied file: $item" -ForegroundColor Green
        }
    } else {
        Write-Host "  Warning: $item not found" -ForegroundColor Red
    }
}

# Rename config file for DirectAdmin
if (Test-Path "$tempDir/config-directadmin.php") {
    Copy-Item "$tempDir/config-directadmin.php" "$tempDir/config.php"
    Write-Host "  Created config.php from config-directadmin.php" -ForegroundColor Green
}

Write-Host "`nCreating ZIP file..." -ForegroundColor Yellow

# Create ZIP using Compress-Archive
Compress-Archive -Path "$tempDir\*" -DestinationPath $outputFile -Force

# Clean up temporary directory
Remove-Item $tempDir -Recurse -Force

# Get file size
$fileSize = (Get-Item $outputFile).Length
$fileSizeMB = [math]::Round($fileSize / 1MB, 2)

Write-Host "`nDeployment package created successfully!" -ForegroundColor Green
Write-Host "File: $outputFile" -ForegroundColor White
Write-Host "Size: $fileSizeMB MB" -ForegroundColor White
Write-Host "`nReady for DirectAdmin upload!" -ForegroundColor Green

Write-Host "`nNext steps:" -ForegroundColor Yellow
Write-Host "1. Upload $outputFile to DirectAdmin File Manager" -ForegroundColor White
Write-Host "2. Extract the ZIP file" -ForegroundColor White
Write-Host "3. Create database vpbankgame_halovpbank" -ForegroundColor White
Write-Host "4. Create database user vpbankgame_user" -ForegroundColor White
Write-Host "5. Import database-setup-directadmin.sql" -ForegroundColor White
Write-Host "6. Test the application" -ForegroundColor White
