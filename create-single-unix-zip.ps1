# PowerShell script to create a single Unix-compatible ZIP file
Write-Host "Creating single Unix-compatible ZIP file..." -ForegroundColor Green

# Define the project name and output file
$projectName = "halovpbank2025"
$outputFile = "$projectName-final.zip"

# Remove existing ZIP if it exists
if (Test-Path $outputFile) {
    Remove-Item $outputFile -Force
    Write-Host "Removed existing ZIP file" -ForegroundColor Yellow
}

# Files and folders to include
$includeItems = @(
    "admin",
    "api",
    "assets/css",
    "assets/js",
    "assets/images",
    "assets/fonts",
    "config-directadmin.php",
    "database-setup-directadmin.sql",
    "game.php",
    "index.php",
    "reward.php"
)

Write-Host "Including files and folders:" -ForegroundColor Cyan
$includeItems | ForEach-Object { Write-Host "  - $_" -ForegroundColor White }

# Create temporary directory
$tempDir = "temp-final"
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

Write-Host "`nCreating single ZIP file..." -ForegroundColor Yellow

# Try to use 7-Zip if available
$sevenZipPath = "C:\Program Files\7-Zip\7z.exe"
if (Test-Path $sevenZipPath) {
    Write-Host "Using 7-Zip for Unix-compatible paths..." -ForegroundColor Cyan
    & $sevenZipPath a -tzip $outputFile "$tempDir\*" | Out-Null
    Write-Host "7-Zip created ZIP with Unix paths" -ForegroundColor Green
} else {
    Write-Host "7-Zip not found, using PowerShell Compress-Archive..." -ForegroundColor Yellow
    # Fallback to PowerShell Compress-Archive
    Compress-Archive -Path "$tempDir\*" -DestinationPath $outputFile -Force
    Write-Host "PowerShell created ZIP (may have Windows paths)" -ForegroundColor Yellow
}

# Clean up temporary directory
Remove-Item $tempDir -Recurse -Force

# Get file size
$fileSize = (Get-Item $outputFile).Length
$fileSizeMB = [math]::Round($fileSize / 1MB, 2)

Write-Host "`nSingle deployment package created successfully!" -ForegroundColor Green
Write-Host "File: $outputFile" -ForegroundColor White
Write-Host "Size: $fileSizeMB MB" -ForegroundColor White
Write-Host "`nReady for DirectAdmin upload!" -ForegroundColor Green

if (Test-Path $sevenZipPath) {
    Write-Host "`nThis ZIP uses Unix paths (forward slashes) for DirectAdmin compatibility." -ForegroundColor Yellow
} else {
    Write-Host "`nNote: This ZIP may use Windows paths. If extraction fails, install 7-Zip and run this script again." -ForegroundColor Yellow
    Write-Host "Download 7-Zip from: https://www.7-zip.org/" -ForegroundColor Cyan
}
