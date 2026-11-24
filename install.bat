@echo off
:: Projects Explorer: Windows installer.
:: Copies index.php and the explorer folder into XAMPP's htdocs\dashboard folder.
:: Run it again to update; your explorer\config.php is never overwritten.
:: Optional: pass your XAMPP folder as the first argument, e.g.  install.bat "D:\xampp"
setlocal EnableDelayedExpansion

echo.
echo  Projects Explorer: install
echo  ==========================
echo.

set "SOURCE=%~dp0"
if not exist "%SOURCE%index.php" goto :missing_files
if not exist "%SOURCE%explorer\app.php" goto :missing_files

call "%SOURCE%scripts\find-xampp.bat" "%~1"
if not defined XAMPP (
    echo  ERROR: no htdocs folder found there. Check the path and run this again.
    goto :fail
)

set "DASH=!XAMPP!\htdocs\dashboard"
echo  XAMPP folder : !XAMPP!
echo  Installing to: !DASH!
echo.

if not exist "!DASH!\" (
    mkdir "!DASH!" || goto :copy_failed
    echo  [OK] Created the dashboard folder.
)

:: ---- Back up XAMPP's own dashboard page, once ----
:: index.php.original is the real XAMPP page and is never overwritten.
:: "PV_ENTRY" marks v2 of this dashboard; "dashUrl" marks v1. Neither should be backed up.
if not exist "!DASH!\index.php.original" (
    set "ORIGINAL="
    if exist "!DASH!\index.php" (
        findstr /c:"PV_ENTRY" /c:"dashUrl" "!DASH!\index.php" >nul || set "ORIGINAL=!DASH!\index.php"
    )
    rem v1 kept its backup as index.php.bak; use it if it holds XAMPP's page.
    if not defined ORIGINAL if exist "!DASH!\index.php.bak" (
        findstr /c:"PV_ENTRY" /c:"dashUrl" "!DASH!\index.php.bak" >nul || set "ORIGINAL=!DASH!\index.php.bak"
    )
    if defined ORIGINAL (
        copy /Y "!ORIGINAL!" "!DASH!\index.php.original" >nul || goto :copy_failed
        echo  [OK] Saved XAMPP's original dashboard as index.php.original
    )
) else (
    echo  [OK] XAMPP's original dashboard is already saved as index.php.original
)

:: ---- Copy the dashboard ----
copy /Y "%SOURCE%index.php" "!DASH!\index.php" >nul || goto :copy_failed
:: robocopy: /E copies sub-folders, /XF config.php keeps the user's settings. Exit codes below 8 mean success.
robocopy "%SOURCE%explorer" "!DASH!\explorer" /E /XF config.php /NFL /NDL /NJH /NJS /NP >nul
if errorlevel 8 goto :copy_failed
echo  [OK] Installed Projects Explorer.

echo.
echo  Done. Open http://localhost/dashboard/ in your browser.
echo  To change settings, copy explorer\config.example.php to explorer\config.php
echo  inside !DASH! and edit the copy.
echo.
pause
exit /b 0

:missing_files
echo  ERROR: index.php or the explorer folder is missing next to this installer.
echo  Download the whole project again (Code, Download ZIP), unzip it, then run install.bat.
goto :fail

:copy_failed
echo  ERROR: could not copy the files. Close any program using them, or right-click
echo  install.bat and choose "Run as administrator", then try again.
goto :fail

:fail
echo.
pause
exit /b 1
