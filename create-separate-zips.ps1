# PowerShell script to create separate ZIP files for each folder
Write-Host "Creating separate ZIP files for manual upload..." -ForegroundColor Green

# Define the project name
$projectName = "halovpbank2025"

# Create output directory
$outputDir = "separate-zips"
if (Test-Path $outputDir) {
    Remove-Item $outputDir -Recurse -Force
}
New-Item -ItemType Directory -Path $outputDir | Out-Null

# Define folders and files to zip separately
$items = @(
    @{Name="core-files"; Items=@("index.php", "game.php", "reward.php", "config-directadmin.php")},
    @{Name="admin"; Items=@("admin")},
    @{Name="api"; Items=@("api")},
    @{Name="assets-css"; Items=@("assets/css")},
    @{Name="assets-js"; Items=@("assets/js")},
    @{Name="assets-images"; Items=@("assets/images")},
    @{Name="assets-fonts"; Items=@("assets/fonts")},
    @{Name="database"; Items=@("database-setup-directadmin.sql")}
)

Write-Host "Creating separate ZIP files:" -ForegroundColor Cyan

foreach ($item in $items) {
    $zipName = "$projectName-$($item.Name).zip"
    $zipPath = Join-Path $outputDir $zipName

    Write-Host "`nCreating: $zipName" -ForegroundColor Yellow

    # Create temporary directory for this item
    $tempDir = "temp-$($item.Name)"
    if (Test-Path $tempDir) {
        Remove-Item $tempDir -Recurse -Force
    }
    New-Item -ItemType Directory -Path $tempDir | Out-Null

    # Copy items to temp directory
    foreach ($subItem in $item.Items) {
        if (Test-Path $subItem) {
            if ((Get-Item $subItem) -is [System.IO.DirectoryInfo]) {
                # Copy directory
                Copy-Item $subItem -Destination $tempDir -Recurse
                Write-Host "  Copied directory: $subItem" -ForegroundColor Green
            } else {
                # Copy file
                Copy-Item $subItem -Destination $tempDir
                Write-Host "  Copied file: $subItem" -ForegroundColor Green
            }
        } else {
            Write-Host "  Warning: $subItem not found" -ForegroundColor Red
        }
    }

    # Create ZIP file
    Compress-Archive -Path "$tempDir\*" -DestinationPath $zipPath -Force

    # Clean up temp directory
    Remove-Item $tempDir -Recurse -Force

    # Get file size
    $fileSize = (Get-Item $zipPath).Length
    $fileSizeMB = [math]::Round($fileSize / 1MB, 2)

    Write-Host "  Created: $zipName ($fileSizeMB MB)" -ForegroundColor Cyan
}

Write-Host "`nAll separate ZIP files created successfully!" -ForegroundColor Green
Write-Host "Location: $outputDir" -ForegroundColor White

Write-Host "`nUpload order:" -ForegroundColor Yellow
Write-Host "1. core-files.zip (PHP files + config)" -ForegroundColor White
Write-Host "2. admin.zip (Admin panel)" -ForegroundColor White
Write-Host "3. api.zip (API endpoints)" -ForegroundColor White
Write-Host "4. assets-css.zip (CSS files)" -ForegroundColor White
Write-Host "5. assets-js.zip (JavaScript files)" -ForegroundColor White
Write-Host "6. assets-images.zip (Images)" -ForegroundColor White
Write-Host "7. assets-fonts.zip (Fonts)" -ForegroundColor White
Write-Host "8. database.zip (Database schema)" -ForegroundColor White

Write-Host "`nNote: After uploading core-files.zip, rename config-directadmin.php to config.php" -ForegroundColor Cyan
