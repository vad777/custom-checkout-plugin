<?php
/**
 * Plugin Name: Custom Checkout Plugin
 * Plugin URI:  https://example.com
 * Description: Кастомизация страницы оформления заказа WooCommerce.
 * Version:     1.0
 * Author:      Ваше Имя
 * Author URI:  https://example.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Подключаем класс
require_once plugin_dir_path(__FILE__) . 'includes/class-custom-checkout.php';

// Запускаем плагин
function run_custom_checkout_plugin() {
    if (class_exists('WooCommerce')) {
        new Custom_Checkout_Plugin();
    } else {
        error_log('WooCommerce НЕ найден!');
    }
}
add_action('plugins_loaded', 'run_custom_checkout_plugin');



