@echo off
setlocal EnableDelayedExpansion

echo.
echo  Projects Explorer - XAMPP Dashboard Installer
echo  ==============================================
echo.

:: --- Auto-detect XAMPP across common drives and paths ---
set "XAMPP="
for %%D in (C D E F G H) do (
    for %%P in ("%%D:\xampp" "%%D:\XAMPP" "%%D:\Program Files\xampp" "%%D:\Program Files (x86)\xampp") do (
        if exist "%%~P\htdocs" (
            if "!XAMPP!" == "" set "XAMPP=%%~P"
        )
    )
)

if "!XAMPP!" == "" (
    echo  Could not auto-detect XAMPP installation.
    echo  Please enter the full path to your XAMPP folder.
    echo  Example: C:\xampp
    echo.
    set /p XAMPP="  XAMPP path: "
    echo.
)

:: Strip trailing backslash if present
if "!XAMPP:~-1!" == "\" set "XAMPP=!XAMPP:~0,-1!"

set "HTDOCS=!XAMPP!\htdocs"
set "TARGET=!HTDOCS!\dashboard\index.php"
set "BACKUP=!HTDOCS!\dashboard\index.php.bak"
set "SOURCE=%~dp0index.php"

:: --- Validate ---
if not exist "!HTDOCS!" (
    echo  ERROR: htdocs folder not found at !HTDOCS!
    echo  Make sure XAMPP is installed and the path is correct.
    echo.
    pause
    exit /b 1
)

if not exist "!HTDOCS!\dashboard" (
    echo  ERROR: dashboard folder not found at !HTDOCS!\dashboard
    echo  Start XAMPP at least once so it creates the dashboard folder.
    echo.
    pause
    exit /b 1
)

if not exist "%SOURCE%" (
    echo  ERROR: index.php not found next to this installer.
    echo  Make sure install.bat and index.php are in the same folder.
    echo.
    pause
    exit /b 1
)

echo  XAMPP found at : !XAMPP!
echo  Installing to  : !TARGET!
echo.

:: --- Backup original ---
if exist "!TARGET!" (
    copy /Y "!TARGET!" "!BACKUP!" >nul
    echo  [OK] Backed up original dashboard to index.php.bak
)

:: --- Install ---
copy /Y "%SOURCE%" "!TARGET!" >nul
if errorlevel 1 (
    echo  ERROR: Could not copy index.php. Try running as Administrator.
    echo.
    pause
    exit /b 1
)
echo  [OK] Installed Projects Explorer dashboard.

:: --- Create Projects folder if missing ---
set "PROJECTS=!HTDOCS!\Projects"
if not exist "!PROJECTS!" (
    mkdir "!PROJECTS!"
    echo  [OK] Created htdocs\Projects folder.
) else (
    echo  [OK] htdocs\Projects folder already exists.
)

echo.
echo  All done! Open http://localhost in your browser to see your dashboard.
echo.
pause
