@echo off
:: Projects Explorer: Windows uninstaller.
:: Puts XAMPP's original dashboard page back and removes the explorer folder.
:: Your config.php, if you made one, is kept as projects-explorer-config.php.
:: Optional: pass your XAMPP folder as the first argument, e.g.  uninstall.bat "D:\xampp"
setlocal EnableDelayedExpansion

echo.
echo  Projects Explorer: uninstall
echo  ============================
echo.

call "%~dp0scripts\find-xampp.bat" "%~1"
if not defined XAMPP (
    echo  ERROR: no htdocs folder found there. Check the path and run this again.
    goto :fail
)

set "DASH=!XAMPP!\htdocs\dashboard"
echo  Removing from: !DASH!
echo.

if not exist "!DASH!\index.php.original" (
    echo  ERROR: index.php.original was not found, so XAMPP's page can't be restored.
    echo  Nothing was changed. Reinstalling XAMPP also brings the original dashboard back.
    goto :fail
)

:: Keep the user's settings outside the folder that is about to be removed.
if exist "!DASH!\explorer\config.php" (
    copy /Y "!DASH!\explorer\config.php" "!DASH!\projects-explorer-config.php" >nul
    echo  [OK] Kept your settings as projects-explorer-config.php
)

copy /Y "!DASH!\index.php.original" "!DASH!\index.php" >nul || goto :fail
del "!DASH!\index.php.original"
echo  [OK] Restored XAMPP's original dashboard.

if exist "!DASH!\explorer\" rmdir /S /Q "!DASH!\explorer"
echo  [OK] Removed the explorer folder.

echo.
echo  Done. http://localhost/dashboard/ shows XAMPP's page again.
echo.
pause
exit /b 0

:fail
echo.
pause
exit /b 1
