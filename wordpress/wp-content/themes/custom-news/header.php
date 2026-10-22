<?php
/**
 * header.php – Custom News Theme
 * Impeccable Editorial & Frontend Design
 * Đề 19: Quản trị và Triển khai Hệ thống Phần mềm – CNTT K23C
 * Sinh viên: Đinh Bách Hợp – DTC245201006
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php bloginfo('description'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ═══════════════════════════════════════ 1. TRI-PORTAL UTILITY BAR (DÙNG CHO CẢ 3 WEBSITE) ══ -->
<div class="tri-portal-bar">
    <div class="dt-container">
        <div class="tri-portal-inner">
            <!-- Metadata trái -->
            <div class="portal-meta-left">
                <span class="meta-issue">TẬP I &bull; SỐ 19 &bull; NIÊN GIÁM 2026</span>
                <span class="meta-sep">/</span>
                <span class="meta-date" id="current-vietnamese-date">Thứ Năm, 22/10/2026</span>
                <span class="meta-clock" id="current-live-time">00:00:00</span>
                <span class="meta-sep">/</span>
                <span class="meta-course">Môn: Quản trị & Triển khai HT Phần mềm</span>
            </div>

            <!-- Switcher phải: Kết nối cả 3 Website trong hệ thống -->
            <nav class="tri-portal-nav" aria-label="Điều hướng 3 nền tảng hệ thống">
                <span class="nav-label">HỆ THỐNG DỊCH VỤ:</span>
                <a href="<?= esc_url(home_url('/')); ?>" class="portal-tab active" title="Website Tin tức / Cổng thông tin (WordPress Core)">
                    <span class="portal-dot dot-wp"></span>
                    <span>1. Website Tin tức</span>
                </a>
                <a href="http://192.168.21.129/pma/" target="_blank" class="portal-tab" title="Quản trị cơ sở dữ liệu MySQL qua phpMyAdmin">
                    <span class="portal-dot dot-db"></span>
                    <span>2. phpMyAdmin</span>
                    <span class="tab-ext">&nearr;</span>
                </a>
                <a href="http://192.168.21.129:3000" target="_blank" class="portal-tab" title="Bảng điều khiển giám sát hệ thống Grafana">
                    <span class="portal-dot dot-grafana"></span>
                    <span>3. Grafana Telemetry</span>
                    <span class="tab-ext">&nearr;</span>
                </a>
            </nav>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════ 2. BROADSHEET MASTHEAD ══ -->
<header class="editorial-masthead">
    <div class="dt-container">
        
        <!-- Header Info Bar -->
        <div class="masthead-pre">
            <div class="pre-left">
                <span>Trường Đại học Công nghệ &bull; Khoa CNTT</span>
                <span>Lớp: CNTT K23C</span>
            </div>
            <div class="pre-center">
                <span>BÁO ĐIỆN TỬ PHÂN PHỐI NỘI DUNG QUA NGINX 1.25</span>
            </div>
            <div class="pre-right">
                <span>Sinh viên: <strong>Đinh Bách Hợp</strong></span>
                <span>Mã SV: <strong>DTC245201006</strong></span>
            </div>
        </div>

        <!-- Grand Newspaper Title -->
        <div class="masthead-main">
            <h1 class="newspaper-title">
                <a href="<?= esc_url(home_url('/')); ?>">CỔNG THÔNG TIN TIN TỨC</a>
            </h1>
            <p class="newspaper-subhead">
                Xuất bản và Phân phối Nội dung Số trên Hạ tầng Nginx Reverse Proxy, MySQL 8.0 & Cụm Giám sát Prometheus - Grafana
            </p>
        </div>

        <!-- Navigation Bar & Quick Search -->
        <div class="masthead-nav-wrapper">
            <button class="nav-toggle-editorial" aria-label="Menu" onclick="document.querySelector('.editorial-nav').classList.toggle('open')">
                ☰ DANH MỤC
            </button>

            <ul class="editorial-nav">
                <li class="<?= is_home() ? 'current-item' : ''; ?>">
                    <a href="<?= esc_url(home_url('/')); ?>">Trang nhất</a>
                </li>
                <?php
                $nav_cats = get_categories(['hide_empty' => false, 'number' => 5]);
                foreach ($nav_cats as $cat):
                    $is_active = is_category($cat->term_id) ? 'current-item' : '';
                ?>
                <li class="<?= $is_active; ?>">
                    <a href="<?= esc_url(get_category_link($cat)); ?>"><?= esc_html($cat->name); ?></a>
                </li>
                <?php endforeach; ?>
                <li>
                    <a href="<?= esc_url(admin_url()); ?>" target="_blank" class="nav-admin-link">Quản trị CMS &rarr;</a>
                </li>
            </ul>

            <div class="editorial-search">
                <form role="search" method="get" class="search-form-clean" action="<?= esc_url(home_url('/')); ?>">
                    <input type="search" name="s" placeholder="Tra cứu bản tin..." value="<?= get_search_query(); ?>" required>
                    <button type="submit" aria-label="Tìm kiếm">&#128269;</button>
                </form>
            </div>
        </div>

    </div>
</header>

<!-- ═══════════════════════════════════════ 3. EDITORIAL TICKER ══ -->
<?php
$ticker_query = new WP_Query([
    'posts_per_page' => 5,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC'
]);
if ($ticker_query->have_posts()):
?>
<div class="editorial-ticker-bar">
    <div class="dt-container">
        <div class="ticker-flex">
            <span class="ticker-badge">BẢN TIN 24H</span>
            <div class="ticker-content">
                <?php while ($ticker_query->have_posts()): $ticker_query->the_post(); ?>
                    <a href="<?= esc_url(get_the_permalink()); ?>" class="ticker-link">
                        <time class="t-time"><?= get_the_time('H:i'); ?></time>
                        <span class="t-text"><?= esc_html(get_the_title()); ?></span>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════ MAIN WRAPPER ══ -->
<div class="editorial-canvas">
