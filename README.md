# Đề 19: Website Tin tức / Cổng thông tin (Cổng dịch vụ số)

### Báo cáo Môn học: Quản trị và Triển khai Hệ thống Phần mềm
**Trường Đại học Công nghệ Thông tin và Truyền thông Thái Nguyên (ICTU)**  
**Khoa Công nghệ Thông tin**

---

## Thông tin sinh viên & Đề tài

- **Sinh viên thực hiện:** Đinh Bách Hợp
- **Mã số sinh viên (MSSV):** DTC245201006
- **Lớp / Khóa:** CNTT K23C
- **Học phần:** Quản trị và Triển khai Hệ thống Phần mềm
- **Giảng viên hướng dẫn:** ThS. Vũ Việt Dũng
- **Kho lưu trữ mã nguồn:** [https://github.com/minesan2297/dtc245201006](https://github.com/minesan2297/dtc245201006)
- **Học kỳ / Năm học:** Học kỳ I – Năm học 2026

---

## 1. Tổng quan hệ thống

Hệ thống được xây dựng và triển khai theo kiến trúc vi dịch vụ container hóa (Docker Compose) nhằm phục vụ mô hình Cổng thông tin điện tử / Tòa soạn báo điện tử với đầy đủ các phân hệ:

1. **Phần mềm ứng dụng Web:** Website tin tức xây dựng trên nền tảng WordPress 6.5 chạy qua PHP 8.2 FPM, tích hợp theme báo chí chuẩn Editorial Broadsheet, hỗ trợ đầy đủ các phân mục tin tức, chức năng tìm kiếm thời gian thực và bảng điều khiển quản trị viên đăng bài viết.
2. **Cơ sở dữ liệu:** Hệ quản trị cơ sở dữ liệu MySQL 8.0 lưu trữ bền vững qua Docker volume, đi kèm bản sao lưu dump `db/wordpress_backup.sql` đầy đủ 7 bài viết và 5 danh mục; công cụ quản trị trực quan phpMyAdmin 5.2 được định tuyến an toàn qua Nginx.
3. **Cổng giao tiếp biên (Reverse Proxy):** Nginx 1.25 Alpine đóng vai trò điểm tiếp nhận duy nhất, định tuyến toàn bộ lưu lượng cổng 80 (HTTP), cổng 443 (HTTPS SSL tự ký) và cổng 3000 (Grafana), áp dụng các header bảo mật HTTP và chính sách phân quyền mạng.
4. **Hệ thống giám sát hiệu năng (Observability):** Cặp đôi Prometheus 2.52 và Grafana 11.0 thu thập chỉ số phần cứng (Node Exporter) và chỉ số từng container (cAdvisor) theo thời gian thực; tích hợp sẵn các quy tắc cảnh báo (Alert Rules).
5. **Quản lý nhật ký tập trung (Centralized Logging):** Promtail thu thập log hệ thống và container đẩy về Loki 3.0, hỗ trợ truy vấn lọc lỗi bằng cú pháp LogQL trên Grafana Explore.
6. **Bảo mật và gia cố hệ thống (Hardening):** Cấu hình tường lửa UFW, chống tấn công brute-force với Fail2Ban, gia cố SSH, kernel sysctl và cách ly phân vùng mạng nội bộ Docker.
7. **Hồ sơ minh chứng thực thi (Evidence):** Toàn bộ kết quả chạy lệnh kiểm thử 6 bước được trích xuất tự động và lưu trữ minh bạch trong thư mục `evidence/`.

---

## 2. Sơ đồ kiến trúc tổng thể

```
[ Người dùng / Trình duyệt Client ]
             │
             │ Port 80 (HTTP), Port 443 (HTTPS SSL) & Port 3000 (Grafana)
             ▼
┌────────────────────────────────────────────────────────┐
│               NGINX REVERSE PROXY                     │
│  - Điều hướng: /       -> WordPress FastCGI (:9000)   │
│  - Điều hướng: /pma/   -> phpMyAdmin (:80)            │
│  - Điều hướng: :3000   -> Grafana Dashboard (:3000)   │
│  - HTTPS SSL X.509 tự ký (certs/server.crt, key)      │
│  - HTTP Security Headers, Rate Limiting, Block attack  │
└──────┬───────────────────────┬─────────────────────────┘
       │ Network: frontend     │ Network: frontend
       ▼                       ▼
┌──────────────┐        ┌─────────────┐        ┌──────────────┐
│  WordPress   │        │ phpMyAdmin  │        │   Grafana    │
│  (PHP-FPM)   │        │   (Web DB)  │        │  Dashboard   │
└──────┬───────┘        └──────┬──────┘        └──────▲───────┘
       │ Network: backend      │ Network: backend     │
       └──────────────┬────────┘                      │
                      ▼                               │
              ┌────────────────┐       Network:       │
              │   MySQL 8.0    │      monitoring      │
              │ (Data Volume)  │                      │
              └────────────────┘                      │
                                                      │
┌─────────────────────────────────────────────────────┼──────┐
│                  HỆ THỐNG GIÁM SÁT                  │      │
│  [Node Exporter] ──┐                                │      │
│  [cAdvisor]      ──┼─> [Prometheus :9090] ──────────┘      │
│                    │                                       │
│  [Promtail]      ──┴─> [Loki Server :3100] ────────────────┘
└────────────────────────────────────────────────────────────┘
```

---

## 3. Danh mục dịch vụ (Service Matrix)

Toàn bộ 10 container được điều phối qua `docker-compose.yml`:

| STT | Tên Service | Container Name | Docker Image | Cổng nội bộ | Cổng Host | Vai trò |
|:---:|:---|:---|:---|:---:|:---:|:---|
| 1 | **nginx** | `de19_nginx` | nginx:1.25-alpine | 80, 443, 3000 | **80, 443, 3000** | Cổng tiếp nhận duy nhất, reverse proxy, SSL HTTPS, bảo mật HTTP |
| 2 | **wordpress** | `de19_wordpress` | wordpress:6.5-php8.2-fpm | 9000 | Không | Ứng dụng tin tức, xử lý mã nguồn PHP qua FastCGI |
| 3 | **mysql** | `de19_mysql` | mysql:8.0 | 3306 | Không | Lưu trữ CSDL wordpress_db, cô lập mạng backend |
| 4 | **phpmyadmin** | `de19_phpmyadmin` | phpmyadmin:5.2 | 80 | Không | Giao diện web quản trị CSDL, đi qua proxy `/pma/` |
| 5 | **prometheus** | `de19_prometheus` | prom/prometheus:v2.52.0 | 9090 | Không | Thu thập metrics từ node-exporter, cadvisor, loki |
| 6 | **grafana** | `de19_grafana` | grafana/grafana:11.0.0 | 3000 | **3000** | Trực quan hóa dashboard giám sát, cảnh báo và LogQL |
| 7 | **loki** | `de19_loki` | grafana/loki:3.0.0 | 3100 | Không | Máy chủ lưu trữ và lập chỉ mục log tập trung |
| 8 | **promtail** | `de19_promtail` | grafana/promtail:3.0.0 | Không | Không | Thu thập tệp log Nginx và Docker stdout đẩy về Loki |
| 9 | **node-exporter** | `de19_node_exporter` | prom/node-exporter:v1.8.0 | 9100 | Không | Thu thập chỉ số tài nguyên phần cứng (CPU, RAM, Disk) |
| 10 | **cadvisor** | `de19_cadvisor` | gcr.io/cadvisor/cadvisor:v0.49.1 | 8080 | Không | Thu thập chỉ số tài nguyên chi tiết từng container |

---

## 4. Bảng thông tin truy cập & Tài khoản mặc định

| Dịch vụ | Địa chỉ URL | Tài khoản mặc định | Mật khẩu mặc định | Ghi chú |
|:---|:---|:---|:---|:---|
| **Website Tin tức (HTTP)** | `http://<SERVER_IP>/` | *(Khách đọc tin)* | *(Công khai)* | Giao diện báo chí, đọc tin tức, tìm kiếm |
| **Website Tin tức (HTTPS)** | `https://<SERVER_IP>/` | *(Khách đọc tin)* | *(Công khai)* | Giao thức mã hóa SSL TLS 1.3 an toàn |
| **Quản trị WordPress** | `http://<SERVER_IP>/wp-admin/` | `admin` | `Admin@2026` | Quản trị viên đăng bài, quản lý danh mục |
| **phpMyAdmin** | `http://<SERVER_IP>/pma/` | `root` | `RootPass@2026` | Quản lý database `wordpress_db` |
| **phpMyAdmin (App user)** | `http://<SERVER_IP>/pma/` | `wp_user` | `WpPass@2026` | Tài khoản ứng dụng giới hạn quyền |
| **Grafana Dashboard** | `http://<SERVER_IP>:3000` | `admin` | `Admin@Grafana2026` | Theo dõi metrics và tra cứu log |
| **Máy chủ Ubuntu (SSH)** | `ssh ubuntu@<SERVER_IP>` | `ubuntu` | `3076` | Hỗ trợ xác thực SSH Key tự động |

---

## 5. Hướng dẫn cài đặt và triển khai

### Yêu cầu môi trường tối thiểu
- Hệ điều hành: Ubuntu 22.04 LTS hoặc Ubuntu 24.04 LTS
- Docker Engine >= 24.0.0, Docker Compose Plugin >= 2.20.0
- Phần cứng tối thiểu: 2 vCPU, 4GB RAM, 20GB dung lượng đĩa khả dụng

### Cách 1: Cài đặt tự động 1 lệnh duy nhất (Khuyên dùng)
Mở cửa sổ Terminal trên máy chủ Ubuntu và thực thi lệnh sau:

```bash
sudo apt update && sudo apt install -y git && git clone https://github.com/minesan2297/dtc245201006.git && cd dtc245201006 && bash setup.sh
```

### Cách 2: Triển khai thủ công từng bước

```bash
# 1. Tải mã nguồn dự án
git clone https://github.com/minesan2297/dtc245201006.git
cd dtc245201006

# 2. Khởi tạo tệp cấu hình biến môi trường
cp .env.example .env

# 3. Tạo chứng chỉ SSL tự ký cho HTTPS (nếu chưa có)
bash nginx/gen-cert.sh

# 4. Khởi động các container ở chế độ chạy ngầm
docker compose up -d

# 5. Kiểm tra sức khỏe toàn bộ hệ thống
bash verify.sh
```

---

## 6. Cấu hình Nginx Reverse Proxy & Bảo mật HTTP

Tệp cấu hình Nginx (`nginx/conf.d/default.conf`) áp dụng các chuẩn cấu hình biên an toàn:

- **Hỗ trợ SSL HTTPS:** Cổng 443 nạp chứng chỉ SSL X.509 (`nginx/certs/server.crt`, `server.key`), bật giao thức TLSv1.2, TLSv1.3.
- **Định tuyến phpMyAdmin:** Sử dụng chỉ thị `location ^~ /pma/` có độ ưu tiên cao nhất, ngăn chặn các quy tắc regex khớp tệp tĩnh của WordPress đè nhầm định tuyến của phpMyAdmin.
- **Bảo vệ mã nguồn PHP:** Chuyển tiếp toàn bộ yêu cầu `.php` đến `upstream wordpress_fpm` chạy trên cổng nội bộ 9000.
- **HTTP Security Headers áp dụng:**
  ```nginx
  add_header X-Frame-Options "SAMEORIGIN" always;
  add_header X-Content-Type-Options "nosniff" always;
  add_header X-XSS-Protection "1; mode=block" always;
  add_header Referrer-Policy "strict-origin-when-cross-origin" always;
  add_header Strict-Transport-Security "max-age=31536000" always;
  server_tokens off;
  ```
  - `X-Frame-Options: SAMEORIGIN`: Chống tấn công Clickjacking bằng cách cấm nhúng trang web vào thẻ iframe của bên thứ ba.
  - `X-Content-Type-Options: nosniff`: Ngăn chặn trình duyệt đoán định sai MIME-type để thực thi tệp tin giả mạo.
  - `X-XSS-Protection: 1; mode=block`: Bật bộ lọc chống Cross-Site Scripting của trình duyệt client.
  - `Strict-Transport-Security`: Bắt buộc trình duyệt ghi nhớ giao thức HTTPS an toàn.
  - `server_tokens off`: Ẩn thông tin phiên bản Nginx trong header phản hồi HTTP.

---

## 7. Giám sát hệ thống & Quản lý Log tập trung

### Prometheus Metrics
Prometheus tự động cào chỉ số định kỳ mỗi 15 giây với 4 target đều ở trạng thái `UP`:
- `cadvisor`: Đo lường chi tiết mức tiêu thụ CPU, RAM, Network I/O của từng container.
- `node-exporter`: Đo lường phần cứng máy chủ (tải CPU, RAM thực tế, dung lượng phân vùng `/`).
- `loki`: Đo lường tình trạng xử lý của máy chủ log.
- `prometheus`: Tự giám sát tiến trình nội bộ.

### Grafana Dashboard & Alerting
- Dashboard quản trị **DE19 – News Website Monitor** được nạp tự động qua cơ chế provisioning, hiển thị biểu đồ tải CPU, RAM, Disk, lưu lượng Nginx và container status.
- Bộ quy tắc cảnh báo tại `grafana/provisioning/alerting/alerting.yaml` tự động cảnh báo khi CPU > 80% hoặc RAM > 85%.

### Loki & Các câu truy vấn LogQL tiêu biểu
Trên giao diện **Grafana -> Explore**, chọn nguồn dữ liệu **Loki** để tra cứu nhật ký hoạt động:

```logql
# 1. Xem toàn bộ log truy cập máy chủ web Nginx
{service_name="nginx"}

# 2. Lọc các lỗi phản hồi máy chủ HTTP 5xx từ Nginx
{service_name="nginx"} | status =~ "5.."

# 3. Lọc lỗi từ container ứng dụng WordPress
{container="de19_wordpress"} |~ "(ERROR|Exception|Warning)"

# 4. Đếm tổng số lượng request gửi tới hệ thống trong vòng 5 phút
sum(count_over_time({service_name="nginx"}[5m]))
```

---

## 8. Các biện pháp Hardening (Gia cố an toàn thông tin)

Hệ thống được gia cố toàn diện thông qua kịch bản `hardening/hardening.sh` và thiết kế kiến trúc:

| STT | Biện pháp bảo mật | Công cụ / Cấu hình | Mục đích & Hiệu quả đạt được |
|:---:|:---|:---|:---|
| 1 | **Tường lửa máy chủ** | UFW Firewall | Đặt chính sách mặc định `deny incoming`. Chỉ mở cổng 22 (SSH), 80 (HTTP), 443 (HTTPS), 3000 (Grafana). Chặn hoàn toàn truy cập trực tiếp từ ngoài vào MySQL cổng 3306. |
| 2 | **Chống brute-force** | Fail2Ban (`jail.local`) | Tự động khóa địa chỉ IP 3600 giây nếu đăng nhập sai SSH quá 5 lần hoặc quét bot độc hại trên Nginx. Có kịch bản kiểm thử tại `scripts/simulate_bruteforce.sh`. |
| 3 | **Gia cố dịch vụ SSH** | `/etc/ssh/sshd_config` | Vô hiệu hóa đăng nhập trực tiếp tài khoản root (`PermitRootLogin no`), giới hạn `MaxAuthTries 3`, sử dụng khóa SSH Key ED25519. |
| 4 | **Gia cố Kernel Linux** | `/etc/sysctl.conf` | Kích hoạt SYN Cookies chống tấn công TCP SYN Flood, vô hiệu hóa IP source routing và ICMP redirect. |
| 5 | **Cô lập mạng Docker** | Docker Networks | Phân chia thành 3 mạng độc lập: `frontend`, `backend`, `monitoring`. Dữ liệu MySQL chỉ giao tiếp nội bộ qua mạng `backend`. |
| 6 | **Ẩn thông tin phiên bản** | `server_tokens off` | Loại bỏ dấu vết phiên bản phần mềm trong header phản hồi HTTP. |
| 7 | **Chặn vector tấn công** | Nginx rules | Chặn triệt để truy cập tệp tin nhạy cảm (`.env`, `.git`, `.htaccess`) và vô hiệu hóa endpoint nguy cơ `xmlrpc.php`. |
| 8 | **Giới hạn tần suất** | Nginx rate limiting | Giới hạn tốc độ request vào trang đăng nhập `wp-login.php` chống tấn công vét cạn tài khoản. |
| 9 | **Quản lý biến môi trường** | `.env` & `.gitignore` | Tuyệt đối không commit mật khẩu, khóa bí mật lên kho lưu trữ GitHub công khai. |
| 10 | **Tắt máy an toàn 1-click** | `Tat_May_Ao_De19.bat` | Kịch bản tắt máy gửi tín hiệu `poweroff` an toàn qua SSH, đảm bảo cơ sở dữ liệu MySQL flush hết buffer trước khi ngắt nguồn. |

---

## 9. Cấu trúc thư mục mã nguồn

```
dtc245201006/
├── docker-compose.yml          # Cấu hình điều phối toàn bộ 10 container microservices
├── .env.example                # Tệp mẫu cấu hình biến môi trường và mật khẩu an toàn
├── .gitignore                  # Danh sách tệp loại trừ khỏi Git (loại bỏ .env, data, certs)
├── setup.sh                    # Kịch bản tự động hóa cài đặt và khởi động toàn bộ hệ thống
├── verify.sh                   # Kịch bản tự động kiểm tra sức khỏe và tự phục hồi
├── teardown.sh                 # Kịch bản dừng và giải phóng an toàn tài nguyên hệ thống
├── README.md                   # Báo cáo môn học và tài liệu hướng dẫn kỹ thuật chi tiết
├── db/                         # Cơ sở dữ liệu MySQL
│   ├── init.sql                # Script khởi tạo cấu trúc CSDL và phân quyền đặc quyền tối thiểu
│   └── wordpress_backup.sql    # Bản dump CSDL đầy đủ 7 bài viết tin tức và 5 chuyên mục
├── docs/                       # Tài liệu báo cáo và hướng dẫn vận hành
│   ├── bao-cao.md              # Báo cáo môn học chi tiết 4 chương theo chuẩn ICTU
│   └── AUTO_START.md           # Hướng dẫn quy trình khởi động tự động 1-click
├── evidence/                   # Minh chứng thực nghiệm dòng lệnh 6 bước nộp bài
│   ├── buoc1-moitruong.txt     # Minh chứng khảo sát hệ điều hành, tài nguyên host, Docker
│   ├── buoc2-web-db.txt        # Minh chứng trạng thái WordPress, MySQL 7 bài viết, phpMyAdmin
│   ├── buoc3-nginx-ssl.txt     # Minh chứng Nginx Reverse Proxy, chứng chỉ SSL và Security Headers
│   ├── buoc4-prometheus-grafana.txt # Minh chứng 4 Scrape Targets UP và Grafana Dashboard
│   ├── buoc5-loki-logql.txt    # Minh chứng thu thập log Loki và kết quả thực thi 4 truy vấn LogQL
│   └── buoc6-hardening.txt     # Minh chứng bảo mật UFW Firewall, Fail2Ban, SSH, sysctl, network
├── hardening/                  # Kịch bản gia cố bảo mật máy chủ
│   └── hardening.sh            # Script cấu hình tường lửa UFW, Fail2Ban, SSH và kernel sysctl
├── nginx/                      # Cổng tiếp nhận biên Reverse Proxy
│   ├── nginx.conf              # Cấu hình chính của máy chủ Nginx (Gzip, Events, Worker)
│   ├── gen-cert.sh             # Script OpenSSL tự động tạo chứng chỉ SSL X.509 tự ký
│   ├── certs/                  # Thư mục lưu trữ chứng chỉ SSL (server.crt, server.key)
│   └── conf.d/
│       └── default.conf        # Định tuyến HTTP (80), HTTPS SSL (443), Grafana (3000), Security Headers
├── prometheus/                 # Hệ thống thu thập chỉ số hiệu năng (Metrics)
│   ├── prometheus.yml          # Cấu hình scrape interval và 4 targets giám sát
│   └── rules/
│       └── alerts.yml          # Quy tắc kích hoạt cảnh báo ngưỡng quá tải
├── loki/                       # Hệ thống quản lý nhật ký tập trung (Centralized Logging)
│   └── loki.yml                # Cấu hình lưu trữ và phân vùng log của Loki Server
├── promtail/                   # Bộ thu gom log đẩy về Loki (Log Shipper)
│   └── promtail.yml            # Cấu hình đường dẫn tệp log Nginx, Docker socket và nhãn
├── grafana/                    # Trực quan hóa Dashboard quản trị
│   └── provisioning/
│       ├── datasources/        # Tự động kết nối Prometheus và Loki datasources
│       ├── dashboards/         # Tự động nạp dashboard giám sát Đề 19
│       └── alerting/           # Quy tắc cảnh báo Alert Rules (CPU >80%, RAM >85%)
├── scripts/                    # Các kịch bản kiểm thử an ninh và hỗ trợ vận hành
│   ├── simulate_bruteforce.sh  # Kịch bản mô phỏng tấn công Brute-force kiểm thử Fail2Ban
│   ├── test_logql.sh           # Kịch bản kiểm tra và trích xuất dữ liệu qua Loki LogQL API
│   └── health_check.sh         # Kịch bản kiểm tra nhanh toàn diện sức khỏe hệ thống
└── wordpress/                  # Mã nguồn ứng dụng Web Cổng thông tin tin tức
    └── wp-content/
        └── themes/
            └── custom-news/    # Theme giao diện tòa soạn báo tùy biến độc quyền
                ├── style.css   # Định kiểu CSS chuẩn tòa soạn báo điện tử
                ├── functions.php # Đăng ký widget, menu điều hướng, thumbnail bài viết
                ├── header.php / footer.php / sidebar.php # Bố cục trang web
                ├── index.php / single.php / archive.php # Hiển thị bài viết và phân loại danh mục
                └── assets/
                    ├── design-tokens.css # Hệ thống design tokens màu sắc, typography
                    └── main.js # Xử lý tương tác giao diện và tìm kiếm thời gian thực
```

---

## 10. Lệnh dừng và gỡ bỏ hệ thống

```bash
# Dừng hệ thống nhưng giữ nguyên dữ liệu CSDL và nội dung bài viết
docker compose down

# Dừng và giải phóng toàn bộ dịch vụ thông qua script
bash teardown.sh
```

---

## Giấy phép & Tác quyền

Đề tài Báo cáo Môn học được xây dựng và hoàn thiện bởi sinh viên **Đinh Bách Hợp (MSSV: DTC245201006)** thuộc Khoa Công nghệ Thông tin – Trường Đại học Công nghệ Thông tin và Truyền thông Thái Nguyên (ICTU). Mã nguồn phát hành dưới giấy phép MIT License nhằm phục vụ mục đích học tập và nghiên cứu học phần.
