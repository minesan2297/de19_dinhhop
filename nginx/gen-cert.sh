#!/usr/bin/env bash
# ==============================================================================
# gen-cert.sh - Tạo chứng chỉ SSL OpenSSL tự ký cho Nginx HTTPS
# Đề 19: Website Tin tức / Cổng thông tin - Sinh viên: Đinh Bách Hợp (DTC245201006)
# ==============================================================================

set -e

CERT_DIR="$(dirname "$0")/certs"
mkdir -p "$CERT_DIR"

echo "[1/2] Đang tạo Private Key và Self-signed Certificate (365 ngày)..."
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout "$CERT_DIR/server.key" \
  -out "$CERT_DIR/server.crt" \
  -subj "/C=VN/ST=ThaiNguyen/L=ThaiNguyen/O=ICTU/OU=CNTT_K23C/CN=192.168.21.129"

chmod 600 "$CERT_DIR/server.key"
chmod 644 "$CERT_DIR/server.crt"

echo "[2/2] Hoàn tất! Chứng chỉ đã được tạo tại:"
echo "  - Private Key : $CERT_DIR/server.key"
echo "  - Certificate : $CERT_DIR/server.crt"
