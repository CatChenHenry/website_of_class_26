#!/bin/bash
# Debian 服务器部署脚本
# 用法: bash deploy.sh [项目目录]
# 示例: bash deploy.sh /var/www/cat

set -e

PROJECT_DIR="${1:-$(cd "$(dirname "$0")" && pwd)}"
echo "=== 26班网站部署 ==="
echo "项目目录: $PROJECT_DIR"
echo ""

# 1. 检查 PHP 和必要扩展
echo "[1/5] 检查 PHP 环境..."
if ! command -v php &> /dev/null; then
    echo "[错误] 未找到 PHP，请先安装："
    echo "  sudo apt install php php-mysql php-mbstring php-xml"
    exit 1
fi

# 检查 fileinfo 扩展（mime_content_type 依赖）
if ! php -m | grep -qi fileinfo; then
    echo "[警告] PHP fileinfo 扩展未启用，头像上传功能可能异常"
    echo "  安装: sudo apt install php-fileinfo"
fi

# 检查 mbstring 扩展
if ! php -m | grep -qi mbstring; then
    echo "[警告] PHP mbstring 扩展未启用，字符串处理可能异常"
    echo "  安装: sudo apt install php-mbstring"
fi

# 检查 pdo_mysql 扩展
if ! php -m | grep -qi pdo_mysql; then
    echo "[警告] PHP pdo_mysql 扩展未启用，数据库连接将失败"
    echo "  安装: sudo apt install php-mysql"
fi

echo "  PHP 版本: $(php -v | head -1)"

# 2. 创建必要目录
echo "[2/5] 创建运行时目录..."
mkdir -p "$PROJECT_DIR/tmp/sessions"
mkdir -p "$PROJECT_DIR/public/static/avatars"
mkdir -p "$PROJECT_DIR/public/static/videos"

# 3. 设置目录权限（www-data 为 Apache/Nginx 常用用户）
echo "[3/5] 设置目录权限..."
WEB_USER="www-data"
# 如果使用 PHP 内置服务器，当前用户即为运行用户
if [ -n "$SUDO_USER" ]; then
    WEB_USER="$SUDO_USER"
fi

chmod 700 "$PROJECT_DIR/tmp"
chmod 700 "$PROJECT_DIR/tmp/sessions"
chown -R $WEB_USER:$WEB_USER "$PROJECT_DIR/tmp" 2>/dev/null || true

chmod 755 "$PROJECT_DIR/public/static/avatars"
chown -R $WEB_USER:$WEB_USER "$PROJECT_DIR/public/static/avatars" 2>/dev/null || true

chmod 755 "$PROJECT_DIR/public/static/videos"
chown -R $WEB_USER:$WEB_USER "$PROJECT_DIR/public/static/videos" 2>/dev/null || true

# 确保默认头像存在
if [ ! -f "$PROJECT_DIR/public/static/avatars/default.jpg" ]; then
    echo "[警告] 默认头像 default.jpg 不存在，请手动添加"
fi

# 4. 检查 .env 文件
echo "[4/5] 检查配置文件..."
if [ ! -f "$PROJECT_DIR/.env" ]; then
    echo "[警告] .env 文件不存在，将使用默认数据库配置"
    echo "  请复制 .env.example 并修改："
    echo "  cp .env.example .env"
fi

# 5. 保护敏感文件
echo "[5/5] 保护敏感文件..."
chmod 600 "$PROJECT_DIR/.env" 2>/dev/null || true

echo ""
echo "=== 部署完成 ==="
echo ""
echo "启动方式："
echo "  开发模式: cd $PROJECT_DIR/public && php -S 0.0.0.0:8080 router.php"
echo "  生产模式: 建议使用 Nginx + PHP-FPM"
echo ""
echo "注意事项："
echo "  - 确保 MariaDB/MySQL 已启动且数据库已创建"
echo "  - .env 中的数据库密码已正确配置"
echo "  - 生产环境请关闭 PHP 错误显示（php.ini: display_errors = Off）"
