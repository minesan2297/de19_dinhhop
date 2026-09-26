# BÁO CÁO ĐỒ ÁN CUỐI KỲ
## Kiến trúc & Triển khai Hệ thống Phần mềm (CNTT K23C)

---

| Thông tin | Chi tiết |
|-----------|---------|
| **Họ và tên** | Đinh Bách Hợp |
| **Mã sinh viên** | DTC245201006 |
| **Đề số** | 19 |
| **Tên đề tài** | Website Tin tức / Cổng thông tin |
| **Ngày nộp** | ____/____/2026 |
| **Giảng viên hướng dẫn** | Vũ Việt Dũng |

---

## CHƯƠNG 1: PHÂN TÍCH YÊU CẦU

### 1.1 Mô tả hệ thống

Website Tin tức / Cổng thông tin là một hệ thống web cho phép:
- **Đọc giả** xem tin tức, tìm kiếm và lọc theo danh mục
- **Biên tập viên** đăng bài, quản lý nội dung qua giao diện admin
- **Quản trị viên** quản lý toàn bộ hệ thống, người dùng, cài đặt

### 1.2 Yêu cầu chức năng

| STT | Chức năng | Ưu tiên |
|-----|-----------|---------|
| 1 | Hiển thị danh sách tin tức theo thời gian | Cao |
| 2 | Phân loại tin tức theo danh mục | Cao |
| 3 | Tìm kiếm bài viết | Cao |
| 4 | Xem chi tiết bài viết | Cao |
| 5 | Admin đăng/sửa/xóa bài viết | Cao |
| 6 | Quản lý danh mục | Trung bình |
| 7 | Phân quyền người dùng | Trung bình |
| 8 | Thống kê lượt xem | Trung bình |
| 9 | Bình luận bài viết | Thấp |
| 10 | Tìm kiếm nâng cao | Thấp |

### 1.3 Yêu cầu phi chức năng

- **Hiệu năng**: Tải trang < 3 giây với 100 người dùng đồng thời
- **Bảo mật**: OWASP Top 10, hardening server
- **Khả dụng**: Uptime ≥ 99%
- **Quan sát được**: Monitoring metrics + logs tập trung

---

## CHƯƠNG 2: THIẾT KẾ HỆ THỐNG

### 2.1 Kiến trúc tổng thể

```
┌─────────────────────────────────────────────────────┐
│                    INTERNET                         │
└─────────────────────┬───────────────────────────────┘
                      │ Port 80/443
                      ▼
         ┌────────────────────────┐
         │   Nginx Reverse Proxy  │ ← Security headers
         │   (Rate limiting,      │   X-Frame-Options
         │    Block xmlrpc.php)   │   server_tokens off
         └──────┬─────────────────┘
                │
        ┌───────┴───────┐
        ▼               ▼
┌──────────────┐  ┌─────────────┐
│  WordPress   │  │ phpMyAdmin  │
│  (PHP-FPM)   │  │  (Port 80)  │
└──────┬───────┘  └──────┬──────┘
       │                 │
       └────────┬────────┘
                ▼
       ┌────────────────┐
       │   MySQL 8.0    │
       │  (Internal     │
       │   only)        │
       └────────────────┘

┌────────────────────────────────────────────┐
│              MONITORING STACK              │
│                                            │
│  [Promtail] → [Loki] → [Grafana :3000]    │
│  [Node Exporter] → [Prometheus] ──────┘   │
│  [cAdvisor]   ──────────────────────┘     │
└────────────────────────────────────────────┘
```

### 2.2 Công nghệ sử dụng

| Thành phần | Công nghệ | Phiên bản | Vai trò |
|-----------|-----------|-----------|---------|
| CMS | WordPress | 6.5 | Quản lý nội dung |
| Database | MySQL | 8.0 | Lưu trữ dữ liệu |
| DB Admin | phpMyAdmin | 5.2 | Quản lý database |
| Web Server | Nginx | 1.25 | Reverse proxy |
| Container | Docker | 24.x | Containerization |
| Orchestration | Docker Compose | 2.x | Service management |
| Metrics | Prometheus | 2.52 | Thu thập metrics |
| Dashboard | Grafana | 11.0 | Hiển thị dashboard |
| Logs | Loki + Promtail | 3.0 | Tập trung logs |
| System metrics | Node Exporter | 1.8 | Metrics hệ thống |
| Container metrics | cAdvisor | 0.49 | Metrics container |
| CI/CD | GitHub Actions | – | Tự động deploy |
| Source control | Git / GitHub | – | Quản lý mã nguồn |

### 2.3 Thiết kế cơ sở dữ liệu

Sử dụng schema mặc định của WordPress với các bảng chính:

| Bảng | Mô tả |
|------|-------|
| `wp_posts` | Lưu bài viết, trang, các loại nội dung |
| `wp_postmeta` | Metadata bài viết (views, featured, ...) |
| `wp_terms` | Danh mục, tags |
| `wp_term_relationships` | Liên kết bài viết – danh mục |
| `wp_users` | Quản lý người dùng |
| `wp_options` | Cài đặt hệ thống |

### 2.4 Cấu trúc thư mục dự án

```
de19-news-website/
├── docker-compose.yml
├── .env
├── .gitignore
├── setup.sh                    ← Script tự động deploy
├── nginx/
│   ├── nginx.conf
│   └── conf.d/
│       ├── wordpress.conf
│       └── phpmyadmin.conf
├── prometheus/
│   ├── prometheus.yml
│   └── rules/alerts.yml
├── loki/loki.yml
├── promtail/promtail.yml
├── grafana/provisioning/
│   ├── datasources/
│   └── dashboards/             ← Dashboard tự động load
├── hardening/hardening.sh
└── wordpress/wp-content/
    └── themes/custom-news/     ← Custom theme
        ├── style.css
        ├── functions.php
        ├── header.php / footer.php
        ├── index.php / single.php / archive.php
        ├── sidebar.php
        └── assets/js/main.js
```

---

## CHƯƠNG 3: TRIỂN KHAI

### 3.1 Môi trường triển khai

| Thông số | Giá trị |
|---------|--------|
| OS | Ubuntu 22.04 LTS |
| CPU | 2 vCPU |
| RAM | 4 GB |
| Disk | 20 GB SSD |
| Docker | 24.x |
| Docker Compose | 2.x |

### 3.2 Quy trình triển khai

**Bước 1:** Clone repository từ GitHub
```bash
git clone https://github.com/<username>/de19-news-website.git
cd de19-news-website
```

**Bước 2:** Cấu hình biến môi trường
```bash
nano .env   # Đổi mật khẩu mặc định
```

**Bước 3:** Chạy script tự động deploy
```bash
chmod +x setup.sh
bash setup.sh
```

**Bước 4:** Cài WordPress tại http://server-ip

**Bước 5:** Bảo mật server
```bash
sudo bash hardening/hardening.sh
```

### 3.3 CI/CD Pipeline (GitHub Actions)

Pipeline tự động gồm 3 job:

1. **Validate**: Kiểm tra docker-compose.yml, nginx config, prometheus config
2. **Security Scan**: Quét với Trivy, kiểm tra hardcoded secrets
3. **Deploy**: SSH vào server, pull code, restart containers, health check

---

## CHƯƠNG 4: BẢO MẬT (HARDENING)

### 4.1 Các biện pháp bảo mật đã thực hiện

| STT | Biện pháp | Công cụ | Mô tả |
|-----|-----------|---------|-------|
| 1 | Firewall | UFW | Chỉ mở port 22, 80, 443, 3000 |
| 2 | SSH hardening | sshd_config | PermitRootLogin no, MaxAuthTries 3 |
| 3 | Brute-force protection | Fail2Ban | Chặn IP sau 5 lần thử sai |
| 4 | Kernel hardening | sysctl | Chống SYN flood, IP spoofing |
| 5 | HTTP security headers | Nginx | X-Frame-Options, X-XSS-Protection |
| 6 | Server info hiding | Nginx | server_tokens off |
| 7 | Rate limiting | Nginx | 5 req/phút cho /wp-login.php |
| 8 | Block attack vectors | Nginx | Chặn xmlrpc.php, .env, .git |
| 9 | Secret management | .gitignore | .env không đưa lên GitHub |
| 10 | Container isolation | Docker networks | Frontend/Backend/Monitoring tách biệt |

### 4.2 Phân vùng mạng Docker

```
Network: frontend   → Nginx ↔ WordPress ↔ phpMyAdmin ↔ Grafana
Network: backend    → WordPress ↔ MySQL ↔ phpMyAdmin
Network: monitoring → Prometheus ↔ Grafana ↔ Loki ↔ Promtail
```

MySQL **không expose** port ra ngoài – chỉ truy cập nội bộ qua network `backend`.

---

## CHƯƠNG 5: GIÁM SÁT (MONITORING)

### 5.1 Prometheus – Thu thập Metrics

**Scrape targets:**
- `node-exporter:9100` – CPU, RAM, Disk, Network
- `cadvisor:8080` – Metrics từng container Docker
- `prometheus:9090` – Self-monitoring
- `loki:3100` – Loki metrics

**Alert rules:**
- CPU > 80% trong 5 phút → Warning
- RAM > 85% trong 2 phút → Critical
- Disk > 80% → Warning
- Container bị dừng → Critical

### 5.2 Loki + LogQL – Tập trung Logs

**Log sources:**
- Nginx access log (`/var/log/nginx/access.log`)
- Nginx error log (`/var/log/nginx/error.log`)
- Tất cả Docker containers có prefix `de19_`

**LogQL queries mẫu:**

```logql
# Xem tất cả log Nginx
{job="nginx"}

# Lọc HTTP 4xx errors
{job="nginx"} | regexp `status=(?P<status>\d+)` | status =~ "4.."

# Lọc HTTP 500 errors
{job="nginx"} |= "\" 500 "

# Log WordPress
{container="de19_wordpress"}

# Log MySQL
{container="de19_mysql"}

# Đếm request 404 trong 5 phút
sum(count_over_time({job="nginx"} |= "404" [5m]))

# Top IP truy cập nhiều nhất
topk(10, sum by (remote_addr) (count_over_time({job="nginx"} [1h])))
```

### 5.3 Grafana Dashboard

Dashboard `DE19 – News Website Monitor` gồm các panel:
- **Row 1 (Stat)**: CPU %, RAM %, Disk %, Containers running, Uptime, Network In
- **Row 2 (Time series)**: CPU & RAM theo thời gian, Network I/O
- **Row 3 (Logs)**: Nginx access logs, WordPress container logs
- **Row 4 (Table)**: Container CPU usage, Container RAM usage

---

## CHƯƠNG 6: KẾT LUẬN

### 6.1 Kết quả đạt được

✅ Website tin tức hoạt động đầy đủ (bài viết, danh mục, admin)  
✅ Nginx reverse proxy với security headers và rate limiting  
✅ MySQL + phpMyAdmin quản lý database  
✅ Prometheus + Grafana dashboard giám sát real-time  
✅ Loki + Promtail thu thập và truy vấn logs bằng LogQL  
✅ Hardening: UFW, Fail2Ban, SSH hardening, kernel sysctl  
✅ GitHub Actions CI/CD tự động deploy  
✅ Docker Compose orchestration 10 services  

### 6.2 Hạn chế và hướng phát triển

| Hạn chế | Hướng phát triển |
|---------|-----------------|
| Chưa có HTTPS | Tích hợp Let's Encrypt + Certbot |
| Chưa có CDN | Tích hợp Cloudflare |
| Backup thủ công | Tự động hóa backup với cron job |
| Chưa có load balancing | Tích hợp HAProxy hoặc scale WordPress |
| Monitoring chưa có alerting | Tích hợp Alertmanager + email/Telegram |

### 6.3 Tài liệu tham khảo

1. Docker Documentation – https://docs.docker.com
2. WordPress Developer Resources – https://developer.wordpress.org
3. Prometheus Documentation – https://prometheus.io/docs
4. Grafana Documentation – https://grafana.com/docs
5. Loki Documentation – https://grafana.com/docs/loki/latest
6. Nginx Documentation – https://nginx.org/en/docs
7. OWASP Top 10 – https://owasp.org/www-project-top-ten

---

*Báo cáo được thực hiện bởi: **Đinh Bách Hợp – DTC245201006***  
*Đề 19 – CNTT K23C – Năm học 2025-2026*
