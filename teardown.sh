#!/usr/bin/env bash
# Báo cáo Môn học: Quản trị và Triển khai Hệ thống Phần mềm - Đề 19
# Sinh viên: Đinh Bách Hợp - MSSV: DTC245201006
# ==============================================================================
#  teardown.sh – Gỡ bỏ sạch sẽ toàn bộ hệ thống Đề 19 khi cần làm lại từ đầu
#  Sinh viên: Đinh Bách Hợp – DTC245201006 – Lớp CNTT K23C
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

echo ""
echo -e "${BOLD}${CYAN}============================================================${NC}"
echo -e "${BOLD}${YELLOW}         DỌN DẸP & GỠ BỎ HỆ THỐNG ĐỀ 19 (TEARDOWN)          ${NC}"
echo -e "${BOLD}${CYAN}============================================================${NC}"
echo ""

# 1. Dừng và xóa toàn bộ container, volumes, networks của project
echo -e "${YELLOW}[1/3] Đang dừng và xóa 10 container và volumes...${NC}"
docker compose down -v --remove-orphans 2>/dev/null || sudo docker compose down -v --remove-orphans 2>/dev/null || true

# 2. Xóa các file cache tạm và log cũ
echo -e "${YELLOW}[2/3] Xóa logs và dữ liệu tạm...${NC}"
rm -rf data/grafana/* data/loki/* data/prometheus/* 2>/dev/null || sudo rm -rf data/grafana/* data/loki/* data/prometheus/* 2>/dev/null || true

# 3. Dọn dẹp mạng rác Docker
echo -e "${YELLOW}[3/3] Dọn dẹp mạng nội bộ Docker thừa...${NC}"
docker network prune -f 2>/dev/null || true

echo ""
echo -e "${BOLD}${GREEN}[✔] Đã gỡ bỏ sạch sẽ toàn bộ hệ thống! Máy ảo đã về trạng thái ban đầu.${NC}"
echo -e "Khi muốn khởi động lại từ đầu, bạn chỉ cần chạy: ${BOLD}bash setup.sh${NC}"
echo ""
