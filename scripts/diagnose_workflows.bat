@echo off
REM Workflow Diagnostic Script for Windows
REM Run this on your Windows server to diagnose workflow export issues

echo ========================================================
echo    Workflow Diagnostic Tool for Windows
echo ========================================================
echo.

echo Step 1: Checking current directory...
cd
echo.

echo Step 2: Verifying PHP installation...
php --version
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: PHP not found!
    pause
    exit /b 1
)
echo.

echo Step 3: Running workflow diagnostic...
echo.
php scripts\verify_workflows.php
echo.

echo Step 4: Checking for export files...
dir /b workflows_export*.json 2>nul
if %ERRORLEVEL% EQU 0 (
    echo.
    echo Found export files. Analyzing...
    for %%f in (workflows_export*.json) do (
        echo.
        echo Analyzing: %%f
        php scripts\verify_workflows.php %%f
    )
) else (
    echo No export files found.
    echo.
    echo To create an export, run:
    echo   php scripts\export_workflows.php
)
echo.

pause
