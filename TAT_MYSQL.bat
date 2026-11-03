@echo off
set "XAMPP=C:\minhthuc\xampp"
echo Dang tat MySQL...
"%XAMPP%\mysql\bin\mysqladmin.exe" -u root shutdown
if errorlevel 1 (
    echo [LOI] Khong tat duoc bang lenh. Mo XAMPP Control Panel bam Stop o dong MySQL,
    echo hoac mo Task Manager va ket thuc tien trinh mysqld.exe.
) else (
    echo Da gui lenh tat MySQL.
)
echo Doi 5 giay roi kiem tra Task Manager khong con mysqld.exe truoc khi chuyen thu muc.
timeout /t 5 /nobreak >nul
pause
