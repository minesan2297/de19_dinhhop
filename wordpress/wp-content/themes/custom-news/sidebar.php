<?php
/**
 * sidebar.php – Sidebar chính (Upgraded)
 * Đề 19: Website Tin tức / Cổng thông tin
 * Sinh viên: Đinh Bách Hợp – DTC245201006 – CNTT K23C
 */
?>
<aside class="sidebar-column">

    <!-- ── 1. THẺ ĐỊNH DANH SINH VIÊN & ĐỀ TÀI (ĐIỂM NHẤN CHUYÊN NGHIỆP) ── -->
    <div class="sidebar-widget widget-student-profile">
        <div class="widget-header header-student">
            <span class="widget-icon">📚</span>
            <h3 class="widget-heading">DỰ ÁN MÔN HỌC & SINH VIÊN</h3>
        </div>
        <div class="widget-content student-card-body">
            <div class="student-avatar-box">
                <div class="student-avatar-initials">ĐBH</div>
                <div class="student-meta-top">
                    <h4 class="student-name">Đinh Bách Hợp</h4>
                    <span class="student-id">Mã SV: DTC245201006</span>
                </div>
            </div>
            <div class="student-details-list">
                <div class="detail-row">
                    <span class="detail-label">📖 Môn học:</span>
                    <span class="detail-val">Quản trị & Triển khai HT Phần mềm</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">🏫 Lớp học:</span>
                    <span class="detail-val">CNTT K23C</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">📋 Đề tài:</span>
                    <span class="detail-val">Đề 19 – Cổng thông tin tin tức</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">🖥️ Máy chủ IP:</span>
                    <span class="detail-val code-ip">192.168.21.129</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">⚙️ Kiến trúc:</span>
                    <span class="detail-val">Nginx + WP + MySQL + Monitoring</span>
                </div>
            </div>
            <div class="student-card-footer">
                <span class="badge-verified">✓ Bài tập lớn đã hoàn thiện</span>
                <span class="badge-term">Năm học 2025 - 2026</span>
            </div>
        </div>
    </div>

    <!-- ── 2. WIDGET TRẠNG THÁI HẠ TẦNG (DOCKER / DEVOPS MONITORING) ── -->
    <div class="sidebar-widget widget-infra-status">
        <div class="widget-header header-infra">
            <span class="widget-icon">🛡️</span>
            <h3 class="widget-heading">HẠ TẦNG DỊCH VỤ DOCKER</h3>
            <span class="live-pulse-badge">10/10 OK</span>
        </div>
        <div class="widget-content infra-body">
            <div class="infra-item">
                <span class="infra-dot dot-active"></span>
                <span class="infra-name">de19_nginx (Reverse Proxy)</span>
                <span class="infra-port">Port 80, 3000</span>
            </div>
            <div class="infra-item">
                <span class="infra-dot dot-active"></span>
                <span class="infra-name">de19_wordpress (PHP-FPM)</span>
                <span class="infra-port">Port 9000</span>
            </div>
            <div class="infra-item">
                <span class="infra-dot dot-active"></span>
                <span class="infra-name">de19_mysql (Database 8.0)</span>
                <span class="infra-port">Port 3306</span>
            </div>
            <div class="infra-item">
                <span class="infra-dot dot-active"></span>
                <span class="infra-name">de19_phpmyadmin (DB Admin)</span>
                <span class="infra-port">/pma/</span>
            </div>
            <div class="infra-item">
                <span class="infra-dot dot-active"></span>
                <span class="infra-name">de19_prometheus (Metrics TSDB)</span>
                <span class="infra-port">Port 9090</span>
            </div>
            <div class="infra-item">
                <span class="infra-dot dot-active"></span>
                <span class="infra-name">de19_grafana (Dashboards)</span>
                <span class="infra-port">Port 3000</span>
            </div>
            <div class="infra-item">
                <span class="infra-dot dot-active"></span>
                <span class="infra-name">de19_loki + promtail (Logs)</span>
                <span class="infra-port">Port 3100</span>
            </div>
            <div class="infra-item">
                <span class="infra-dot dot-active"></span>
                <span class="infra-name">de19_cadvisor + node-exporter</span>
                <span class="infra-port">Metrics</span>
            </div>
            <div class="infra-actions">
                <a href="http://192.168.21.129:3000" target="_blank" class="infra-btn">
                    📊 Xem biểu đồ Grafana &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- ── 3. BÀI ĐỌC NHIỀU NHẤT (TRENDING NEWS) ── -->
    <div class="sidebar-widget widget-trending">
        <div class="widget-header">
            <span class="widget-icon">🔥</span>
            <h3 class="widget-heading">TIN ĐỌC NHIỀU NHẤT</h3>
        </div>
        <div class="widget-content">
            <?php
            $popular_posts = new WP_Query([
                'posts_per_page' => 5,
                'meta_key'       => 'cn_views',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
            ]);
            if ($popular_posts->have_posts()):
                $p_rank = 1;
            ?>
            <div class="trending-list">
                <?php while ($popular_posts->have_posts()): $popular_posts->the_post(); ?>
                <div class="trending-item">
                    <div class="trending-rank-num">#<?= $p_rank++; ?></div>
                    <div class="trending-item-details">
                        <h4 class="trending-title">
                            <a href="<?= esc_url(get_the_permalink()); ?>"><?= esc_html(get_the_title()); ?></a>
                        </h4>
                        <div class="trending-meta">
                            <span>🕐 <?= cn_time_ago(); ?></span>
                            <span>&bull;</span>
                            <span>👁️ <?= number_format(cn_get_views()); ?> lượt xem</span>
                        </div>
                    </div>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── 4. CHUYÊN MỤC TIN TỨC ── -->
    <div class="sidebar-widget widget-categories">
        <div class="widget-header">
            <span class="widget-icon">📁</span>
            <h3 class="widget-heading">CHUYÊN MỤC BÁO CHÍ</h3>
        </div>
        <div class="widget-content">
            <ul class="cat-pill-list">
                <?php
                $side_cats = get_categories(['hide_empty' => false]);
                foreach ($side_cats as $scat):
                ?>
                <li class="cat-pill-item">
                    <a href="<?= esc_url(get_category_link($scat)); ?>">
                        <span class="scat-name"><?= esc_html($scat->name); ?></span>
                        <span class="scat-count"><?= $scat->count; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

</aside>
