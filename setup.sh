#!/bin/bash
# =============================================================
#  setup.sh – Script tự động deploy toàn bộ stack
#  Đề 19: Website Tin tức – DTC245201006
#  Dùng trên Linux server (Ubuntu 22.04+)
# =============================================================

set -euo pipefail
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; NC='\033[0m'; BOLD='\033[1m'

info()    { echo -e "${BLUE}[INFO]${NC}  $1"; }
success() { echo -e "${GREEN}[OK]${NC}    $1"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

banner() {
cat << 'EOF'
╔══════════════════════════════════════════════════════════╗
║    ĐỀ 19 – WEBSITE TIN TỨC / CỔNG THÔNG TIN            ║
║    Sinh viên: Đinh Bách Hợp – DTC245201006              ║
║    Auto Deploy Script v1.0                              ║
╚══════════════════════════════════════════════════════════╝
EOF
}

# ─── 0. Banner ───
banner

# ─── 1. Kiểm tra OS ───
info "Kiểm tra hệ thống..."
if [[ "$(uname)" != "Linux" ]]; then
    error "Script này chỉ chạy trên Linux!"
fi
success "Linux OS detected"

# ─── 2. Kiểm tra và cài Docker ───
info "Kiểm tra Docker..."
if ! command -v docker &>/dev/null; then
    warn "Docker chưa cài – đang cài..."
    curl -fsSL https://get.docker.com | sh
    sudo usermod -aG docker "$USER"
    success "Docker đã cài. Cần logout/login lại để dùng không cần sudo."
else
    DOCKER_VER=$(docker --version | grep -oP '\d+\.\d+' | head -1)
    success "Docker $DOCKER_VER đã có"
fi

# ─── 3. Kiểm tra Docker Compose ───
info "Kiểm tra Docker Compose..."
if ! docker compose version &>/dev/null 2>&1; then
    warn "Docker Compose v2 chưa có – đang cài plugin..."
    mkdir -p ~/.docker/cli-plugins
    COMPOSE_URL="https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)"
    curl -SL "$COMPOSE_URL" -o ~/.docker/cli-plugins/docker-compose
    chmod +x ~/.docker/cli-plugins/docker-compose
    success "Docker Compose đã cài"
else
    success "Docker Compose đã có"
fi

# ─── 4. Chuẩn bị .env ───
info "Chuẩn bị file .env..."
if [[ ! -f ".env" ]]; then
    if [[ -f ".env.example" ]]; then
        cp .env.example .env
        warn ".env được tạo từ .env.example – hãy đổi mật khẩu mặc định!"
    else
        error "Không tìm thấy .env hoặc .env.example!"
    fi
else
    success ".env đã tồn tại"
fi

# ─── 5. Tạo thư mục cần thiết ───
info "Tạo thư mục cần thiết..."
mkdir -p \
    wordpress/wp-content/uploads \
    wordpress/wp-content/plugins \
    data/mysql \
    data/prometheus \
    data/grafana \
    data/loki
chmod -R 777 data/grafana data/loki 2>/dev/null || true
success "Thư mục đã tạo"

# ─── 6. Pull images ───
info "Pull Docker images (có thể mất vài phút)..."
docker compose pull
success "Đã pull xong tất cả images"

# ─── 7. Khởi động services ───
info "Khởi động services..."
docker compose up -d
success "Tất cả services đã start"

# ─── 8. Chờ MySQL healthy ───
info "Chờ MySQL khởi động (tối đa 60s)..."
TIMEOUT=60
COUNT=0
until docker compose exec -T mysql mysqladmin ping -h localhost --silent 2>/dev/null; do
    COUNT=$((COUNT + 1))
    if [[ $COUNT -ge $TIMEOUT ]]; then
        error "MySQL không khởi động được sau ${TIMEOUT}s!"
    fi
    printf "."
    sleep 1
done
echo ""
success "MySQL đã sẵn sàng"

# ─── 9. Kiểm tra tất cả containers ───
info "Kiểm tra trạng thái containers..."
echo ""
docker compose ps
echo ""

FAILED=$(docker compose ps --status exited --format "{{.Name}}" 2>/dev/null || true)
if [[ -n "$FAILED" ]]; then
    warn "Các container sau đang bị lỗi: $FAILED"
    warn "Xem log: docker compose logs <tên-container>"
fi

# ─── 10. Lấy IP server ───
SERVER_IP=$(hostname -I | awk '{print $1}')

# ─── 11. Hiển thị thông tin truy cập ───
echo ""
echo -e "${BOLD}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║              TRIỂN KHAI HOÀN THÀNH! 🎉                  ║${NC}"
echo -e "${BOLD}╠══════════════════════════════════════════════════════════╣${NC}"
echo -e "${BOLD}║  📰 Website Tin tức  : ${GREEN}http://$SERVER_IP${NC}"
echo -e "${BOLD}║  🔧 WordPress Admin  : ${GREEN}http://$SERVER_IP/wp-admin${NC}"
echo -e "${BOLD}║  🗄️  phpMyAdmin      : ${GREEN}http://pma.localhost${NC}"
echo -e "${BOLD}║  📊 Grafana          : ${GREEN}http://$SERVER_IP:3000${NC}"
echo -e "${BOLD}║     → User: admin    Password: xem file .env${NC}"
echo -e "${BOLD}╠══════════════════════════════════════════════════════════╣${NC}"
echo -e "${BOLD}║  Bước tiếp theo:${NC}"
echo -e "${BOLD}║  1. Truy cập http://$SERVER_IP để cài WordPress${NC}"
echo -e "${BOLD}║  2. sudo bash hardening/hardening.sh  (bảo mật server)${NC}"
echo -e "${BOLD}║  3. Cấu hình domain và HTTPS (tùy chọn)${NC}"
echo -e "${BOLD}╚══════════════════════════════════════════════════════════╝${NC}"
