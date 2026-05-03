@echo off
chcp 65001 >nul
title 26班网站 - 本地开发服务器
echo ============================================
echo       26班网站 - 本地开发服务器
echo ============================================
echo.

echo [1/2] 启动 MariaDB + PHP 服务器...
wsl -e bash /mnt/c/Users/华为/Desktop/cat/start-server.sh
if %errorlevel% neq 0 (
    echo [错误] 服务器启动失败
    pause
    exit /b 1
)
echo.

:: 获取 WSL IP
for /f "tokens=*" %%i in ('wsl -e bash -c "hostname -I | awk '{print $1}'"') do set WSL_IP=%%i

echo [2/2] 服务器就绪！
echo.
echo ============================================
echo   访问地址: http://%WSL_IP%:8080
echo   登录页面: http://%WSL_IP%:8080/user/login
echo.
echo   关闭此窗口将停止服务器
echo ============================================
echo.

echo 按任意键停止服务器...
pause >nul

echo.
echo 正在停止服务器...
wsl -e bash /mnt/c/Users/华为/Desktop/cat/stop-server.sh
echo 服务器已停止
