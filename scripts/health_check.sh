#!/usr/bin/env bash
# ==============================================================================
# health_check.sh - Kiểm tra sức khỏe hệ thống Đề 19 (DTC245201006)
# ==============================================================================

cd /home/ubuntu/dtc245201006 || exit 1

echo "=== KIỂM TRA SỨC KHỎE HỆ THỐNG ĐỀ 19 ==="
echo "[1] Trạng thái 10 Docker Containers:"
docker compose ps

echo ""
echo "[2] Kiểm tra phản hồi HTTP / HTTPS:"
echo -n "  - HTTP  (Port 80) : " && curl -s -o /dev/null -w "%{http_code}\n" http://localhost/
echo -n "  - HTTPS (Port 443): " && curl -k -s -o /dev/null -w "%{http_code}\n" https://localhost/
echo -n "  - phpMyAdmin      : " && curl -s -o /dev/null -w "%{http_code}\n" http://localhost/pma/
echo -n "  - Grafana (:3000) : " && curl -s -o /dev/null -w "%{http_code}\n" http://localhost:3000

echo ""
echo "[3] Kiểm tra tài nguyên hệ thống:"
free -h
df -h /
