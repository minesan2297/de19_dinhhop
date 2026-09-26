<?php
/**
 * index.php – Trang chủ / Danh sách tin tức
 */
get_header();
?>

<div class="container">

    <!-- ── Hero: Bài nổi bật ── -->
    <?php
    $hero_query = new WP_Query([
        'posts_per_page' => 1,
        'tag'            => 'featured',
    ]);
    if (!$hero_query->have_posts()) {
        $hero_query = new WP_Query(['posts_per_page' => 1]);
    }

    if ($hero_query->have_posts()):
        $hero_query->the_post();
    ?>
    <section class="hero-section">
        <div class="hero-post">
            <?php if (has_post_thumbnail()): ?>
                <?php the_post_thumbnail('hero'); ?>
            <?php endif; ?>
            <div class="hero-content">
                <?php
                $cat = cn_get_first_category();
                if ($cat): ?>
                    <a href="<?= get_category_link($cat); ?>" class="hero-cat"><?= esc_html($cat->name); ?></a>
                <?php endif; ?>
                <h1 class="hero-title">
                    <a href="<?= get_the_permalink(); ?>"><?= get_the_title(); ?></a>
                </h1>
                <div class="hero-meta">
                    ✍️ <?= get_the_author(); ?>
                    &nbsp;•&nbsp;
                    🕐 <?= cn_time_ago(); ?>
                    &nbsp;•&nbsp;
                    📖 <?= cn_reading_time(); ?> phút đọc
                    &nbsp;•&nbsp;
                    👁️ <?= number_format(cn_get_views(get_the_ID())); ?> lượt xem
                </div>
            </div>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    endif;
    ?>

    <!-- ── Layout chính: Posts + Sidebar ── -->
    <div class="site-main">

        <!-- ── Nội dung chính ── -->
        <main class="main-content">

            <!-- Tin mới nhất -->
            <div class="section-title">🗞️ Tin mới nhất</div>

            <?php
            $paged = get_query_var('paged') ?: 1;
            $main_query = new WP_Query([
                'post_type'      => 'post',
                'posts_per_page' => 6,
                'paged'          => $paged,
                'post__not_in'   => [$hero_query->posts[0]->ID ?? 0],
            ]);
            ?>

            <?php if ($main_query->have_posts()): ?>
            <div class="posts-grid" id="posts-grid">
                <?php while ($main_query->have_posts()): $main_query->the_post(); ?>
                <article class="card">
                    <?php if (has_post_thumbnail()): ?>
                    <a href="<?= get_the_permalink(); ?>" class="card-thumb">
                        <?php the_post_thumbnail('card', ['loading' => 'lazy']); ?>
                    </a>
                    <?php endif; ?>
                    <div class="card-body">
                        <?php cn_category_badge(); ?>
                        <h2 class="card-title">
                            <a href="<?= get_the_permalink(); ?>"><?= get_the_title(); ?></a>
                        </h2>
                        <p class="card-excerpt"><?= get_the_excerpt(); ?></p>
                        <div class="card-meta">
                            <span>✍️ <?= get_the_author(); ?></span>
                            <span>🕐 <?= cn_time_ago(); ?></span>
                            <span>📖 <?= cn_reading_time(); ?> phút</span>
                        </div>
                    </div>
                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <!-- Load More -->
            <?php if ($main_query->max_num_pages > 1): ?>
            <div style="text-align:center; margin-top:24px;">
                <button
                    id="load-more-btn"
                    class="btn-load-more"
                    data-page="<?= $paged; ?>"
                    data-max="<?= $main_query->max_num_pages; ?>"
                    style="background:var(--primary);color:#fff;border:none;padding:12px 32px;
                           border-radius:8px;font-size:1rem;cursor:pointer;font-weight:600;">
                    Xem thêm tin tức
                </button>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="no-posts">
                <p>😢 Chưa có bài viết nào. <a href="<?= admin_url('post-new.php'); ?>">Đăng bài ngay!</a></p>
            </div>
            <?php endif; ?>

            <!-- ── Tin theo danh mục ── -->
            <?php
            $categories = get_categories(['hide_empty' => true, 'number' => 4]);
            foreach ($categories as $category):
                $cat_posts = new WP_Query([
                    'cat'            => $category->term_id,
                    'posts_per_page' => 4,
                ]);
                if (!$cat_posts->have_posts()) continue;
            ?>
            <div style="margin-top:40px;">
                <div class="section-title">
                    <a href="<?= get_category_link($category); ?>" style="color:inherit;">
                        📁 <?= esc_html($category->name); ?>
                    </a>
                    <a href="<?= get_category_link($category); ?>"
                       style="font-size:.8rem;font-weight:400;color:var(--primary);float:right;">
                        Xem thêm →
                    </a>
                </div>
                <div class="posts-list">
                    <?php while ($cat_posts->have_posts()): $cat_posts->the_post(); ?>
                    <div class="list-item">
                        <?php if (has_post_thumbnail()): ?>
                        <div class="thumb">
                            <a href="<?= get_the_permalink(); ?>">
                                <?php the_post_thumbnail('thumb', ['loading' => 'lazy']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        <div class="info">
                            <span class="cat"><?= esc_html($category->name); ?></span>
                            <h3><a href="<?= get_the_permalink(); ?>"><?= get_the_title(); ?></a></h3>
                            <div class="meta">🕐 <?= cn_time_ago(); ?> &nbsp;•&nbsp; 👁️ <?= number_format(cn_get_views()); ?></div>
                        </div>
                    </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
            <?php endforeach; ?>

        </main><!-- .main-content -->

        <!-- ── Sidebar ── -->
        <?php get_sidebar(); ?>

    </div><!-- .site-main -->
</div><!-- .container -->

<?php get_footer(); ?>
