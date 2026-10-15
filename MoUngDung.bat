@echo off
setlocal

set "PROJECT_DIR=%~dp0"
for %%I in ("%PROJECT_DIR:~0,-1%") do set "PROJECT_NAME=%%~nxI"

set "XAMPP_DIR=C:\xampp"
set "APP_URL=http://localhost/%PROJECT_NAME%/index.php"

echo Dang mo ung dung Quan Ly Phong Tro...
echo.

if exist "%XAMPP_DIR%\xampp_start.exe" (
    echo Dang khoi dong XAMPP...
    start "" /min "%XAMPP_DIR%\xampp_start.exe"
    timeout /t 5 /nobreak >nul
) else if exist "%XAMPP_DIR%\xampp-control.exe" (
    echo Tim thay XAMPP Control Panel. Hay bat Apache va MySQL neu chua chay.
    start "" "%XAMPP_DIR%\xampp-control.exe"
    timeout /t 2 /nobreak >nul
) else (
    echo Khong tim thay XAMPP tai %XAMPP_DIR%.
    echo Hay cai dat XAMPP hoac dat project trong thu muc htdocs roi chay lai file nay.
    echo.
)

echo Mo trinh duyet: %APP_URL%
start "" "%APP_URL%"

echo.
echo Neu trang khong mo duoc, hay kiem tra Apache/MySQL trong XAMPP.
pause
