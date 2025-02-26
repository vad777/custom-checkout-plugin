<?php
if (!defined('ABSPATH')) {
    exit; // Защита от прямого доступа
}

class Custom_Checkout_Plugin {


    public function __construct() {

        add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_assets']);

        // Добавляем кастомное поле
        add_action('woocommerce_after_order_notes',   [$this,'add_custom_checkout_field']);

        // Сохраняем кастомное поле в заказ
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_custom_checkout_field']);

        add_action( 'wp', [$this, 'move_woocommerce_payment_section'] );

        add_filter( 'woocommerce_cart_item_name', [$this, 'add_product_image_to_checkout_table'] , 10, 3);

        add_filter('woocommerce_checkout_fields', [$this, 'custom_move_email_field']);

    }

    public function enqueue_custom_assets() {
        if (is_checkout()) {
            wp_enqueue_style(
                'custom-checkout-style',
                plugin_dir_url(__FILE__) . '../assets/css/custom-checkout.css',
                array(),
                '1.3',
                'all'
            );
        }
    }


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

    public function save_custom_checkout_field($order_id) {
        if (!empty($_POST['custom_message'])) {
            update_post_meta($order_id, 'custom_message', sanitize_text_field($_POST['custom_message']));
        }
    }

    public function move_woocommerce_payment_section() {

        remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

        add_action( 'woocommerce_after_order_notes', 'woocommerce_checkout_payment', 999 );
    }

    public function add_product_image_to_checkout_table( $name, $cart_item, $cart_item_key ) {
        // Получаем объект товара
        $product = $cart_item['data'];

        // Проверяем, есть ли изображение у товара
        $thumbnail = $product->get_image( [50, 50] ); // Размер миниатюры 50x50px

        // Добавляем изображение перед названием товара
        $name = '<span class="checkout-product-thumbnail">' . $thumbnail . '</span> ' . $name;

        return $name;
    }

    public function custom_move_email_field( $fields ) {

        $fields['billing']['billing_email']['priority'] = 5;

        return $fields;
    }


}
?>
