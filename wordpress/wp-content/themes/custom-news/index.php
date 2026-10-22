<?php
/**
 * index.php – Trang chủ Cổng thông tin tin tức
 * Impeccable Editorial & Frontend Design Layout
 * Đề 19: Quản trị và Triển khai Hệ thống Phần mềm – CNTT K23C
 * Sinh viên: Đinh Bách Hợp – DTC245201006
 */
get_header();
?>

<main class="dt-container">

    <!-- ═══════════════════════════════════════ LANDING HERO: 3-COLUMN BROADSHEET ══ -->
    <?php
    $hero_query = new WP_Query([
        'posts_per_page' => 4,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    ]);

    if ($hero_query->have_posts()):
        $posts_list = $hero_query->posts;
        $lead_post  = $posts_list[0];
        $side_posts = array_slice($posts_list, 1, 3);
    ?>
    <section class="landing-hero-broadsheet">
        
        <!-- ── CỘT 1: BÀI BÁO TIÊU ĐIỂM CHÍNH (LEAD STORY - 52%) ── -->
        <article class="hero-lead-column">
            <div class="lead-eyebrow">
                <span class="kicker-tag">TIÊU ĐIỂM HÔM NAY</span>
                <span class="kicker-sep">&bull;</span>
                <?php
                $lead_cat = cn_get_first_category($lead_post->ID);
                if ($lead_cat): ?>
                    <a href="<?= esc_url(get_category_link($lead_cat)); ?>" class="kicker-cat">
                        <?= esc_html($lead_cat->name); ?>
                    </a>
                <?php endif; ?>
            </div>

            <h2 class="lead-headline">
                <a href="<?= esc_url(get_permalink($lead_post->ID)); ?>">
                    <?= esc_html($lead_post->post_title); ?>
                </a>
            </h2>

            <div class="lead-byline">
                <span class="byline-author">Bài viết bởi <strong>Đinh Bách Hợp</strong></span>
                <span class="byline-dot">&bull;</span>
                <time class="byline-time"><?= cn_time_ago($lead_post->ID); ?></time>
                <span class="byline-dot">&bull;</span>
                <span class="byline-read"><?= cn_reading_time($lead_post->ID); ?> phút đọc</span>
            </div>

            <div class="lead-image-frame">
                <a href="<?= esc_url(get_permalink($lead_post->ID)); ?>">
                    <?php cn_the_thumb($lead_post->ID, 'hero', 'lead-img'); ?>
                </a>
                <figcaption class="lead-caption">
                    Tư liệu thực hành: Triển khai và vận hành hệ thống tin tức phân tán trên nền tảng Docker & Nginx.
                </figcaption>
            </div>

            <div class="lead-summary">
                <p>
                    <?= esc_html(wp_trim_words($lead_post->post_excerpt ?: $lead_post->post_content, 35)); ?>
                </p>
            </div>
            
            <a href="<?= esc_url(get_permalink($lead_post->ID)); ?>" class="lead-readmore">
                Đọc toàn văn bài viết &rarr;
            </a>
        </article>

        <!-- ── CỘT 2: BẢN TIN TRONG NGÀY (THE DISPATCH - 25%) ── -->
        <aside class="hero-dispatch-column">
            <div class="column-header-rule">
                <span class="col-title">BẢN TIN TIÊU ĐIỂM</span>
            </div>

            <div class="dispatch-stream">
                <?php foreach ($side_posts as $sub):
                    $scat = cn_get_first_category($sub->ID);
                ?>
                <article class="dispatch-entry">
                    <div class="dispatch-micro-meta">
                        <span class="dispatch-time">[<?= get_the_time('H:i', $sub->ID); ?>]</span>
                        <?php if ($scat): ?>
                            <a href="<?= esc_url(get_category_link($scat)); ?>" class="dispatch-cat">
                                <?= esc_html($scat->name); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="dispatch-title">
                        <a href="<?= esc_url(get_permalink($sub->ID)); ?>">
                            <?= esc_html($sub->post_title); ?>
                        </a>
                    </h3>

                    <div class="dispatch-thumb-box">
                        <a href="<?= esc_url(get_permalink($sub->ID)); ?>">
                            <?php cn_the_thumb($sub->ID, 'thumb', 'dispatch-thumb'); ?>
                        </a>
                    </div>

                    <p class="dispatch-snippet">
                        <?= esc_html(wp_trim_words($sub->post_excerpt ?: $sub->post_content, 18)); ?>
                    </p>
                </article>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- ── CỘT 3: HỒ SƠ HẠ TẦNG TRI-PORTAL (SYSTEM DOSSIER - 23%) ── -->
        <aside class="hero-manifest-column">
            <div class="column-header-rule">
                <span class="col-title">HỒ SƠ HẠ TẦNG & DỊCH VỤ</span>
            </div>

            <!-- Student Identity Registry -->
            <div class="manifest-card">
                <div class="manifest-title-row">
                    <span class="manifest-badge">SINH VIÊN THỰC HIỆN</span>
                </div>
                <div class="manifest-content">
                    <div class="manifest-field">
                        <span class="m-label">Họ và tên:</span>
                        <span class="m-value">Đinh Bách Hợp</span>
                    </div>
                    <div class="manifest-field">
                        <span class="m-label">Mã SV:</span>
                        <span class="m-value m-mono">DTC245201006</span>
                    </div>
                    <div class="manifest-field">
                        <span class="m-label">Lớp:</span>
                        <span class="m-value">CNTT K23C</span>
                    </div>
                    <div class="manifest-field">
                        <span class="m-label">Môn học:</span>
                        <span class="m-value">QT & Triển khai HT Phần mềm</span>
                    </div>
                    <div class="manifest-field">
                        <span class="m-label">Đề tài:</span>
                        <span class="m-value">Đề 19 – Cổng thông tin tin tức</span>
                    </div>
                </div>
            </div>

            <!-- Tri-Portal Architecture Registry (Nối cả 3 website) -->
            <div class="manifest-card manifest-services">
                <div class="manifest-title-row">
                    <span class="manifest-badge">3 NỀN TẢNG HỆ THỐNG</span>
                    <span class="m-status-live">● ONLINE</span>
                </div>
                <div class="manifest-content">
                    <div class="service-entry">
                        <div class="srv-head">
                            <span class="srv-num">01.</span>
                            <span class="srv-name">Website Tin tức (WordPress)</span>
                        </div>
                        <div class="srv-desc">Cổng 80 &bull; Nginx Reverse Proxy &bull; Core CMS</div>
                        <a href="<?= esc_url(home_url('/')); ?>" class="srv-btn current">
                            Trang đang xem &bull; Port 80
                        </a>
                    </div>

                    <div class="service-entry">
                        <div class="srv-head">
                            <span class="srv-num">02.</span>
                            <span class="srv-name">phpMyAdmin Console</span>
                        </div>
                        <div class="srv-desc">Đường dẫn /pma/ &bull; MySQL 8.0 &bull; Quản trị DB</div>
                        <a href="http://192.168.21.129/pma/" target="_blank" class="srv-btn">
                            Mở phpMyAdmin &nearr;
                        </a>
                    </div>

                    <div class="service-entry">
                        <div class="srv-head">
                            <span class="srv-num">03.</span>
                            <span class="srv-name">Grafana Telemetry Hub</span>
                        </div>
                        <div class="srv-desc">Cổng 3000 &bull; Prometheus TSDB &bull; Loki Logs</div>
                        <a href="http://192.168.21.129:3000" target="_blank" class="srv-btn">
                            Mở Grafana Dashboard &nearr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Architectural Manifest Specs -->
            <div class="manifest-tech-specs">
                <div class="spec-row">
                    <span>Host Node:</span>
                    <code>192.168.21.129</code>
                </div>
                <div class="spec-row">
                    <span>Containers:</span>
                    <code>10 Services Active</code>
                </div>
                <div class="spec-row">
                    <span>OS Platform:</span>
                    <code>Ubuntu 24.04 (SSD)</code>
                </div>
            </div>

        </aside>

    </section>
    <?php wp_reset_postdata(); endif; ?>

    <!-- ═══════════════════════════════════════ BROADSHEET TRIPLE RULE SEPARATOR ══ -->
    <div class="broadsheet-separator">
        <span class="sep-rule-thick"></span>
        <span class="sep-text">CÁC CHUYÊN MỤC BÁO CHÍ ĐẶC SẮC</span>
        <span class="sep-rule-thin"></span>
    </div>

    <!-- ═══════════════════════════════════════ SECONDARY SECTION: 3-COLUMN EDITORIAL FEEDS ══ -->
    <section class="editorial-category-trio">
        <?php
        $main_cats = get_categories(['hide_empty' => false, 'number' => 3]);
        foreach ($main_cats as $category):
            $cat_posts = new WP_Query([
                'cat'            => $category->term_id,
                'posts_per_page' => 2,
            ]);
        ?>
        <div class="category-column-panel">
            <div class="cat-col-heading">
                <h3 class="cat-col-title"><?= esc_html($category->name); ?></h3>
                <a href="<?= esc_url(get_category_link($category)); ?>" class="cat-col-link">Xem thêm &rarr;</a>
            </div>

            <div class="cat-col-stream">
                <?php while ($cat_posts->have_posts()): $cat_posts->the_post(); ?>
                <article class="cat-story-card">
                    <div class="cat-story-img">
                        <a href="<?= esc_url(get_the_permalink()); ?>">
                            <?php cn_the_thumb(get_the_ID(), 'card', 'cat-thumb-img'); ?>
                        </a>
                    </div>
                    <h4 class="cat-story-title">
                        <a href="<?= esc_url(get_the_permalink()); ?>"><?= esc_html(get_the_title()); ?></a>
                    </h4>
                    <p class="cat-story-excerpt">
                        <?= esc_html(wp_trim_words(get_the_excerpt(), 16)); ?>
                    </p>
                    <div class="cat-story-meta">
                        <span>🕐 <?= cn_time_ago(); ?></span>
                        <span>&bull;</span>
                        <span>👁️ <?= number_format(cn_get_views()); ?></span>
                    </div>
                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </section>

</main><!-- .dt-container -->

<?php get_footer(); ?>
