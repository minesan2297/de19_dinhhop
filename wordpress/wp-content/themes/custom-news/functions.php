<?php
/**
 * functions.php – Custom News Theme
 * Đề 19: Website Tin tức – DTC245201006
 */

defined('ABSPATH') || exit;

/* ── Theme Setup ── */
add_action('after_setup_theme', function () {
    load_theme_textdomain('custom-news', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);
    add_theme_support('custom-logo', ['height' => 60, 'width' => 200]);
    add_theme_support('automatic-feed-links');

    // Kích thước ảnh thumbnail tùy chỉnh
    add_image_size('hero',     1200, 480, true);  // Hero featured
    add_image_size('card',      600, 338, true);  // 16:9 card
    add_image_size('thumb',     200, 150, true);  // List thumbnail
    add_image_size('sidebar',   320, 200, true);  // Sidebar widget

    // Đăng ký menu
    register_nav_menus([
        'primary'  => __('Menu chính', 'custom-news'),
        'footer'   => __('Menu footer', 'custom-news'),
        'category' => __('Menu danh mục', 'custom-news'),
    ]);
});

/* ── Enqueue Scripts & Styles ── */
add_action('wp_enqueue_scripts', function () {
    // Main stylesheet
    wp_enqueue_style(
        'custom-news-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );

    // Font Awesome icons
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
        [],
        '6.5.0'
    );

    // Main JS
    wp_enqueue_script(
        'custom-news-js',
        get_template_directory_uri() . '/assets/js/main.js',
        ['jquery'],
        wp_get_theme()->get('Version'),
        true
    );

    // Truyền dữ liệu cho JS
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

/* ── Custom Post Type: Tin tức ── */
add_action('init', function () {
    register_post_type('news', [
        'labels' => [
            'name'               => 'Tin tức',
            'singular_name'      => 'Tin tức',
            'add_new_item'       => 'Thêm tin tức mới',
            'edit_item'          => 'Sửa tin tức',
            'view_item'          => 'Xem tin tức',
            'search_items'       => 'Tìm kiếm tin tức',
            'not_found'          => 'Không tìm thấy tin tức',
        ],
        'public'       => true,
        'has_archive'  => true,
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments'],
        'menu_icon'    => 'dashicons-newspaper',
        'rewrite'      => ['slug' => 'tin-tuc'],
        'show_in_rest' => true,  // Block editor support
    ]);

    // Taxonomy: Chuyên mục tin tức
    register_taxonomy('news_category', 'news', [
        'labels' => [
            'name'          => 'Chuyên mục',
            'singular_name' => 'Chuyên mục',
            'add_new_item'  => 'Thêm chuyên mục',
        ],
        'hierarchical' => true,
        'public'       => true,
        'rewrite'      => ['slug' => 'chuyen-muc'],
        'show_in_rest' => true,
    ]);
});

/* ── Helper Functions ── */

/**
 * Lấy danh mục đầu tiên của bài viết
 */
function cn_get_first_category(int $post_id = 0): ?WP_Term {
    $cats = get_the_category($post_id ?: get_the_ID());
    return $cats[0] ?? null;
}

/**
 * Hiển thị badge danh mục
 */
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

/**
 * Thời gian đăng bài (tiếng Việt)
 */
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

/**
 * Đọc thời gian (phút)
 */
function cn_reading_time(int $post_id = 0): int {
    $content  = get_post_field('post_content', $post_id ?: get_the_ID());
    $word_count = str_word_count(strip_tags($content));
    return max(1, (int) ceil($word_count / 200));
}

/**
 * Thumbnail với fallback
 */
function cn_thumbnail(string $size = 'card', string $class = ''): void {
    if (has_post_thumbnail()) {
        the_post_thumbnail($size, ['class' => $class, 'loading' => 'lazy']);
    } else {
        printf(
            '<img src="%s/assets/img/no-image.svg" alt="No image" class="%s" loading="lazy">',
            esc_url(get_template_directory_uri()),
            esc_attr($class)
        );
    }
}

/* ── Security: tắt các thứ không cần thiết ── */
remove_action('wp_head', 'wp_generator');               // Ẩn version WP
remove_action('wp_head', 'wlwmanifest_link');           // Windows Live Writer
remove_action('wp_head', 'rsd_link');                   // Really Simple Discovery
remove_action('wp_head', 'wp_shortlink_wp_head');       // Shortlink
add_filter('the_generator', '__return_empty_string');   // Ẩn version trong feed

/* ── Custom Excerpt ── */
add_filter('excerpt_length', fn() => 25);
add_filter('excerpt_more', fn() => '...');

/* ── AJAX: Load more posts ── */
add_action('wp_ajax_load_more_posts',        'cn_ajax_load_more');
add_action('wp_ajax_nopriv_load_more_posts', 'cn_ajax_load_more');

function cn_ajax_load_more(): void {
    check_ajax_referer('custom_news_nonce', 'nonce');

    $page     = absint($_POST['page'] ?? 1);
    $category = absint($_POST['category'] ?? 0);

    $args = [
        'post_type'      => 'post',
        'posts_per_page' => 6,
        'paged'          => $page,
        'cat'            => $category ?: 0,
    ];

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        wp_send_json_error(['message' => 'Không còn bài viết']);
    }

    ob_start();
    while ($query->have_posts()) {
        $query->the_post();
        get_template_part('template-parts/card');
    }
    wp_reset_postdata();

    wp_send_json_success([
        'html'     => ob_get_clean(),
        'has_more' => $query->max_num_pages > $page,
    ]);
}

/* ── Breadcrumb ── */
function cn_breadcrumb(): void {
    echo '<nav class="breadcrumb">';
    echo '<a href="' . home_url() . '">Trang chủ</a>';

    if (is_category()) {
        echo ' / ' . single_cat_title('', false);
    } elseif (is_single()) {
        $cat = cn_get_first_category();
        if ($cat) {
            echo ' / <a href="' . get_category_link($cat) . '">' . esc_html($cat->name) . '</a>';
        }
        echo ' / ' . get_the_title();
    } elseif (is_search()) {
        echo ' / Kết quả tìm kiếm: ' . get_search_query();
    }

    echo '</nav>';
}

/* ── View Count (đơn giản dùng post meta) ── */
function cn_increment_views(int $post_id): void {
    $views = (int) get_post_meta($post_id, 'cn_views', true);
    update_post_meta($post_id, 'cn_views', $views + 1);
}

function cn_get_views(int $post_id = 0): int {
    return (int) get_post_meta($post_id ?: get_the_ID(), 'cn_views', true);
}
