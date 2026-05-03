#!/bin/bash
# 停止 PHP 开发服务器
if [ -f /tmp/php-server.pid ]; then
    kill $(cat /tmp/php-server.pid) 2>/dev/null
    rm /tmp/php-server.pid
fi
killall php 2>/dev/null
echo "PHP server stopped"
