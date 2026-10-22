<?php
/**
 * functions.php – Custom News Theme (Upgraded Version)
 * Đề 19: Website Tin tức / Cổng thông tin – CNTT K23C
 * Sinh viên: Đinh Bách Hợp – DTC245201006
 */

defined('ABSPATH') || exit;

/* ── Theme Setup ── */
add_action('after_setup_theme', function () {
    load_theme_textdomain('custom-news', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);
    add_theme_support('custom-logo', ['height' => 70, 'width' => 240]);
    add_theme_support('automatic-feed-links');

    // Kích thước ảnh thumbnail
    add_image_size('hero',    1200, 520, true);
    add_image_size('card',     640, 360, true);
    add_image_size('thumb',    240, 160, true);
    add_image_size('sidebar',  320, 200, true);

    // Đăng ký menu
    register_nav_menus([
        'primary'  => __('Menu chính', 'custom-news'),
        'footer'   => __('Menu footer', 'custom-news'),
        'category' => __('Menu danh mục', 'custom-news'),
    ]);
});

/* ── Enqueue Scripts & Styles ── */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'design-tokens',
        get_template_directory_uri() . '/assets/design-tokens.css',
        [],
        '2.1.0'
    );

    wp_enqueue_style(
        'custom-news-style',
        get_stylesheet_uri(),
        ['design-tokens'],
        '2.1.0'
    );

    wp_enqueue_script(
        'custom-news-js',
        get_template_directory_uri() . '/assets/main.js',
        ['jquery'],
        '2.0.0',
        true
    );

    wp_localize_script('custom-news-js', 'customNews', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('custom_news_nonce'),
        'siteUrl' => get_site_url(),
    ]);
});

/* ── Sidebar / Widget Areas ── */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="widget-title">',
        'after_title'   => '</div><div class="widget-body">',
    ];

    register_sidebar(array_merge($config, [
        'name'        => 'Sidebar chính',
        'id'          => 'sidebar-main',
        'description' => 'Khu vực widget bên phải',
    ]));

    register_sidebar(array_merge($config, [
        'name'        => 'Footer Cột 1',
        'id'          => 'footer-1',
    ]));
    register_sidebar(array_merge($config, [
        'name'        => 'Footer Cột 2',
        'id'          => 'footer-2',
    ]));
    register_sidebar(array_merge($config, [
        'name'        => 'Footer Cột 3',
        'id'          => 'footer-3',
    ]));
});

/* ── Helper Functions ── */

function cn_get_first_category(int $post_id = 0): ?WP_Term {
    $cats = get_the_category($post_id ?: get_the_ID());
    return $cats[0] ?? null;
}

function cn_category_badge(int $post_id = 0): void {
    $cat = cn_get_first_category($post_id);
    if ($cat) {
        printf(
            '<a href="%s" class="card-cat">%s</a>',
            esc_url(get_category_link($cat)),
            esc_html($cat->name)
        );
    }
}

function cn_time_ago(int $post_id = 0): string {
    $time = get_the_time('U', $post_id ?: get_the_ID());
    $diff = time() - $time;

    return match (true) {
        $diff < 60        => 'Vừa xong',
        $diff < 3600      => floor($diff / 60) . ' phút trước',
        $diff < 86400     => floor($diff / 3600) . ' giờ trước',
        $diff < 604800    => floor($diff / 86400) . ' ngày trước',
        default           => get_the_date('d/m/Y', $post_id ?: get_the_ID()),
    };
}

function cn_reading_time(int $post_id = 0): int {
    $content = get_post_field('post_content', $post_id ?: get_the_ID());
    $word_count = str_word_count(strip_tags($content));
    return max(1, (int) ceil($word_count / 200));
}

/**
 * Lấy URL ảnh thumbnail hỗ trợ featured image, post meta và fallback
 */
function cn_get_thumb_url(int $post_id = 0, string $size = 'card'): string {
    $pid = $post_id ?: get_the_ID();
    if (has_post_thumbnail($pid)) {
        $url = get_the_post_thumbnail_url($pid, $size);
        if ($url) return $url;
    }
    $meta_img = get_post_meta($pid, 'cn_featured_img_url', true);
    if (!empty($meta_img)) return $meta_img;

    $fallbacks = [
        'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80',
        'http://192.168.21.129/wp-content/themes/custom-news/assets/images/bong-da-phui.jpg',
    ];
    return $fallbacks[$pid % count($fallbacks)];
}

function cn_the_thumb(int $post_id = 0, string $size = 'card', string $class = ''): void {
    $url = cn_get_thumb_url($post_id, $size);
    $title = esc_attr(get_the_title($post_id));
    printf(
        '<img src="%s" alt="%s" class="%s" loading="lazy">',
        esc_url($url),
        $title,
        esc_attr($class)
    );
}

function cn_increment_views(int $post_id): void {
    $views = (int) get_post_meta($post_id, 'cn_views', true);
    update_post_meta($post_id, 'cn_views', $views + 1);
}

function cn_get_views(int $post_id = 0): int {
    return (int) get_post_meta($post_id ?: get_the_ID(), 'cn_views', true);
}

function cn_breadcrumb(): void {
    echo '<nav class="breadcrumb">';
    echo '<a href="' . home_url() . '">Trang chủ</a>';

    if (is_category()) {
        echo ' &rsaquo; ' . single_cat_title('', false);
    } elseif (is_single()) {
        $cat = cn_get_first_category();
        if ($cat) {
            echo ' &rsaquo; <a href="' . esc_url(get_category_link($cat)) . '">' . esc_html($cat->name) . '</a>';
        }
        echo ' &rsaquo; <span>' . esc_html(get_the_title()) . '</span>';
    } elseif (is_search()) {
        echo ' &rsaquo; Kết quả: ' . get_search_query();
    }

    echo '</nav>';
}

/* ── Custom Excerpt ── */
add_filter('excerpt_length', fn() => 24);
add_filter('excerpt_more', fn() => '...');

/* ── Security ── */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
add_filter('the_generator', '__return_empty_string');

