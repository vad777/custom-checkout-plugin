<?php
/**
 * Woo Side Mini Cart Manual
 *
 * Этот файл содержит класс `Woo_Side_Mini_Cart_Manual`, который управляет боковой мини-корзиной WooCommerce.
 * Функционал:
 * - Добавление мини-корзины в `wp_footer`
 * - Подключение стилей и скриптов
 * - Обновление мини-корзины через AJAX (добавление, удаление, изменение количества)
 * - Генерация HTML мини-корзины
 * - Обработка AJAX-запросов для изменения количества товаров в корзине
 *
 * @package    WooCommerce
 * @subpackage Woo Side Mini Cart
 * @author     Your Name
 * @version    1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Класс Woo_Side_Mini_Cart_Manual
 *
 * Управляет боковой мини-корзиной WooCommerce.
 */
class Woo_Side_Mini_Cart_Manual_Code {

    public function __construct()
    {
        add_action('wp_enqueue_scripts',[ $this, 'enqueue_scripts']);
        add_action('wp_footer', [$this, 'render_side_cart']);
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'plugin-style',
            plugin_dir_url(__FILE__ ) .'style.css',
            array(),
            '1.3',
            'all'
        );


        wp_enqueue_script(
            'plugin_script', plugin_dir_url(__FILE__) . 'script/js',
            array('jquery'),
            '1.3',
            true
        );
    }
    public  function  render_side_cart() { ?>
        <p>
            Title
            <div class="woo-side-mini-cart__items">
                <?php echo $this->generate_cart_html(); ?>
            </div>
        </p>


   <?php }


     public  function generate_cart_html(){
         echo '<p> Mini Cart</p>';

   }


}


new Woo_Side_Mini_Cart_Manual_Code();