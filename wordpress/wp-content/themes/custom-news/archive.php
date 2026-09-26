<?php
/**
 * archive.php – Trang danh mục / lưu trữ
 */
get_header();
?>

<div class="container">
    <div class="site-main">
        <main class="main-content">

            <!-- Tiêu đề danh mục -->
            <div class="archive-header" style="background:#fff;border-radius:8px;padding:24px;margin-bottom:24px;box-shadow:var(--shadow);border-left:4px solid var(--primary);">
                <h1 style="font-size:1.5rem;font-weight:800;">
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
                        echo 'Lưu trữ';
                    }
                    ?>
                </h1>
                <?php if (is_category() && category_description()): ?>
                    <p style="color:#666;margin-top:8px;"><?= category_description(); ?></p>
                <?php endif; ?>
                <p style="color:#999;font-size:.85rem;margin-top:8px;">
                    Tìm thấy <?= $wp_query->found_posts; ?> bài viết
                </p>
            </div>

            <?php cn_breadcrumb(); ?>

            <?php if (have_posts()): ?>
            <div class="section-title">Bài viết</div>
            <div class="posts-grid">
                <?php while (have_posts()): the_post(); ?>
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
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php
                echo paginate_links([
                    'prev_text' => '← Trước',
                    'next_text' => 'Sau →',
                ]);
                ?>
            </div>

            <?php else: ?>
            <div class="no-posts">
                <p>😢 Không có bài viết nào trong danh mục này.</p>
                <a href="<?= home_url(); ?>" style="color:var(--primary);">← Về trang chủ</a>
            </div>
            <?php endif; ?>

        </main>

        <?php get_sidebar(); ?>
    </div>
</div>

<?php get_footer(); ?>
