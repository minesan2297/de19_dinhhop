<?php
/**
 * single.php – Trang đọc bài viết đơn lẻ
 */
get_header();

// Tăng view counter
if (is_single()) {
    cn_increment_views(get_the_ID());
}
?>

<div class="container">
    <div class="site-main">

        <!-- ── Nội dung bài viết ── -->
        <main class="main-content">

            <?php while (have_posts()): the_post(); ?>

            <?php cn_breadcrumb(); ?>

            <article class="single-post" id="post-<?= get_the_ID(); ?>">

                <!-- Header bài viết -->
                <div class="post-header">
                    <div class="post-categories">
                        <?php
                        $cats = get_the_category();
                        foreach ($cats as $cat) {
                            printf(
                                '<a href="%s" class="post-cat-badge">%s</a>',
                                esc_url(get_category_link($cat)),
                                esc_html($cat->name)
                            );
                        }
                        ?>
                    </div>

                    <h1 class="post-title"><?= get_the_title(); ?></h1>

                    <div class="post-meta-bar">
                        <div class="author">
                            <?= get_avatar(get_the_author_meta('email'), 36, '', '', ['class' => 'author-avatar']); ?>
                            <div>
                                <div style="font-size:.8rem;color:#999;">Tác giả</div>
                                <?= esc_html(get_the_author()); ?>
                            </div>
                        </div>
                        <span>📅 <?= get_the_date('d/m/Y'); ?></span>
                        <span>🕐 <?= cn_time_ago(); ?></span>
                        <span>📖 <?= cn_reading_time(); ?> phút đọc</span>
                        <span>👁️ <?= number_format(cn_get_views()); ?> lượt xem</span>
                    </div>
                </div>

                <!-- Ảnh đại diện -->
                <?php if (has_post_thumbnail()): ?>
                <div class="post-featured-img">
                    <?php the_post_thumbnail('hero', ['loading' => 'eager']); ?>
                </div>
                <?php endif; ?>

                <!-- Nội dung bài viết -->
                <div class="post-content">
                    <?php the_content(); ?>

                    <?php
                    wp_link_pages([
                        'before' => '<div class="page-links">Trang:',
                        'after'  => '</div>',
                    ]);
                    ?>
                </div>

                <!-- Tags -->
                <?php $tags = get_the_tags(); if ($tags): ?>
                <div class="post-tags">
                    <span style="color:#999;font-size:.85rem;">🏷️ Tags:</span>
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?= get_tag_link($tag); ?>" class="tag-badge"><?= esc_html($tag->name); ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </article>

            <!-- ── Chia sẻ bài viết ── -->
            <div class="share-box" style="background:#fff;border-radius:8px;padding:20px;margin:20px 0;box-shadow:var(--shadow);">
                <p style="font-weight:700;margin-bottom:12px;">📤 Chia sẻ bài viết:</p>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <?php $url = urlencode(get_the_permalink()); $title = urlencode(get_the_title()); ?>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $url; ?>"
                       target="_blank" style="background:#1877f2;color:#fff;padding:8px 16px;border-radius:6px;font-size:.875rem;">
                        Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= $url; ?>&text=<?= $title; ?>"
                       target="_blank" style="background:#1da1f2;color:#fff;padding:8px 16px;border-radius:6px;font-size:.875rem;">
                        Twitter
                    </a>
                </div>
            </div>

            <!-- ── Bài liên quan ── -->
            <?php
            $related = new WP_Query([
                'post__not_in'   => [get_the_ID()],
                'posts_per_page' => 3,
                'cat'            => implode(',', wp_get_post_categories(get_the_ID())),
                'orderby'        => 'rand',
            ]);
            if ($related->have_posts()):
            ?>
            <div style="margin-top:32px;">
                <div class="section-title">📚 Bài viết liên quan</div>
                <div class="posts-grid" style="grid-template-columns:repeat(3,1fr);">
                    <?php while ($related->have_posts()): $related->the_post(); ?>
                    <article class="card">
                        <?php if (has_post_thumbnail()): ?>
                        <a href="<?= get_the_permalink(); ?>" class="card-thumb">
                            <?php the_post_thumbnail('card'); ?>
                        </a>
                        <?php endif; ?>
                        <div class="card-body">
                            <?php cn_category_badge(); ?>
                            <h3 class="card-title">
                                <a href="<?= get_the_permalink(); ?>"><?= get_the_title(); ?></a>
                            </h3>
                            <div class="card-meta">
                                <span>🕐 <?= cn_time_ago(); ?></span>
                            </div>
                        </div>
                    </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── Bình luận ── -->
            <?php if (comments_open() || get_comments_number()): ?>
                <div style="background:#fff;border-radius:8px;padding:24px;margin-top:24px;box-shadow:var(--shadow);">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>

            <?php endwhile; ?>

        </main>

        <?php get_sidebar(); ?>

    </div>
</div>

<?php get_footer(); ?>
