# 📰 Đề 19 – Website Tin tức / Cổng thông tin

**Sinh viên:** Đinh Bách Hợp – DTC245201006  
**Môn:** Thiết kế & Quản trị Hệ thống Phần mềm (CNTT K23C)

---

## 🏗️ Kiến trúc hệ thống

```
Internet (Port 80)
       │
  [Nginx Reverse Proxy]
   ┌───┴───────────────┐
   │                   │
[WordPress]      [phpMyAdmin]
   │
[MySQL 8.0]

[Prometheus] ← scrape ← [Node Exporter] + [cAdvisor]
       │
   [Grafana :3000] ← [Loki] ← [Promtail]
```

## 📦 Services

| Service | Image | Port nội bộ | Mô tả |
|---------|-------|------------|-------|
| nginx | nginx:1.25-alpine | **80** (public) | Reverse proxy |
| wordpress | wordpress:6.5-php8.2-fpm | 9000 (FPM) | CMS tin tức |
| mysql | mysql:8.0 | 3306 (internal) | Database |
| phpmyadmin | phpmyadmin:5.2 | 80 (internal) | DB GUI |
| prometheus | prom/prometheus:v2.52.0 | 9090 (internal) | Metrics collector |
| grafana | grafana/grafana:11.0.0 | **3000** (public) | Dashboard |
| loki | grafana/loki:3.0.0 | 3100 (internal) | Log aggregator |
| promtail | grafana/promtail:3.0.0 | 9080 (internal) | Log shipper |
| node-exporter | prom/node-exporter:v1.8.0 | 9100 (internal) | System metrics |
| cadvisor | gcr.io/cadvisor/cadvisor | 8080 (internal) | Container metrics |

---

## 🚀 Hướng dẫn triển khai

### Yêu cầu
- Docker Engine ≥ 24.0
- Docker Compose ≥ 2.20
- RAM ≥ 4GB, Disk ≥ 20GB

### Bước 1: Clone repo
```bash
git clone https://github.com/<your-username>/de19-news-website.git
cd de19-news-website
```

### Bước 2: Cấu hình biến môi trường
```bash
cp .env.example .env   # Hoặc chỉnh .env trực tiếp
# Đổi mật khẩu mặc định trước khi deploy!
```

### Bước 3: Khởi động toàn bộ stack
```bash
docker compose up -d
```

### Bước 4: Kiểm tra trạng thái
```bash
docker compose ps
docker compose logs -f
```

### Bước 5: Cài WordPress
Truy cập http://localhost → chạy wizard cài đặt WordPress

### Bước 6: Truy cập các dịch vụ
| URL | Dịch vụ | Tài khoản |
|-----|---------|-----------|
| http://localhost | Website tin tức | – |
| http://localhost/wp-admin | WordPress Admin | (tạo khi cài) |
| http://pma.localhost | phpMyAdmin | root / xem .env |
| http://localhost:3000 | Grafana | admin / Admin@Grafana2026 |

---

## 📊 Grafana – Hướng dẫn tạo Dashboard

### Import dashboard có sẵn
1. Vào Grafana → Dashboards → Import
2. Dán ID: **1860** (Node Exporter Full)
3. Chọn datasource: **Prometheus**

### LogQL queries mẫu
```logql
# Xem tất cả log Nginx
{job="nginx"}

# Lọc lỗi 404
{job="nginx"} |= "404"

# Lọc lỗi 500
{job="nginx"} | regexp `status=(?P<status>\d+)` | status = "500"

# Log WordPress container
{container="de19_wordpress"}

# Đếm request theo status code (5 phút)
sum by (status) (count_over_time({job="nginx"} | regexp `"status":"(?P<status>\d+)"` [5m]))
```

---

## 🔒 Hardening đã thực hiện

| Biện pháp | Mô tả |
|-----------|-------|
| **UFW Firewall** | Chỉ mở port 22, 80, 443, 3000 |
| **SSH Hardening** | PermitRootLogin no, MaxAuthTries 3 |
| **Fail2Ban** | Chặn brute-force SSH + Nginx |
| **Kernel sysctl** | Chống SYN flood, IP spoofing, redirect |
| **Nginx headers** | X-Frame-Options, X-Content-Type-Options, XSS-Protection |
| **server_tokens off** | Ẩn version Nginx |
| **Rate limiting** | Giới hạn request đến /wp-login.php |
| **Block sensitive files** | Chặn .htaccess, .env, .git qua HTTP |
| **Block xmlrpc.php** | Tắt XML-RPC (attack vector WordPress) |
| **.env không lên Git** | .gitignore bảo vệ credentials |

---

## 🛑 Dừng hệ thống
```bash
docker compose down          # Dừng nhưng giữ data
docker compose down -v       # Dừng và XÓA data (cẩn thận!)
```

---

## 📁 Cấu trúc thư mục

```
de19-news-website/
├── docker-compose.yml          # Orchestration chính
├── .env                        # Biến môi trường (KHÔNG commit)
├── .gitignore
├── nginx/
│   ├── nginx.conf              # Config chính Nginx
│   └── conf.d/
│       ├── wordpress.conf      # Reverse proxy WordPress
│       └── phpmyadmin.conf     # Reverse proxy phpMyAdmin
├── prometheus/
│   ├── prometheus.yml          # Scrape config
│   └── rules/
│       └── alerts.yml          # Alert rules
├── loki/
│   └── loki.yml                # Loki config
├── promtail/
│   └── promtail.yml            # Log collection config
├── grafana/
│   └── provisioning/
│       ├── datasources/        # Auto-provision Prometheus + Loki
│       └── dashboards/         # Auto-load dashboards
├── hardening/
│   └── hardening.sh            # Script bảo mật server
└── wordpress/
    └── wp-content/             # Custom themes / plugins
```
