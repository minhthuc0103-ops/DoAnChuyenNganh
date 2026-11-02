@echo off
setlocal

set "XAMPP_DIR="
for %%D in (C D E F G) do (
    if not defined XAMPP_DIR if exist "%%D:\xampp\apache\bin\httpd.exe" set "XAMPP_DIR=%%D:\xampp"
)
if not defined XAMPP_DIR (
    echo [LOI] Khong tim thay XAMPP o cac o C, D, E, F, G.
    pause
    exit /b 1
)

echo Dang tat MySQL...
"%XAMPP_DIR%\mysql\bin\mysqladmin.exe" -u root shutdown 2>nul

echo Dang tat Apache...
taskkill /IM httpd.exe /F >nul 2>&1

tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if not errorlevel 1 (
    echo [CANH BAO] MySQL chua tat duoc. Hay tat bang XAMPP Control Panel.
) else (
    echo Da tat Apache va MySQL.
)
pause