# PowerShell script to create minimal DirectAdmin-compatible ZIP file
Write-Host "Creating minimal DirectAdmin deployment package..." -ForegroundColor Green

# Define the project name and output file
$projectName = "halovpbank2025"
$outputFile = "$projectName-minimal.zip"

# Remove existing ZIP if it exists
if (Test-Path $outputFile) {
    Remove-Item $outputFile -Force
    Write-Host "Removed existing ZIP file" -ForegroundColor Yellow
}

# Files and folders to include (minimal - only essential files)
$includeItems = @(
    "admin",
    "api",
    "assets/css",
    "assets/js",
    "assets/images/background-1.png",
    "assets/images/background-2.png",
    "assets/images/background-3.png",
    "assets/images/background.png",
    "assets/images/checked-box.png",
    "assets/images/no-checked-box.png",
    "assets/images/scan-button.png",
    "assets/images/claim-button.png",
    "assets/images/join-button.png",
    "config-directadmin.php",
    "database-setup-directadmin.sql",
    "game.php",
    "index.php",
    "reward.php"
)

Write-Host "Including minimal essential files:" -ForegroundColor Cyan
$includeItems | ForEach-Object { Write-Host "  - $_" -ForegroundColor White }

Write-Host "`nExcluding all large files:" -ForegroundColor Red
Write-Host "  - assets/designs (PSD files)" -ForegroundColor Red
Write-Host "  - assets/fonts (OTF files)" -ForegroundColor Red
Write-Host "  - database folder" -ForegroundColor Red
Write-Host "  - DEPLOYMENT-GUIDE.md" -ForegroundColor Red
Write-Host "  - All documentation" -ForegroundColor Red

# Create temporary directory
$tempDir = "temp-minimal"
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

Write-Host "`nCreating minimal ZIP file..." -ForegroundColor Yellow

# Create ZIP using Compress-Archive
Compress-Archive -Path "$tempDir\*" -DestinationPath $outputFile -Force

# Clean up temporary directory
Remove-Item $tempDir -Recurse -Force

# Get file size
$fileSize = (Get-Item $outputFile).Length
$fileSizeMB = [math]::Round($fileSize / 1MB, 2)

Write-Host "`nMinimal deployment package created successfully!" -ForegroundColor Green
Write-Host "File: $outputFile" -ForegroundColor White
Write-Host "Size: $fileSizeMB MB" -ForegroundColor White
Write-Host "`nReady for DirectAdmin upload!" -ForegroundColor Green

Write-Host "`nNote: This minimal version includes only:" -ForegroundColor Yellow
Write-Host "- Core PHP files" -ForegroundColor White
Write-Host "- Essential CSS/JS" -ForegroundColor White
Write-Host "- Required images only" -ForegroundColor White
Write-Host "- Database setup file" -ForegroundColor White
Write-Host "`nFonts and designs can be added later if needed." -ForegroundColor Cyan
