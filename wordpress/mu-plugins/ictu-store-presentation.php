<?php
/**
 * Plugin Name: ICTU Store Presentation
 * Description: Site-wide demo notice and footer navigation for the ICTU student project.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function ictu_store_render_demo_notice(): void {
    echo '<div class="ictu-demo-ribbon" role="note"><strong>WEBSITE DEMO ĐỒ ÁN ICTU</strong><span>Sản phẩm, giá và tồn kho là dữ liệu mẫu. Không nhập dữ liệu cá nhân hoặc thanh toán thật.</span></div>';
}
add_action('astra_header_before', 'ictu_store_render_demo_notice', 5);

function ictu_store_render_footer_navigation(): void {
    if (!has_nav_menu('footer_menu')) {
        return;
    }
    echo '<nav class="ictu-footer-navigation" aria-label="Liên kết hỗ trợ và chính sách">';
    wp_nav_menu([
        'theme_location' => 'footer_menu',
        'container' => false,
        'menu_class' => 'ictu-footer-links',
        'depth' => 1,
        'fallback_cb' => false,
    ]);
    echo '</nav>';
}
add_action('astra_footer_before', 'ictu_store_render_footer_navigation', 5);
