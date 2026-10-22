<?php
/**
 * footer.php – Custom News Theme
 * Impeccable Editorial Design Tokens
 * Đề 19: Quản trị và Triển khai Hệ thống Phần mềm – CNTT K23C
 * Sinh viên: Đinh Bách Hợp – DTC245201006
 */
?>
</div><!-- .editorial-canvas -->

<!-- ═══════════════════════════════════════ BROADSHEET FOOTER ══ -->
<footer class="editorial-footer">
    <div class="dt-container">

        <!-- Top Footer Boundary Rule -->
        <div class="footer-top-rule"></div>

        <div class="footer-editorial-grid">
            
            <!-- Cột 1: Thông tin Xuất bản & Đề tài -->
            <div class="f-col f-about">
                <div class="f-brand">
                    <span class="f-logo-badge">ĐỀ 19</span>
                    <h4 class="f-logo-title">CỔNG THÔNG TIN TIN TỨC</h4>
                </div>
                <p class="f-statement">
                    Bản tin điện tử thực hành được xây dựng trong khuôn khổ học phần <strong>Quản trị và Triển khai Hệ thống Phần mềm</strong>. Hệ thống thiết lập theo mô hình phân tán nhiều tầng với Nginx Reverse Proxy, WordPress Core, MySQL 8.0 cùng hệ sinh thái giám sát Prometheus, Grafana và Loki.
                </p>
                <div class="f-registry-box">
                    <div><strong>Sinh viên thực hiện:</strong> Đinh Bách Hợp</div>
                    <div><strong>Mã số sinh viên:</strong> <code>DTC245201006</code></div>
                    <div><strong>Lớp sinh hoạt:</strong> CNTT K23C &bull; Khoa Công nghệ Thông tin</div>
                </div>
            </div>

            <!-- Cột 2: Cổng dịch vụ nối 3 Website -->
            <div class="f-col f-tri-services">
                <h5 class="f-col-label">HỆ THỐNG 3 NỀN TẢNG DỊCH VỤ</h5>
                <ul class="f-service-list">
                    <li>
                        <a href="<?= esc_url(home_url('/')); ?>" class="f-service-anchor">
                            <span class="f-srv-tag">01. CMS</span>
                            <div class="f-srv-txt">
                                <strong>Website Tin tức</strong>
                                <small>Nginx Reverse Proxy &bull; Cổng 80</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="http://192.168.21.129/pma/" target="_blank" class="f-service-anchor">
                            <span class="f-srv-tag">02. DB</span>
                            <div class="f-srv-txt">
                                <strong>phpMyAdmin Console</strong>
                                <small>Cơ sở dữ liệu MySQL 8.0 &bull; /pma/</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="http://192.168.21.129:3000" target="_blank" class="f-service-anchor">
                            <span class="f-srv-tag">03. OPS</span>
                            <div class="f-srv-txt">
                                <strong>Grafana Telemetry</strong>
                                <small>Prometheus TSDB &bull; Cổng 3000</small>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Cột 3: Chuyên mục báo chí -->
            <div class="f-col f-navigation">
                <h5 class="f-col-label">DANH MỤC XUẤT BẢN</h5>
                <ul class="f-category-list">
                    <?php
                    $footer_categories = get_categories(['hide_empty' => false, 'number' => 6]);
                    foreach ($footer_categories as $fc):
                    ?>
                    <li>
                        <a href="<?= esc_url(get_category_link($fc)); ?>">
                            <?= esc_html($fc->name); ?>
                            <span class="f-count">[<?= $fc->count; ?>]</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div><!-- .footer-editorial-grid -->

        <!-- Bottom Copyright Statement -->
        <div class="footer-colophon">
            <div class="colophon-left">
                &copy; <?= date('Y'); ?> <strong>Cổng Thông Tin Tin Tức – Đề 19</strong> &mdash; Dự án học phần Quản trị và Triển khai Hệ thống Phần mềm.
            </div>
            <div class="colophon-right">
                <span>Node: <code>192.168.21.129</code></span> &bull;
                <span>Nginx 1.25</span> &bull;
                <span>MySQL 8.0</span> &bull;
                <span>Prometheus / Grafana</span>
            </div>
        </div>

    </div>
</footer>

<!-- Nút trở về đầu trang -->
<button id="back-to-top" class="editorial-back-to-top" title="Cuộn lên đầu trang" aria-label="Cuộn lên đầu trang">
    &uarr; ĐẦU TRANG
</button>

<?php wp_footer(); ?>
</body>
</html>
