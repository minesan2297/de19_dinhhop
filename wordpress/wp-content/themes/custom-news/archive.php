<?php
/**
 * archive.php – Trang danh mục / lưu trữ bài viết
 * Đề 19: Cổng Thông Tin Tin Tức – CNTT K23C
 */
get_header();
?>

<div class="container main-container">
    <div class="site-main-layout">
        <main class="main-content-column">

            <!-- Tiêu đề danh mục -->
            <div class="archive-header-box">
                <span class="archive-badge">CHUYÊN MỤC</span>
                <h1 class="archive-title">
                    <?php
                    if (is_category()) {
                        echo '📁 ' . single_cat_title('', false);
                    } elseif (is_tag()) {
                        echo '🏷️ Tag: ' . single_tag_title('', false);
                    } elseif (is_date()) {
                        echo '📅 Lưu trữ: ' . get_the_date('F Y');
                    } elseif (is_author()) {
                        echo '✍️ Tác giả: ' . get_the_author();
                    } else {
                        echo 'Lưu trữ tin tức';
                    }
                    ?>
                </h1>
                <?php if (is_category() && category_description()): ?>
                    <p class="archive-desc"><?= category_description(); ?></p>
                <?php endif; ?>
                <div class="archive-count">Tìm thấy <?= $wp_query->found_posts; ?> bài viết</div>
            </div>

            <?php cn_breadcrumb(); ?>

            <?php if (have_posts()): ?>
            <div class="news-card-grid">
                <?php while (have_posts()): the_post(); ?>
                <article class="news-card">
                    <div class="card-image-wrap">
                        <a href="<?= esc_url(get_the_permalink()); ?>">
                            <?php cn_the_thumb(get_the_ID(), 'card', 'card-img'); ?>
                        </a>
                        <?php cn_category_badge(get_the_ID()); ?>
                    </div>
                    <div class="card-details">
                        <h3 class="card-title">
                            <a href="<?= esc_url(get_the_permalink()); ?>"><?= esc_html(get_the_title()); ?></a>
                        </h3>
                        <p class="card-summary">
                            <?= esc_html(get_the_excerpt()); ?>
                        </p>
                        <div class="card-footer-meta">
                            <div class="meta-left">
                                <span>✍️ <?= get_the_author(); ?></span>
                                <span>&bull;</span>
                                <span>🕐 <?= cn_time_ago(); ?></span>
                            </div>
                            <div class="meta-right">
                                <span>👁️ <?= number_format(cn_get_views()); ?></span>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>

            <div class="pagination-wrapper">
                <?php
                echo paginate_links([
                    'prev_text' => '← Trang trước',
                    'next_text' => 'Trang sau →',
                ]);
                ?>
            </div>

            <?php else: ?>
            <div class="no-news-box">
                <p>Không tìm thấy bài viết nào trong chuyên mục này.</p>
            </div>
            <?php endif; ?>

        </main><!-- .main-content-column -->

        <!-- Sidebar -->
        <?php get_sidebar(); ?>

    </div><!-- .site-main-layout -->
</div><!-- .container -->

<?php get_footer(); ?>
