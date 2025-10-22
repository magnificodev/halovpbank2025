# PowerShell script to create Unix-compatible ZIP file
Write-Host "Creating Unix-compatible DirectAdmin deployment package..." -ForegroundColor Green

# Define the project name and output file
$projectName = "halovpbank2025"
$outputFile = "$projectName-unix.zip"

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
$tempDir = "temp-unix"
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

Write-Host "`nCreating Unix-compatible ZIP file..." -ForegroundColor Yellow

# Load .NET compression assembly
Add-Type -AssemblyName System.IO.Compression.FileSystem

# Create ZIP file with Unix paths
$zip = [System.IO.Compression.ZipFile]::Open($outputFile, [System.IO.Compression.ZipArchiveMode]::Create)

# Get all files in temp directory
$files = Get-ChildItem -Path $tempDir -Recurse -File

foreach ($file in $files) {
    # Convert Windows path to Unix path
    $relativePath = $file.FullName.Substring($tempDir.Length + 1).Replace('\', '/')

    # Create entry in ZIP with Unix path
    $entry = $zip.CreateEntry($relativePath)
    $entryStream = $entry.Open()

    # Copy file content
    $fileStream = [System.IO.File]::OpenRead($file.FullName)
    $fileStream.CopyTo($entryStream)

    # Close streams
    $fileStream.Close()
    $entryStream.Close()

    Write-Host "  Added: $relativePath" -ForegroundColor Cyan
}

$zip.Dispose()

# Clean up temporary directory
Remove-Item $tempDir -Recurse -Force

# Get file size
$fileSize = (Get-Item $outputFile).Length
$fileSizeMB = [math]::Round($fileSize / 1MB, 2)

Write-Host "`nUnix-compatible deployment package created successfully!" -ForegroundColor Green
Write-Host "File: $outputFile" -ForegroundColor White
Write-Host "Size: $fileSizeMB MB" -ForegroundColor White
Write-Host "`nReady for DirectAdmin upload!" -ForegroundColor Green

Write-Host "`nThis version uses Unix paths (forward slashes) for DirectAdmin compatibility." -ForegroundColor Yellow
