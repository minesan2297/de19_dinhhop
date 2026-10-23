#!/usr/bin/env bash
TARGET_URL="http://localhost/wp-login.php"

for i in {1..12}; do
    CODE=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$TARGET_URL" -d "log=admin&pwd=testpass")
    echo "Yeu cau dang nhap $i: HTTP $CODE"
    sleep 0.1
done
