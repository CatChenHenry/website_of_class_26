@echo off
chcp 65001 >nul
echo ============================================
echo   配置 WSL2 镜像网络模式
echo   配置后可通过 localhost:8080 直接访问
echo ============================================
echo.
echo 此操作需要管理员权限，将创建/修改：
echo   %USERPROFILE%\.wslconfig
echo.
echo 配置完成后需要重启 WSL 才能生效。
echo.

set WSLCONFIG=%USERPROFILE%\.wslconfig

if exist "%WSLCONFIG%" (
    echo [!] 检测到已有 .wslconfig 文件：
    type "%WSLCONFIG%"
    echo.
    echo 将在其中添加 networkingMode=mirrored 配置
    echo.
)

echo 正在写入配置...
(
echo [wsl2]
echo networkingMode=mirrored
) > "%WSLCONFIG%"

echo [OK] .wslconfig 已创建
echo.
echo 现在需要重启 WSL 使配置生效：
echo   1. 保存所有 WSL 中的工作
echo   2. 在管理员 PowerShell 中运行: wsl --shutdown
echo   3. 重新运行 start.bat
echo.
echo 重启后即可通过 http://localhost:8080 访问网站
echo.
pause
