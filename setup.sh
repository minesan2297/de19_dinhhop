#!/usr/bin/env bash
# Báo cáo Môn học: Quản trị và Triển khai Hệ thống Phần mềm - Đề 19
# Sinh viên: Đinh Bách Hợp - MSSV: DTC245201006
# ==============================================================================
#  setup.sh – Tự động triển khai 100% toàn bộ hệ thống Đề 19 (One-Command Deploy)
#  Đề 19: Website Tin tức / Cổng thông tin – Lớp CNTT K23C
#  Sinh viên: Đinh Bách Hợp – DTC245201006
#  Đặc điểm: Chạy lại nhiều lần không lỗi, hoàn toàn tự động (Non-interactive)
# ==============================================================================

set -euo pipefail
export DEBIAN_FRONTEND=noninteractive

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

log_info()    { echo -e "${BLUE}[INFO]${NC}  $*"; }
log_ok()      { echo -e "${GREEN}[OK]${NC}    $*"; }
log_warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
log_err()     { echo -e "${RED}[ERROR]${NC} $*"; exit 1; }

echo ""
echo -e "${BOLD}${CYAN}============================================================${NC}"
echo -e "${BOLD}${CYAN}   ĐỀ 19: WEBSITE TIN TỨC / CỔNG THÔNG TIN – CNTT K23C      ${NC}"
echo -e "${BOLD}${CYAN}   Sinh viên: Đinh Bách Hợp – Mã SV: DTC245201006           ${NC}"
echo -e "${BOLD}${CYAN}   Tự động triển khai hệ thống (setup.sh v2.0)             ${NC}"
echo -e "${BOLD}${CYAN}============================================================${NC}"
echo ""

# --- 1. Kiểm tra môi trường Linux ---
log_info "1/7. Kiểm tra hệ điều hành..."
if [[ "$(uname -s)" != "Linux" ]]; then
    log_err "Script này chỉ hỗ trợ chạy trên hệ điều hành Linux/Ubuntu!"
fi
log_ok "Hệ điều hành Linux hợp lệ: $(uname -m)"

# --- 2. Cài đặt các gói phần mềm cơ bản (Non-interactive) ---
log_info "2/7. Cập nhật và cài đặt công cụ cần thiết (curl, git, openssh)..."
sudo apt-get update -y -qq
sudo apt-get install -y -qq curl git openssh-server ca-certificates gnupg

# Đảm bảo SSH service luôn chạy để hỗ trợ kết nối
sudo systemctl enable --now ssh 2>/dev/null || true
log_ok "Các gói phần mềm cơ bản đã sẵn sàng."

# --- 3. Cài đặt Docker & Docker Compose (Nếu chưa có) ---
log_info "3/7. Kiểm tra Docker & Docker Compose Engine..."
if ! command -v docker &>/dev/null; then
    log_info "Đang cài đặt Docker Engine chính thức..."
    curl -fsSL https://get.docker.com | sudo sh
    sudo usermod -aG docker "$USER" 2>/dev/null || true
fi

# Đảm bảo service Docker đang hoạt động
sudo systemctl enable --now docker 2>/dev/null || true
sudo systemctl start docker 2>/dev/null || true
log_ok "Docker Engine hoạt động tốt: $(docker --version 2>/dev/null || sudo docker --version)"

# --- 4. Chuẩn bị file cấu hình môi trường .env ---
log_info "4/7. Chuẩn bị cấu hình môi trường (.env)..."
if [[ ! -f ".env" ]]; then
    if [[ -f ".env.example" ]]; then
        cp .env.example .env
        log_ok "Đã tạo file .env từ template .env.example."
    else
        log_err "Không tìm thấy file .env hoặc .env.example!"
    fi
else
    log_ok "File .env đã tồn tại sẵn, giữ nguyên thông tin cấu hình."
fi

# Nạp thông tin cấu hình từ .env (không hardcode mật khẩu trong script)
# shellcheck disable=SC2046
export $(grep -v '^#' .env | grep '=' | xargs -d '\n')

# --- 5. Khởi tạo thư mục và phân quyền ---
log_info "5/7. Tạo thư mục dữ liệu và phân quyền hệ thống..."
sudo mkdir -p \
    wordpress/wp-content/uploads \
    wordpress/wp-content/plugins \
    data/mysql \
    data/prometheus \
    data/grafana \
    data/loki \
    /var/log/nginx

sudo chmod -R 777 data/grafana data/loki /var/log/nginx 2>/dev/null || true
log_ok "Phân quyền thư mục hoàn tất."

# --- 6. Tải và khởi động 10 container Docker ---
log_info "6/7. Tải và khởi động 10 container Docker..."
# Kiểm tra nếu Nginx host vô tình chạy thì tắt để tránh chiếm port 80
if systemctl is-active --quiet nginx 2>/dev/null; then
    log_warn "Phát hiện Nginx host đang chạy, đang tắt để giải phóng port 80..."
    sudo systemctl stop nginx 2>/dev/null || true
    sudo systemctl disable nginx 2>/dev/null || true
fi

docker compose pull -q 2>/dev/null || sudo docker compose pull -q 2>/dev/null || true
docker compose up -d --remove-orphans 2>/dev/null || sudo docker compose up -d --remove-orphans

log_ok "10 container đã được khởi chạy."

# --- 7. Chạy script kiểm thử và tự sửa lỗi verify.sh ---
log_info "7/7. Bắt đầu kiểm tra và xác minh hệ thống..."
chmod +x verify.sh teardown.sh
./verify.sh
