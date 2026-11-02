@echo off
setlocal

set "PROJECT_DIR=%~dp0"
for %%I in ("%PROJECT_DIR:~0,-1%") do set "PROJECT_NAME=%%~nxI"
set "APP_URL=http://localhost/%PROJECT_NAME%/index.php"

echo Dang mo ung dung Quan Ly Phong Tro...
echo.

rem --- 1. Tim thu muc XAMPP (o C, D, E, F, G) ---
set "XAMPP_DIR="
for %%D in (C D E F G) do (
    if not defined XAMPP_DIR if exist "%%D:\xampp\apache\bin\httpd.exe" set "XAMPP_DIR=%%D:\xampp"
)
if not defined XAMPP_DIR (
    echo [LOI] Khong tim thay XAMPP o cac o C, D, E, F, G.
    echo Hay cai XAMPP roi chay lai file nay.
    echo.
    pause
    exit /b 1
)
echo Tim thay XAMPP tai: %XAMPP_DIR%

if not exist "%XAMPP_DIR%\htdocs\%PROJECT_NAME%\index.php" (
    echo [CANH BAO] Du an chua nam trong %XAMPP_DIR%\htdocs\%PROJECT_NAME%
    echo Trinh duyet co the bao loi 404. Hay chep thu muc du an vao htdocs.
    echo.
)

rem --- 2. Apache (chay PHP) ---
tasklist /FI "IMAGENAME eq httpd.exe" 2>nul | find /I "httpd.exe" >nul
if errorlevel 1 (
    echo Dang bat Apache...
    start "XAMPP Apache" /min /D "%XAMPP_DIR%\apache\bin" "%XAMPP_DIR%\apache\bin\httpd.exe"
) else (
    echo Apache dang chay san.
)

rem --- 3. MySQL (giu database) ---
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if errorlevel 1 (
    echo Dang bat MySQL...
    start "XAMPP MySQL" /min /D "%XAMPP_DIR%\mysql\bin" "%XAMPP_DIR%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_DIR%\mysql\bin\my.ini" --standalone
) else (
    echo MySQL dang chay san.
)

rem --- 4. Cho Apache (cong 80) va MySQL (cong 3306) san sang, toi da 30 giay moi cai ---
echo Cho Apache va MySQL san sang...
powershell -NoProfile -Command "foreach($p in 80,3306){for($i=0;$i -lt 30;$i++){try{$c=New-Object Net.Sockets.TcpClient('127.0.0.1',$p);$c.Close();break}catch{Start-Sleep 1}}}"

rem --- 5. Mo trinh duyet ---
echo Mo trinh duyet: %APP_URL%
start "" "%APP_URL%"

echo.
echo Xong. Dung xong thi chay file DungUngDung.bat de tat Apache va MySQL.
echo Neu trang bao loi ket noi database, doi vai giay roi bam F5.
pause