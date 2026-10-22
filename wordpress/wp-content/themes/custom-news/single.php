<?php
/**
 * single.php – Trang đọc bài viết đơn lẻ
 * Đề 19: Cổng Thông Tin Tin Tức – CNTT K23C
 */
get_header();

if (is_single()) {
    cn_increment_views(get_the_ID());
}
?>

<div class="container main-container">
    <div class="site-main-layout">

        <!-- ── Nội dung bài viết ── -->
        <main class="main-content-column">

            <?php while (have_posts()): the_post(); ?>

            <?php cn_breadcrumb(); ?>

            <article class="single-post-card" id="post-<?= get_the_ID(); ?>">

                <!-- Header bài viết -->
                <div class="post-header-area">
                    <div class="post-cat-list">
                        <?php
                        $cats = get_the_category();
                        foreach ($cats as $cat) {
                            printf(
                                '<a href="%s" class="badge-cat badge-primary">%s</a>',
                                esc_url(get_category_link($cat)),
                                esc_html($cat->name)
                            );
                        }
                        ?>
                    </div>

                    <h1 class="single-post-title"><?= esc_html(get_the_title()); ?></h1>

                    <div class="single-post-meta">
                        <div class="author-info">
                            <span>✍️ <?= esc_html(get_the_author()); ?></span>
                        </div>
                        <span>&bull;</span>
                        <span>📅 <?= get_the_date('d/m/Y'); ?></span>
                        <span>&bull;</span>
                        <span>🕐 <?= cn_time_ago(); ?></span>
                        <span>&bull;</span>
                        <span>📖 <?= cn_reading_time(); ?> phút đọc</span>
                        <span>&bull;</span>
                        <span>👁️ <?= number_format(cn_get_views()); ?> lượt xem</span>
                    </div>
                </div>

                <!-- Ảnh đại diện bài viết -->
                <div class="single-featured-wrap">
                    <?php cn_the_thumb(get_the_ID(), 'hero', 'single-hero-img'); ?>
                </div>

                <!-- Nội dung chính bài viết -->
                <div class="single-content-body">
                    <?php the_content(); ?>
                </div>

                <!-- Tags -->
                <?php $tags = get_the_tags(); if ($tags): ?>
                <div class="single-post-tags">
                    <span class="tags-label">🏷️ Từ khóa:</span>
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?= esc_url(get_tag_link($tag)); ?>" class="tag-pill">#<?= esc_html($tag->name); ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </article>

            <!-- ── Bài viết liên quan ── -->
            <?php
            $orig_cats = wp_get_post_categories(get_the_ID());
            if ($orig_cats) {
                $related = new WP_Query([
                    'category__in'   => $orig_cats,
                    'post__not_in'   => [get_the_ID()],
                    'posts_per_page' => 3,
                ]);
                if ($related->have_posts()):
            ?>
            <div class="related-posts-section">
                <h3 class="related-title">📰 BÀI VIẾT LIÊN QUAN</h3>
                <div class="tech-card-trio">
                    <?php while ($related->have_posts()): $related->the_post(); ?>
                    <div class="tech-item-card">
                        <div class="tech-thumb-wrap">
                            <a href="<?= esc_url(get_the_permalink()); ?>">
                                <?php cn_the_thumb(get_the_ID(), 'card', 'tech-img'); ?>
                            </a>
                        </div>
                        <div class="tech-info">
                            <h4 class="tech-title">
                                <a href="<?= esc_url(get_the_permalink()); ?>"><?= esc_html(get_the_title()); ?></a>
                            </h4>
                            <div class="tech-meta">
                                <span>🕐 <?= cn_time_ago(); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
            <?php endif; } ?>

            <?php endwhile; ?>

        </main><!-- .main-content-column -->

        <!-- ── Sidebar bên phải ── -->
        <?php get_sidebar(); ?>

    </div><!-- .site-main-layout -->
</div><!-- .container -->

<?php get_footer(); ?>
