#!/bin/bash
# 打包项目用于部署到 Debian 服务器
# 在 WSL 或 Linux 中运行: bash pack.sh
# 产物: cat-deploy.tar.gz

set -e
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_NAME="cat"
OUTPUT_FILE="$SCRIPT_DIR/${PROJECT_NAME}-deploy.tar.gz"

echo "=== 打包项目用于部署 ==="
echo "项目目录: $SCRIPT_DIR"
echo "输出文件: $OUTPUT_FILE"
echo ""

# 进入项目父目录打包，排除不需要的文件
cd "$SCRIPT_DIR"

tar czf "$OUTPUT_FILE" \
    --exclude='.git' \
    --exclude='.env' \
    --exclude='.codebuddy' \
    --exclude='tmp/sessions/sess_*' \
    --exclude='tmp/login_attempts' \
    --exclude='tmp/changepwd_attempts' \
    --exclude='public/static/avatars/*.jpg' \
    --exclude='public/static/avatars/*.png' \
    --exclude='public/static/avatars/*.gif' \
    --exclude='start.bat' \
    --exclude='setup-mirrored-network.bat' \
    --exclude='cat-deploy.tar.gz' \
    --exclude='cat-update.tar.gz' \
    .

echo "[OK] 打包完成: $OUTPUT_FILE"
echo ""
echo "上传到服务器："
echo "  scp cat-deploy.tar.gz youruser@yourserver:/tmp/"
echo ""
echo "在服务器上解压并部署："
echo "  mkdir -p /var/www/cat"
echo "  cd /var/www/cat"
echo "  tar xzf /tmp/cat-deploy.tar.gz"
echo "  cp .env.example .env   # 然后编辑 .env 填入服务器数据库密码"
echo "  bash deploy.sh"
