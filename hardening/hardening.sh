#!/bin/bash
# =============================================================
#  hardening.sh – Script bảo mật hệ thống (Hardening)
#  Đề 19: Website Tin tức – DTC245201006
#  Chạy với quyền root trên Linux server
# =============================================================

set -euo pipefail

echo "======================================"
echo " HARDENING – De 19 News Website"
echo "======================================"

# ── 1. Cập nhật hệ thống ──
echo "[1/8] Cập nhật hệ thống..."
apt-get update -y && apt-get upgrade -y

# ── 2. Tắt các dịch vụ không cần thiết ──
echo "[2/8] Tắt dịch vụ thừa..."
SERVICES_TO_DISABLE=("telnet" "rsh" "rlogin" "rexec" "nis" "cups" "avahi-daemon")
for svc in "${SERVICES_TO_DISABLE[@]}"; do
    if systemctl list-units --type=service | grep -q "$svc"; then
        systemctl stop "$svc" 2>/dev/null || true
        systemctl disable "$svc" 2>/dev/null || true
        echo "  ✓ Đã tắt: $svc"
    fi
done

# ── 3. Cấu hình UFW Firewall ──
echo "[3/8] Cấu hình UFW firewall..."
apt-get install -y ufw

# Reset và đặt policy mặc định
ufw --force reset
ufw default deny incoming
ufw default allow outgoing

# Chỉ mở các port cần thiết
ufw allow 22/tcp    comment 'SSH'
ufw allow 80/tcp    comment 'HTTP – Nginx'
ufw allow 443/tcp   comment 'HTTPS – Nginx (future)'
ufw allow 3000/tcp  comment 'Grafana Dashboard'
# Port 9090 (Prometheus) chỉ mở cho IP nội bộ nếu cần
# ufw allow from 10.0.0.0/8 to any port 9090 comment 'Prometheus internal'

ufw --force enable
echo "  ✓ UFW đã bật"
ufw status verbose

# ── 4. Bảo mật SSH ──
echo "[4/8] Bảo mật SSH..."
SSHD_CONFIG="/etc/ssh/sshd_config"
cp "$SSHD_CONFIG" "${SSHD_CONFIG}.bak"

# Tắt đăng nhập root qua SSH
sed -i 's/^#*PermitRootLogin.*/PermitRootLogin no/' "$SSHD_CONFIG"
# Tắt đăng nhập bằng password (chỉ dùng key)
# sed -i 's/^#*PasswordAuthentication.*/PasswordAuthentication no/' "$SSHD_CONFIG"
# Giới hạn số lần thử
sed -i 's/^#*MaxAuthTries.*/MaxAuthTries 3/' "$SSHD_CONFIG"
# Thời gian timeout
sed -i 's/^#*LoginGraceTime.*/LoginGraceTime 30/' "$SSHD_CONFIG"

systemctl restart sshd
echo "  ✓ SSH đã được bảo mật"

# ── 5. Cài đặt Fail2Ban ──
echo "[5/8] Cài Fail2Ban (chặn brute-force)..."
apt-get install -y fail2ban
cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime  = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true
port    = ssh
logpath = /var/log/auth.log

[nginx-http-auth]
enabled  = true
port     = http,https
logpath  = /var/log/nginx/error.log

[nginx-botsearch]
enabled  = true
port     = http,https
logpath  = /var/log/nginx/access.log
maxretry = 2
EOF
systemctl enable fail2ban
systemctl restart fail2ban
echo "  ✓ Fail2Ban đã cài và chạy"

# ── 6. Kernel hardening (sysctl) ──
echo "[6/8] Kernel hardening..."
cat >> /etc/sysctl.conf << 'EOF'

# ── De19 Hardening ──
# Ngăn IP Spoofing
net.ipv4.conf.all.rp_filter = 1
net.ipv4.conf.default.rp_filter = 1
# Tắt ICMP redirect
net.ipv4.conf.all.accept_redirects = 0
net.ipv6.conf.all.accept_redirects = 0
# Tắt source routing
net.ipv4.conf.all.accept_source_route = 0
# Bảo vệ SYN flood
net.ipv4.tcp_syncookies = 1
# Không log martian packets ra file (tránh log spam)
net.ipv4.conf.all.log_martians = 0
EOF
sysctl -p
echo "  ✓ Kernel parameters đã cập nhật"

# ── 7. Phân quyền file Docker ──
echo "[7/8] Phân quyền file cấu hình..."
chmod 600 /opt/de19-news-website/.env 2>/dev/null || true
chmod 644 /opt/de19-news-website/docker-compose.yml 2>/dev/null || true
echo "  ✓ Phân quyền xong"

# ── 8. Tắt IPv6 nếu không dùng (tùy chọn) ──
echo "[8/8] Kiểm tra IPv6..."
echo "  ℹ  Bỏ qua – giữ IPv6 mặc định"

echo ""
echo "======================================"
echo " HARDENING HOÀN THÀNH!"
echo "======================================"
echo " Các bước đã thực hiện:"
echo "  ✓ Cập nhật hệ thống"
echo "  ✓ Tắt dịch vụ không cần thiết"
echo "  ✓ UFW Firewall (chỉ mở port 22, 80, 443, 3000)"
echo "  ✓ SSH bảo mật (PermitRootLogin no)"
echo "  ✓ Fail2Ban (chặn brute-force)"
echo "  ✓ Kernel hardening (sysctl)"
echo "  ✓ Phân quyền file .env"
echo "======================================"
