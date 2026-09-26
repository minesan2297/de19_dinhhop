<?php
/**
 * footer.php – Custom News Theme
 */
?>
</div><!-- .site-wrapper -->

<!-- ═══════════════════════════════════════ FOOTER ══ -->
<footer class="site-footer">
    <div class="container">

        <div class="footer-grid">
            <!-- Cột 1: Giới thiệu -->
            <div class="footer-widget">
                <?php if (is_active_sidebar('footer-1')): ?>
                    <?php dynamic_sidebar('footer-1'); ?>
                <?php else: ?>
                    <h4>📰 <?php bloginfo('name'); ?></h4>
                    <p style="font-size:.875rem; line-height:1.7;"><?php bloginfo('description'); ?></p>
                    <p style="font-size:.8rem; margin-top:12px; color:#888;">
                        🏫 Đề 19 – CNTT K23C<br>
                        👨‍💻 Sinh viên: Đinh Bách Hợp<br>
                        🆔 DTC245201006
                    </p>
                <?php endif; ?>
            </div>

            <!-- Cột 2: Danh mục -->
            <div class="footer-widget">
                <?php if (is_active_sidebar('footer-2')): ?>
                    <?php dynamic_sidebar('footer-2'); ?>
                <?php else: ?>
                    <h4>Danh mục</h4>
                    <ul>
                        <?php
                        $cats = get_categories(['number' => 8, 'hide_empty' => true]);
                        foreach ($cats as $cat) {
                            printf(
                                '<li><a href="%s">%s <span style="color:#666">(%d)</span></a></li>',
                                esc_url(get_category_link($cat)),
                                esc_html($cat->name),
                                $cat->count
                            );
                        }
                        ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Cột 3: Bài mới nhất -->
            <div class="footer-widget">
                <?php if (is_active_sidebar('footer-3')): ?>
                    <?php dynamic_sidebar('footer-3'); ?>
                <?php else: ?>
                    <h4>Bài mới nhất</h4>
                    <ul>
                        <?php
                        $recent = new WP_Query(['posts_per_page' => 5]);
                        while ($recent->have_posts()): $recent->the_post();
                            printf(
                                '<li><a href="%s">%s</a><br><small style="color:#666">%s</small></li>',
                                get_the_permalink(),
                                get_the_title(),
                                cn_time_ago()
                            );
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div><!-- .footer-grid -->

        <div class="footer-bottom">
            <p>
                &copy; <?= date('Y'); ?> <strong><?php bloginfo('name'); ?></strong>.
                Đề 19 – Website Tin tức / Cổng thông tin &mdash;
                CNTT K23C &middot; Đinh Bách Hợp &middot; DTC245201006
            </p>
        </div>

    </div><!-- .container -->
</footer>

<?php wp_footer(); ?>
</body>
</html>
