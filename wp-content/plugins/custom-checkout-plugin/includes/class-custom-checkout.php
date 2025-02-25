<?php
if (!defined('ABSPATH')) {
    exit; // Защита от прямого доступа
}

class Custom_Checkout_Plugin {

    public function __construct() {

        add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_assets']);

        // Добавляем кастомное поле
        add_action('woocommerce_after_order_notes', [$this, 'add_custom_checkout_field']);

        // Сохраняем кастомное поле в заказ
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_custom_checkout_field']);

    }
    public function enqueue_custom_assets() {
        if (is_checkout()) {
            wp_enqueue_style('custom-checkout-style', plugin_dir_url(__FILE__) . '../assets/css/custom-checkout.css');
            wp_enqueue_script('custom-checkout-js', plugin_dir_url(__FILE__) . '../assets/js/custom-checkout.js', array('jquery'), '1.0', true);
        }
    }



    // Добавляем кастомное поле
    public function add_custom_checkout_field($checkout) {
        echo '<div id="custom_checkout_field"><h3>Дополнительная информация</h3>';
        woocommerce_form_field('custom_message', [
            'type'        => 'text',
            'class'       => ['form-row-wide'],
            'label'       => 'Ваш комментарий к заказу',
            'placeholder' => 'Введите комментарий...',
            'required'    => false,
        ], $checkout->get_value('custom_message'));
        echo '</div>';
    }

    // Сохраняем кастомное поле в заказ
    public function save_custom_checkout_field($order_id) {
        if (!empty($_POST['custom_message'])) {
            update_post_meta($order_id, 'custom_message', sanitize_text_field($_POST['custom_message']));
        }
    }
}
?>
