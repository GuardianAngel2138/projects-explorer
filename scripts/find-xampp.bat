@echo off
:: Projects Explorer: find the XAMPP folder.
:: Used by install.bat and uninstall.bat. Sets XAMPP to the folder that holds htdocs.
:: Order: the path passed as the first argument, then common places on drives A to Z,
:: then asks the user to type it. Leaves XAMPP empty if nothing valid is found.

set "XAMPP="

if not "%~1" == "" (
    if exist "%~1\htdocs\" set "XAMPP=%~1"
    goto :check
)

for %%D in (A B C D E F G H I J K L M N O P Q R S T U V W X Y Z) do (
    if not defined XAMPP if exist "%%D:\xampp\htdocs\" set "XAMPP=%%D:\xampp"
    if not defined XAMPP if exist "%%D:\Program Files\xampp\htdocs\" set "XAMPP=%%D:\Program Files\xampp"
    if not defined XAMPP if exist "%%D:\Program Files (x86)\xampp\htdocs\" set "XAMPP=%%D:\Program Files (x86)\xampp"
)

if not defined XAMPP (
    echo  XAMPP was not found in the usual places.
    echo  Type the full path to your XAMPP folder, for example D:\xampp
    echo.
    set /p "XAMPP=  XAMPP folder: "
    echo.
)

:check
:: Stop here when nothing was found. The lines below must not run on an empty
:: XAMPP: cmd expands %XAMPP:"=% to a stray quote and the script crashes.
if not defined XAMPP exit /b 0
:: Remove any quotes the user typed and a trailing backslash.
set "XAMPP=%XAMPP:"=%"
if "%XAMPP:~-1%" == "\" set "XAMPP=%XAMPP:~0,-1%"
if not exist "%XAMPP%\htdocs\" set "XAMPP="
exit /b 0
