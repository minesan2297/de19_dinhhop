-- ==============================================================================
-- init.sql - Khởi tạo cấu trúc và phân quyền Cơ sở dữ liệu MySQL
-- Đề 19: Website Tin tức / Cổng thông tin (DTC245201006 - Đinh Bách Hợp)
-- ==============================================================================

-- 1. Đảm bảo Database tồn tại và hỗ trợ đầy đủ tiếng Việt UTF-8
CREATE DATABASE IF NOT EXISTS `wordpress_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Phân quyền người dùng ứng dụng wp_user theo nguyên tắc đặc quyền tối thiểu (Least Privilege)
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, CREATE TEMPORARY TABLES, LOCK TABLES 
ON `wordpress_db`.* TO 'wp_user'@'%';

FLUSH PRIVILEGES;

-- Ghi chú: Dữ liệu mẫu hoàn chỉnh (7 bài viết, 5 danh mục) đã được lưu trữ trong tệp:
-- db/wordpress_backup.sql (được export trực tiếp từ hệ thống đang chạy).
