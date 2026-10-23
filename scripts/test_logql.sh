#!/usr/bin/env bash
# ==============================================================================
# test_logql.sh - Kịch bản kiểm tra và trích xuất dữ liệu qua Loki LogQL API
# Đề 19: Website Tin tức - DTC245201006 (CNTT K23C)
# ==============================================================================

LOKI_URL="http://localhost:3100"

echo "=================================================================="
echo " [GIÁM SÁT LOG] KIỂM TRA TRUY VẤN LOGQL QUA LOKI API"
echo "=================================================================="

echo ""
echo "[1/4] Kiểm tra trạng thái máy chủ Loki:"
curl -s "$LOKI_URL/ready" && echo " -> Loki Ready!"

echo ""
echo "[2/4] Danh sách các nhãn (Labels) đã được lập chỉ mục:"
curl -s "$LOKI_URL/loki/api/v1/labels" | grep -o '"data":\[[^]]*\]'

echo ""
echo "[3/4] Thực thi truy vấn 1: Lấy 5 dòng log gần nhất của Nginx ({service_name=\"nginx\"}):"
curl -s -G "$LOKI_URL/loki/api/v1/query" --data-urlencode 'query={service_name="nginx"}' --data-urlencode 'limit=5' | head -c 400
echo -e "\n..."

echo ""
echo "[4/4] Thực thi truy vấn 2: Đếm log container ({job=\"docker\"} hoặc {container=\"de19_wordpress\"}):"
curl -s -G "$LOKI_URL/loki/api/v1/query" --data-urlencode 'query={container="de19_wordpress"}' --data-urlencode 'limit=3' | head -c 400
echo -e "\n..."

echo ""
echo "Hoàn thành kiểm tra LogQL!"
