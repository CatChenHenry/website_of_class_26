#!/bin/bash
# 启动 PHP 开发服务器
# 自动检测项目根目录（兼容 WSL 挂载路径和原生 Linux 路径）
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# WSL 下脚本在 /mnt/c/... 路径，Linux 下在正常路径
if [[ "$SCRIPT_DIR" == /mnt/* ]]; then
    PROJECT_DIR="$SCRIPT_DIR"
else
    PROJECT_DIR="$SCRIPT_DIR"
fi
cd "$PROJECT_DIR/public"

# 杀掉可能残留的旧进程
killall php 2>/dev/null
sleep 1

# 启动 MariaDB
sudo service mariadb start 2>/dev/null || sudo service mysql start 2>/dev/null

# 使用 nohup 启动 PHP 服务器（脱离终端）
nohup php -S 0.0.0.0:8080 router.php > /tmp/php-server.log 2>&1 &
echo $! > /tmp/php-server.pid
sleep 2

# 验证是否启动成功
if kill -0 $(cat /tmp/php-server.pid) 2>/dev/null; then
    echo "OK: PHP server started on port 8080 (PID: $(cat /tmp/php-server.pid))"
    if [[ "$SCRIPT_DIR" == /mnt/* ]]; then
        # WSL 环境：显示 WSL IP 供 Windows 浏览器访问
        WSL_IP=$(hostname -I | awk '{print $1}')
        echo "URL: http://${WSL_IP}:8080"
    else
        # 原生 Linux：直接用 localhost
        echo "URL: http://localhost:8080"
    fi
else
    echo "FAIL: PHP server failed to start"
    cat /tmp/php-server.log
    exit 1
fi
