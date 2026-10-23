#!/usr/bin/env bash
# ==============================================================================
# simulate_bruteforce.sh - Kịch bản mô phỏng tấn công Brute-force để kiểm thử Fail2Ban
# Đề 19: Website Tin tức - DTC245201006 (CNTT K23C)
# ==============================================================================

TARGET_IP="${1:-127.0.0.1}"
echo "=================================================================="
echo " [KIỂM THỬ AN NINH] MÔ PHỎNG TẤN CÔNG BRUTE-FORCE VÀO MÁY CHỦ"
echo " Mục tiêu: $TARGET_IP"
echo "=================================================================="

echo ""
echo "[1/2] Gửi 5 yêu cầu đăng nhập sai mật khẩu liên tiếp vào SSH..."
for i in {1..5}; do
    echo "  -> Lần $i: Thử đăng nhập sai user root / pass wrong_password_demo..."
    sshpass -p "wrong_password_demo" ssh -o StrictHostKeyChecking=no -o ConnectTimeout=2 root@$TARGET_IP exit 2>/dev/null || true
    sleep 0.5
done

echo ""
echo "[2/2] Gửi 5 yêu cầu quét bot khai thác lỗ hổng vào Web Nginx..."
for i in {1..5}; do
    echo "  -> Lần $i: Gửi request quét file nhạy cảm /.env, /xmlrpc.php, /admin.php..."
    curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" "http://$TARGET_IP/.env" || true
    sleep 0.3
done

echo ""
echo "=================================================================="
echo " Đã hoàn tất mô phỏng! Kiểm tra trạng thái Fail2Ban:"
echo "   sudo fail2ban-client status sshd"
echo "   sudo fail2ban-client status nginx-botsearch"
echo "=================================================================="
