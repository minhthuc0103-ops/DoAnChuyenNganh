@echo off
setlocal
title CHAY APP QUAN LY PHONG TRO

rem ===== Duong dan XAMPP cua ban (da dung theo may ban) =====
set "XAMPP=C:\minhthuc\xampp"
set "PORT=8000"

rem Chay tai thu muc chua file nay (thu muc du an, noi co index.php)
cd /d "%~dp0"

echo ==========================================
echo   CHAY APP QUAN LY PHONG TRO
echo ==========================================
echo Thu muc du an: %CD%
echo.

if not exist "%XAMPP%\php\php.exe" (
    echo [LOI] Khong thay %XAMPP%\php\php.exe
    echo Hay mo file nay bang Notepad va sua dong set "XAMPP=..." cho dung thu muc XAMPP cua ban.
    echo.
    pause
    exit /b 1
)
if not exist "%CD%\index.php" (
    echo [LOI] Khong thay index.php trong thu muc nay.
    echo Hay dat file CHAY_APP.bat cung cho voi index.php cua du an.
    echo.
    pause
    exit /b 1
)

rem --- 1. MySQL (database) ---
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if errorlevel 1 (
    echo [1/3] Dang bat MySQL...
    start "MySQL - dung dong cua so nay" /min "%XAMPP%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP%\mysql\bin\my.ini" --standalone
    echo       Doi MySQL khoi dong 8 giay...
    timeout /t 8 /nobreak >nul
) else (
    echo [1/3] MySQL dang chay san.
)

rem --- 1b. Sua database (tao ql_phong_tro neu chua co + chay migration, chay lai nhieu lan van an toan) ---
echo [1b] Kiem tra va sua database...
call "%~dp0Database.bat" nopause
cd /d "%~dp0"

rem --- 2. May chu PHP ---
netstat -ano | findstr /R /C:":%PORT% .*LISTENING" >nul
if errorlevel 1 (
    echo [2/3] Dang bat may chu PHP tai cong %PORT%...
    start "PHP SERVER - DONG CUA SO NAY DE TAT APP" cmd /k %XAMPP%\php\php.exe -S localhost:%PORT%
    timeout /t 2 /nobreak >nul
) else (
    echo [2/3] Cong %PORT% dang co chuong trinh chay san, bo qua buoc bat may chu.
)

rem --- 3. Mo trinh duyet ---
echo [3/3] Mo trinh duyet: http://localhost:%PORT%/index.php
start "" "http://localhost:%PORT%/index.php"

echo.
echo XONG. Co 2 cua so nho: "MySQL" (thu nho o thanh tac vu) va "PHP SERVER". Dung dong chung khi dang dung app.
echo Neu trang bao loi ket noi database: doi vai giay roi bam F5.
echo Dung xong: dong cua so "PHP SERVER" (tat app), va chay TAT_MYSQL.bat de tat MySQL.
echo.
pause
