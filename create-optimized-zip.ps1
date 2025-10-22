# PowerShell script to create optimized DirectAdmin-compatible ZIP file
Write-Host "Creating optimized DirectAdmin deployment package..." -ForegroundColor Green

# Define the project name and output file
$projectName = "halovpbank2025"
$outputFile = "$projectName-optimized.zip"

# Remove existing ZIP if it exists
if (Test-Path $outputFile) {
    Remove-Item $outputFile -Force
    Write-Host "Removed existing ZIP file" -ForegroundColor Yellow
}

# Files and folders to include (optimized - no large files)
$includeItems = @(
    "admin",
    "api",
    "assets/css",
    "assets/js",
    "assets/images",
    "config-directadmin.php",
    "database-setup-directadmin.sql",
    "game.php",
    "index.php",
    "reward.php"
)

Write-Host "Including optimized files and folders:" -ForegroundColor Cyan
$includeItems | ForEach-Object { Write-Host "  - $_" -ForegroundColor White }

Write-Host "`nExcluding large files:" -ForegroundColor Red
Write-Host "  - assets/designs (PSD files)" -ForegroundColor Red
Write-Host "  - assets/fonts (OTF files)" -ForegroundColor Red
Write-Host "  - database folder" -ForegroundColor Red
Write-Host "  - DEPLOYMENT-GUIDE.md" -ForegroundColor Red

# Create temporary directory
$tempDir = "temp-optimized"
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

Write-Host "`nCreating optimized ZIP file..." -ForegroundColor Yellow

# Create ZIP using Compress-Archive
Compress-Archive -Path "$tempDir\*" -DestinationPath $outputFile -Force

# Clean up temporary directory
Remove-Item $tempDir -Recurse -Force

# Get file size
$fileSize = (Get-Item $outputFile).Length
$fileSizeMB = [math]::Round($fileSize / 1MB, 2)

Write-Host "`nOptimized deployment package created successfully!" -ForegroundColor Green
Write-Host "File: $outputFile" -ForegroundColor White
Write-Host "Size: $fileSizeMB MB" -ForegroundColor White
Write-Host "`nReady for DirectAdmin upload!" -ForegroundColor Green

Write-Host "`nNote: This optimized version excludes:" -ForegroundColor Yellow
Write-Host "- PSD design files" -ForegroundColor White
Write-Host "- Font files (OTF)" -ForegroundColor White
Write-Host "- Database schema folder" -ForegroundColor White
Write-Host "- Documentation files" -ForegroundColor White
Write-Host "`nYou can add fonts later if needed." -ForegroundColor Cyan
