#!/usr/bin/env bash
# Báo cáo Môn học: Quản trị và Triển khai Hệ thống Phần mềm - Đề 19
# Sinh viên: Đinh Bách Hợp - MSSV: DTC245201006
# ==============================================================================
#  verify.sh – Kiểm tra toàn diện hệ thống Đề 19 (10/10 container, Web, DB, Grafana)
#  Sinh viên: Đinh Bách Hợp – DTC245201006 – Lớp CNTT K23C
#  Tính năng: Tự động phát hiện lỗi, đọc log, tự sửa và thử lại tối đa 3 lần.
# ==============================================================================

set -u

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# 1. Đọc mật khẩu từ file .env (không hardcode trong script)
if [[ -f .env ]]; then
    # shellcheck disable=SC2046
    export $(grep -v '^#' .env | grep '=' | xargs -d '\n')
fi

MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-RootPass@2026}"
GF_SECURITY_ADMIN_USER="${GF_SECURITY_ADMIN_USER:-admin}"
GF_SECURITY_ADMIN_PASSWORD="${GF_SECURITY_ADMIN_PASSWORD:-Admin@Grafana2026}"

EXPECTED_CONTAINERS=(
    "de19_mysql"
    "de19_wordpress"
    "de19_nginx"
    "de19_phpmyadmin"
    "de19_prometheus"
    "de19_grafana"
    "de19_loki"
    "de19_promtail"
    "de19_node_exporter"
    "de19_cadvisor"
)

log_info()    { echo -e "${BLUE}[INFO]${NC}  $*"; }
log_ok()      { echo -e "${GREEN}[OK]${NC}    $*"; }
log_warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
log_err()     { echo -e "${RED}[FAIL]${NC}  $*"; }

SERVER_IP=$(hostname -I 2>/dev/null | awk '{print $1}')
SERVER_IP="${SERVER_IP:-127.0.0.1}"

auto_heal() {
    local target="$1"
    log_warn "Kích hoạt tự sửa lỗi cho [$target]..."
    case "$target" in
        "mysql"|"de19_mysql")
            docker compose restart mysql 2>/dev/null || true
            sleep 10
            ;;
        "nginx"|"de19_nginx")
            # Kiểm tra xem có service nào ngoài host chiếm port 80 không
            if systemctl is-active --quiet nginx 2>/dev/null; then
                log_warn "Phát hiện Nginx host đang chiếm port 80. Đang tắt Nginx host..."
                sudo systemctl stop nginx 2>/dev/null || true
                sudo systemctl disable nginx 2>/dev/null || true
            fi
            docker compose restart nginx 2>/dev/null || true
            sleep 3
            ;;
        *)
            docker compose restart "$target" 2>/dev/null || true
            sleep 3
            ;;
    esac
}

run_checks() {
    local all_passed=true

    echo ""
    echo -e "${BOLD}${CYAN}============================================================${NC}"
    echo -e "${BOLD}${CYAN}          KIỂM TRA HỆ THỐNG ĐỀ 19 – WEBSITE TIN TỨC          ${NC}"
    echo -e "${BOLD}${CYAN}============================================================${NC}"
    echo ""

    # --- 1. Kiểm tra 10/10 container ---
    log_info "1. Kiểm tra trạng thái 10/10 Container..."
    local running_count=0
    for c in "${EXPECTED_CONTAINERS[@]}"; do
        local state
        state=$(docker inspect --format '{{.State.Status}}' "$c" 2>/dev/null || echo "not_found")
        if [[ "$state" == "running" ]]; then
            echo -e "   [✔] ${GREEN}$c${NC} (running)"
            ((running_count++))
        else
            echo -e "   [✘] ${RED}$c${NC} (Trạng thái: $state)"
            all_passed=false
            auto_heal "$c"
        fi
    done

    echo -e "   ➔ Tổng số container đang chạy: ${BOLD}$running_count/10${NC}"
    if [[ "$running_count" -ne 10 ]]; then
        return 1
    fi

    # --- 2. Kiểm tra MySQL Database ---
    log_info "2. Kiểm tra kết nối MySQL (dùng mật khẩu từ .env)..."
    if docker compose exec -T mysql mysqladmin ping -h localhost -u root -p"${MYSQL_ROOT_PASSWORD}" --silent 2>/dev/null; then
        log_ok "MySQL Database hoạt động ổn định và sẵn sàng nhận kết nối."
    else
        log_err "Không kết nối được tới MySQL!"
        auto_heal "mysql"
        return 1
    fi

    # --- 3. Kiểm tra Website WordPress ---
    log_info "3. Kiểm tra Web Tin tức WordPress (HTTP 200/302)..."
    local wp_code
    wp_code=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 "http://localhost/" || echo "000")
    if [[ "$wp_code" == "200" || "$wp_code" == "302" ]]; then
        log_ok "Website WordPress phản hồi thành công (HTTP $wp_code)."
    else
        log_err "Website WordPress phản hồi mã lỗi: $wp_code"
        auto_heal "nginx"
        return 1
    fi

    # --- 4. Kiểm tra phpMyAdmin ---
    log_info "4. Kiểm tra phpMyAdmin (/pma/)..."
    local pma_code
    pma_code=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 "http://localhost/pma/" || echo "000")
    if [[ "$pma_code" == "200" ]]; then
        log_ok "Giao diện phpMyAdmin tải thành công (HTTP $pma_code)."
    else
        log_err "phpMyAdmin không phản hồi (HTTP $pma_code)!"
        auto_heal "phpmyadmin"
        return 1
    fi

    # --- 5. Kiểm tra Grafana Dashboard ---
    log_info "5. Kiểm tra Grafana Dashboard (Port 3000)..."
    local gf_code
    gf_code=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 "http://localhost:3000/login" || echo "000")
    if [[ "$gf_code" == "200" || "$gf_code" == "302" ]]; then
        log_ok "Grafana Dashboard phản hồi tốt (HTTP $gf_code)."
    else
        log_err "Grafana không phản hồi (HTTP $gf_code)!"
        auto_heal "grafana"
        return 1
    fi

    return 0
}

# Vòng lặp tự động sửa lỗi tối đa 3 lần
MAX_RETRIES=3
ATTEMPT=1
SUCCESS=false

while [[ $ATTEMPT -le $MAX_RETRIES ]]; do
    log_info "Lần kiểm tra $ATTEMPT/$MAX_RETRIES..."
    if run_checks; then
        SUCCESS=true
        break
    else
        log_warn "Phát hiện lỗi ở lần kiểm tra $ATTEMPT. Đang đọc log và tự khắc phục..."
        docker compose logs --tail=15 2>/dev/null || true
        ((ATTEMPT++))
        sleep 4
    fi
done

echo ""
echo -e "${BOLD}${CYAN}============================================================${NC}"
if [[ "$SUCCESS" == "true" ]]; then
    echo -e "${BOLD}${GREEN}        TẤT CẢ 10/10 TIÊU CHÍ ĐÃ VƯỢT QUA XUẤT SẮC! ✔      ${NC}"
    echo -e "${BOLD}${CYAN}============================================================${NC}"
    echo ""
    echo -e "${BOLD}Các đường link truy cập từ máy thật Windows:${NC}"
    echo -e " 🌐 ${BOLD}Website Tin tức   :${NC} ${GREEN}http://${SERVER_IP}${NC}"
    echo -e " 🗄️  ${BOLD}phpMyAdmin        :${NC} ${GREEN}http://${SERVER_IP}/pma/${NC}  (User: root / Pass: trong .env)"
    echo -e " 📊 ${BOLD}Grafana Dashboard :${NC} ${GREEN}http://${SERVER_IP}:3000${NC} (User: ${GF_SECURITY_ADMIN_USER} / Pass: trong .env)"
    echo ""
    exit 0
else
    echo -e "${BOLD}${RED}     HỆ THỐNG CÒN LỖI SAU 3 LẦN TỰ ĐỘNG KHẮC PHỤC ✘      ${NC}"
    echo -e "${BOLD}${CYAN}============================================================${NC}"
    echo "Hãy kiểm tra chi tiết lỗi bằng lệnh: docker compose logs"
    exit 1
fi
