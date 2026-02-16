@echo off
REM Workflow Management Batch Script for Windows
REM This provides easy access to workflow export/import tools

:menu
cls
echo ╔════════════════════════════════════════════════════════════╗
echo ║         BR_ERP Workflow Management Tool                   ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
echo Current Directory: %CD%
echo.
echo Choose an option:
echo   1. Export workflows to file
echo   2. Import workflows from file
echo   3. Quick transfer wizard
echo   4. View documentation
echo   5. Exit
echo.
set /p choice="Enter your choice (1-5): "

if "%choice%"=="1" goto export
if "%choice%"=="2" goto import
if "%choice%"=="3" goto quick
if "%choice%"=="4" goto docs
if "%choice%"=="5" goto end
goto menu

:export
cls
echo ╔════════════════════════════════════════════════════════════╗
echo ║                Export Workflows                            ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
set /p filename="Enter output filename (or press Enter for default): "
if "%filename%"=="" (
    php scripts\export_workflows.php
) else (
    php scripts\export_workflows.php "%filename%"
)
echo.
pause
goto menu

:import
cls
echo ╔════════════════════════════════════════════════════════════╗
echo ║                Import Workflows                            ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
set /p filename="Enter input filename: "
if "%filename%"=="" (
    echo Error: Filename required!
    pause
    goto menu
)
if not exist "%filename%" (
    echo Error: File not found: %filename%
    pause
    goto menu
)
echo.
set /p overwrite="Overwrite existing workflows? (Y/N): "
if /i "%overwrite%"=="Y" (
    php scripts\import_workflows.php "%filename%" --force
) else (
    php scripts\import_workflows.php "%filename%"
)
echo.
pause
goto menu

:quick
cls
echo ╔════════════════════════════════════════════════════════════╗
echo ║            Quick Transfer Wizard                           ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
php scripts\quick_workflow_transfer.php
pause
goto menu

:docs
cls
echo ╔════════════════════════════════════════════════════════════╗
echo ║                Documentation                               ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
if exist scripts\WORKFLOW_EXPORT_IMPORT_GUIDE.md (
    type scripts\WORKFLOW_EXPORT_IMPORT_GUIDE.md | more
) else (
    echo Documentation file not found!
    echo Location: scripts\WORKFLOW_EXPORT_IMPORT_GUIDE.md
)
echo.
pause
goto menu

:end
echo.
echo Thank you for using BR_ERP Workflow Management Tool!
echo.
exit /b 0
