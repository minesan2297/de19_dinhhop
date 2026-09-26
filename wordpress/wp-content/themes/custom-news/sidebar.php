<?php
/**
 * sidebar.php – Sidebar chính
 */
?>
<aside class="sidebar">

    <!-- Tìm kiếm -->
    <div class="widget">
        <div class="widget-title">🔍 Tìm kiếm</div>
        <div class="widget-body">
            <form class="search-form" role="search" method="get" action="<?= esc_url(home_url('/')); ?>">
                <input type="search" name="s" placeholder="Tìm kiếm tin tức..."
                       value="<?= get_search_query(); ?>" required>
                <button type="submit">Tìm</button>
            </form>
        </div>
    </div>

    <!-- Danh mục -->
    <div class="widget">
        <div class="widget-title">📁 Danh mục</div>
        <div class="widget-body" style="padding:8px 0;">
            <ul class="cat-list">
                <?php
                $cats = get_categories(['hide_empty' => true]);
                foreach ($cats as $cat) {
                    printf(
                        '<li><a href="%s">%s <span class="count">%d</span></a></li>',
                        esc_url(get_category_link($cat)),
                        esc_html($cat->name),
                        $cat->count
                    );
                }
                ?>
            </ul>
        </div>
    </div>

    <!-- Bài đọc nhiều nhất -->
    <div class="widget">
        <div class="widget-title">🔥 Đọc nhiều nhất</div>
        <div class="widget-body" style="padding:8px 0;">
            <?php
            $popular = new WP_Query([
                'posts_per_page' => 5,
                'meta_key'       => 'cn_views',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
            ]);
            $i = 1;
            while ($popular->have_posts()): $popular->the_post();
            ?>
            <div class="list-item" style="border:none;box-shadow:none;border-bottom:1px solid #eee;border-radius:0;">
                <div class="thumb" style="width:80px;height:60px;flex-shrink:0;">
                    <?php if (has_post_thumbnail()): ?>
                        <a href="<?= get_the_permalink(); ?>">
                            <?php the_post_thumbnail('thumb', ['style' => 'width:80px;height:60px;object-fit:cover;']); ?>
                        </a>
                    <?php else: ?>
                        <div style="width:80px;height:60px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                            <?= $i; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="info" style="padding:8px 12px;">
                    <h3 style="font-size:.85rem;"><a href="<?= get_the_permalink(); ?>"><?= get_the_title(); ?></a></h3>
                    <div class="meta">👁️ <?= number_format(cn_get_views()); ?> lượt xem</div>
                </div>
            </div>
            <?php $i++; endwhile; wp_reset_postdata(); ?>
        </div>
    </div>

    <!-- Tags -->
    <div class="widget">
        <div class="widget-title">🏷️ Tags phổ biến</div>
        <div class="widget-body">
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                <?php
                $tags = get_tags(['number' => 20, 'orderby' => 'count', 'order' => 'DESC']);
                foreach ($tags as $tag) {
                    printf(
                        '<a href="%s" class="tag-badge" style="background:var(--light);color:var(--gray);padding:4px 12px;border-radius:20px;font-size:.8rem;">%s</a>',
                        esc_url(get_tag_link($tag)),
                        esc_html($tag->name)
                    );
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Widget area nếu có -->
    <?php if (is_active_sidebar('sidebar-main')): ?>
        <?php dynamic_sidebar('sidebar-main'); ?>
    <?php endif; ?>

</aside>
