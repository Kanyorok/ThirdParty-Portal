# Workflow Diagnostic PowerShell Script
# Run this on your Windows server to diagnose workflow issues

Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║      Workflow Diagnostic Tool (PowerShell)                ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Step 1: Current Location
Write-Host "📁 Current Directory:" -ForegroundColor Yellow
Write-Host "   $(Get-Location)" -ForegroundColor White
Write-Host ""

# Step 2: Check .env database settings
Write-Host "🗄️  Database Configuration:" -ForegroundColor Yellow
if (Test-Path ".env") {
    $dbSettings = Get-Content .env | Select-String -Pattern "^DB_"
    foreach ($setting in $dbSettings) {
        # Hide password
        if ($setting -match "DB_PASSWORD") {
            Write-Host "   DB_PASSWORD=********" -ForegroundColor Gray
        } else {
            Write-Host "   $setting" -ForegroundColor Gray
        }
    }
} else {
    Write-Host "   ❌ .env file not found!" -ForegroundColor Red
}
Write-Host ""

# Step 3: Run PHP diagnostic
Write-Host "🔍 Running Workflow Diagnostic..." -ForegroundColor Yellow
Write-Host ""
php scripts\verify_workflows.php
Write-Host ""

# Step 4: Check for export files
Write-Host "📦 Checking Export Files..." -ForegroundColor Yellow
$exportFiles = Get-ChildItem -Filter "workflows_export*.json" -ErrorAction SilentlyContinue

if ($exportFiles) {
    Write-Host "   Found $($exportFiles.Count) export file(s):" -ForegroundColor Green
    foreach ($file in $exportFiles) {
        Write-Host ""
        Write-Host "   File: $($file.Name)" -ForegroundColor Cyan
        Write-Host "   Size: $([math]::Round($file.Length/1KB, 2)) KB" -ForegroundColor Gray
        Write-Host "   Modified: $($file.LastWriteTime)" -ForegroundColor Gray
        
        # Analyze the file
        Write-Host ""
        Write-Host "   Analyzing content..." -ForegroundColor Yellow
        php scripts\verify_workflows.php $file.Name
    }
} else {
    Write-Host "   ⚠️  No export files found" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "   To create an export, run:" -ForegroundColor White
    Write-Host "   php scripts\export_workflows.php" -ForegroundColor Cyan
}
Write-Host ""

# Step 5: Summary and Recommendations
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                    Next Steps                              ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

Write-Host "To export workflows from THIS database:" -ForegroundColor Yellow
Write-Host "   php scripts\export_workflows.php my_workflows.json" -ForegroundColor Cyan
Write-Host ""

Write-Host "To import workflows to ANOTHER database:" -ForegroundColor Yellow
Write-Host "   1. Edit .env file to connect to destination database" -ForegroundColor White
Write-Host "   2. php scripts\import_workflows.php my_workflows.json" -ForegroundColor Cyan
Write-Host ""

Write-Host "To force overwrite existing workflows:" -ForegroundColor Yellow
Write-Host "   php scripts\import_workflows.php my_workflows.json --force" -ForegroundColor Cyan
Write-Host ""

Write-Host "Press any key to continue..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
