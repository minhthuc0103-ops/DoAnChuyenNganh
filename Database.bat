@echo off
setlocal
title SUA DATABASE CHO APP QUAN LY PHONG TRO

rem ===== Duong dan XAMPP cua ban =====
set "XAMPP=C:\minhthuc\xampp"
set "MYSQL=%XAMPP%\mysql\bin\mysql.exe"
set "DUMP=%XAMPP%\mysql\bin\mysqldump.exe"

rem Chay tai thu muc chua file nay: thu muc du an, noi co index.php
cd /d "%~dp0"
set "BACKUP=%CD%\ql_phongtro_backup.sql"

echo ==========================================
echo   SUA DATABASE CHO APP QUAN LY PHONG TRO
echo ==========================================
echo.

if not exist "%MYSQL%" (
    echo [LOI] Khong thay %MYSQL%
    echo Hay sua dong set "XAMPP=..." o dau file nay cho dung thu muc XAMPP.
    pause
    exit /b 1
)
if not exist "backend\database\migrations\migration_columns.sql" (
    echo [LOI] Hay dat file nay trong thu muc du an, cung cho voi index.php.
    pause
    exit /b 1
)

echo [1/4] Kiem tra ket noi MySQL...
"%MYSQL%" -u root -e "SELECT 1" >nul 2>&1
if errorlevel 1 (
    echo [LOI] Khong ket noi duoc MySQL bang tai khoan root khong mat khau.
    echo - Neu MySQL chua bat: chay CHAY_APP.bat truoc, doi 10 giay roi chay lai file nay.
    echo - Neu root co mat khau: cho minh biet.
    pause
    exit /b 1
)
echo       MySQL OK.

echo [2/4] Kiem tra ten database va tinh trang bang...
set "HAS_NEW=0"
set "HAS_OLD=0"
set "NEW_OK=0"
set "OLD_OK=0"
"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='ql_phong_tro'" > "%TEMP%\has_new.txt" 2>nul
set /p HAS_NEW=<"%TEMP%\has_new.txt"
"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='ql_phongtro'" > "%TEMP%\has_old.txt" 2>nul
set /p HAS_OLD=<"%TEMP%\has_old.txt"
if "%HAS_NEW%"=="1" (
    "%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM ql_phong_tro.account" >nul 2>&1
    if not errorlevel 1 set "NEW_OK=1"
)
if "%HAS_OLD%"=="1" (
    "%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM ql_phongtro.account" >nul 2>&1
    if not errorlevel 1 set "OLD_OK=1"
)
echo       ql_phong_tro  la ten app dang dung: co=%HAS_NEW%  dung duoc=%NEW_OK%
echo       ql_phongtro   la ten cu cua ban:    co=%HAS_OLD%  dung duoc=%OLD_OK%

rem --- Phat hien bang bi hong do doi ten thu muc database trong mysql\data ---
if "%HAS_NEW%"=="1" if "%NEW_OK%"=="0" (
    echo.
    echo [LOI] Database ql_phong_tro CO TON TAI nhung cac bang bi hong, loi doesn't exist in engine.
    echo       Nguyen nhan: thu muc database bi doi ten hoac chep tay trong mysql\data.
    echo       DU LIEU CHUA MAT. Lam theo cac buoc sau, khong can xoa gi:
    echo.
    echo   1. Chay TAT_MYSQL.bat va doi 5 giay. Mo Task Manager, khong con mysqld.exe moi tiep tuc.
    echo   2. Mo thu muc %XAMPP%\mysql\data
    echo   3. Neu co thu muc ql_phong_tro nhung KHONG co ql_phongtro:
    echo        doi ten ql_phong_tro thanh ql_phongtro, tuc la doi lai ten cu.
    echo      Neu co CA HAI thu muc: cat ql_phong_tro ra Desktop. Dung xoa.
    echo   4. Chay CHAY_APP.bat, doi 10 giay, roi chay lai file nay.
    echo.
    pause
    exit /b 1
)
if "%HAS_NEW%"=="0" if "%HAS_OLD%"=="1" if "%OLD_OK%"=="0" (
    echo.
    echo [LOI] Database ql_phongtro cung bi hong. Lam theo cac buoc o tren, doi ten lai cho dung roi chay lai.
    echo       Neu van khong duoc, cat ca hai thu muc ql_phongtro va ql_phong_tro ra Desktop.
    echo       Khi do file nay se tao moi tu file ql_phong_tro.sql, dung du lieu mau cua du an.
    echo.
    pause
    exit /b 1
)

echo [3/4] Chuan bi database ql_phong_tro...
if "%HAS_NEW%"=="1" (
    echo       Da co ql_phong_tro dung duoc, giu nguyen du lieu.
) else if "%HAS_OLD%"=="1" (
    echo       Sao chep tu ql_phongtro sang ql_phong_tro. Database cu van giu nguyen.
    "%DUMP%" -u root --default-character-set=utf8mb4 ql_phongtro > "%BACKUP%"
    if errorlevel 1 (
        echo [LOI] Khong sao luu duoc ql_phongtro.
        pause
        exit /b 1
    )
    "%MYSQL%" -u root -e "CREATE DATABASE ql_phong_tro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    if errorlevel 1 (
        echo [LOI] Khong tao duoc database ql_phong_tro.
        pause
        exit /b 1
    )
    "%MYSQL%" -u root --default-character-set=utf8mb4 ql_phong_tro < "%BACKUP%"
    if errorlevel 1 (
        echo [LOI] Nhap du lieu vao ql_phong_tro that bai.
        pause
        exit /b 1
    )
    echo       Da sao chep xong. File sao luu: %BACKUP%
) else (
    echo       Chua co database nao. Tao moi ql_phong_tro tu file backend\database\ql_phong_tro.sql
    "%MYSQL%" -u root -e "CREATE DATABASE ql_phong_tro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    "%MYSQL%" -u root --default-character-set=utf8mb4 ql_phong_tro < "backend\database\ql_phong_tro.sql"
    if errorlevel 1 (
        echo [LOI] Nhap file ql_phong_tro.sql that bai.
        pause
        exit /b 1
    )
)

echo [4/4] Cap nhat cot va bang moi, co the chay lai nhieu lan...
"%MYSQL%" -u root --default-character-set=utf8mb4 ql_phong_tro < "backend\database\migrations\migration_columns.sql"
if errorlevel 1 (
    echo [LOI] migration_columns.sql that bai.
    pause
    exit /b 1
)
"%MYSQL%" -u root --default-character-set=utf8mb4 ql_phong_tro < "backend\database\migrations\upgrade_roommate_requests.sql"
if errorlevel 1 (
    echo [LOI] upgrade_roommate_requests.sql that bai.
    pause
    exit /b 1
)

echo.
echo ------------------ KET QUA ------------------
"%MYSQL%" -u root -N -e "SELECT CONCAT('So bang trong ql_phong_tro: ', COUNT(*), '   mong doi 20') FROM information_schema.tables WHERE table_schema='ql_phong_tro'"
"%MYSQL%" -u root -N -e "SELECT CONCAT('Cot account_status co hay chua: ', COUNT(*), '   mong doi 1') FROM information_schema.columns WHERE table_schema='ql_phong_tro' AND table_name='account' AND column_name='account_status'"
"%MYSQL%" -u root -N -e "SELECT CONCAT('So tai khoan: ', COUNT(*)) FROM ql_phong_tro.account"
echo.
echo XONG. Quay lai trinh duyet va bam F5.
echo.
pause
