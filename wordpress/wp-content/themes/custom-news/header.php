<?php
/**
 * header.php – Custom News Theme
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php bloginfo('description'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ═══════════════════════════════════════ HEADER ══ -->
<header class="site-header">
    <div class="container">
        <div class="header-inner">

            <!-- Logo / Tên trang -->
            <div class="site-branding">
                <?php if (has_custom_logo()): ?>
                    <?php the_custom_logo(); ?>
                <?php else: ?>
                    <a href="<?= esc_url(home_url('/')); ?>" class="site-title">
                        📰 <span><?= esc_html(get_bloginfo('name')); ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Navigation -->
            <button class="nav-toggle" aria-label="Mở menu" onclick="this.nextElementSibling.classList.toggle('open')">
                ☰
            </button>
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class'     => 'main-nav',
                'container'      => false,
                'fallback_cb'    => function () {
                    // Fallback: hiển thị danh mục
                    echo '<ul class="main-nav">';
                    echo '<li><a href="' . home_url() . '">Trang chủ</a></li>';
                    $cats = get_categories(['number' => 6, 'hide_empty' => true]);
                    foreach ($cats as $cat) {
                        printf(
                            '<li><a href="%s">%s</a></li>',
                            esc_url(get_category_link($cat)),
                            esc_html($cat->name)
                        );
                    }
                    echo '</ul>';
                },
            ]);
            ?>

        </div><!-- .header-inner -->
    </div><!-- .container -->
</header>

<!-- ═══════════════════════════════════════ BREAKING NEWS ══ -->
<?php
$breaking = new WP_Query(['posts_per_page' => 5, 'tag' => 'breaking']);
if ($breaking->have_posts()):
?>
<div class="breaking-bar">
    <div class="container">
        <div class="breaking-inner">
            <span class="breaking-label">🔴 BREAKING</span>
            <div class="ticker-wrap">
                <div class="ticker">
                    <?php while ($breaking->have_posts()): $breaking->the_post(); ?>
                        <a href="<?= get_the_permalink(); ?>"><?= get_the_title(); ?></a>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════ MAIN CONTENT ══ -->
<div class="site-wrapper">
