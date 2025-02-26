<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Woo_Subscription_Coupon {

    private $subscribers_page_title = 'Подписчики';

    public function __construct() {
        add_action( 'admin_init', [ $this, 'create_subscribers_page' ] );
        add_action( 'woocommerce_before_checkout_form', [ $this, 'display_subscription_form' ], 15 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'wp_ajax_subscribe_for_coupon', [ $this, 'process_subscription' ] );
        add_action( 'wp_ajax_nopriv_subscribe_for_coupon', [ $this, 'process_subscription' ] );
    }

    /**
     * ✅ Создаёт страницу "Подписчики" (Работает при входе в админку)
     */
    public function create_subscribers_page() {
        $page_title = $this->subscribers_page_title;

        // Проверяем, существует ли страница
        $existing_pages = get_posts([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'title'       => $page_title,
            'numberposts' => 1,
        ]);

        if ( empty( $existing_pages ) ) {
            $page_id = wp_insert_post([
                'post_title'   => $page_title,
                'post_content' => '<!-- Подписчики будут записываться здесь -->',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);

            if ( $page_id ) {
                error_log('✅ Страница подписчиков успешно создана: ' . $page_id);
            } else {
                error_log('❌ Ошибка при создании страницы подписчиков!');
            }
        } else {
            error_log('⚠ Страница подписчиков уже существует.');
        }
    }

    /**
     * ✅ Форма подписки на странице оформления заказа
     */
    public function display_subscription_form() {
        ?>
        <div id="subscription-coupon-section">
            <h3>Получите скидку 10%</h3>
            <p>Введите ваш email, чтобы получить скидку на первый заказ.</p>
            <input type="email" id="subscription-email" placeholder="Ваш email" required>
            <button id="subscribe-button">Получить купон</button>
            <p id="subscription-message"></p>
        </div>
        <?php
    }

    /**
     * ✅ Подключает стили и JavaScript
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'woo-subscription-coupon-js',
            plugin_dir_url( __FILE__ ) . '../assets/js/custom-checkout.js',
            [ 'jquery' ],
            '1.0',
            true
        );

        wp_localize_script('woo-subscription-coupon-js', 'ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }


    /**
     * ✅ Отправляет email с купоном, используя шаблон
     */
    private function send_coupon_email( $email, $coupon_code ) {
        // Загружаем шаблон письма
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/email-template.php';
        $message = ob_get_clean();

        // Заголовки для HTML-письма
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Ваш магазин <no-reply@yourstore.com>',
        ];


        // Отправляем email
        wp_mail( $email, 'Ваш купон на 10% скидку', $message, $headers );
    }

    /**
     * ✅ Обрабатывает подписку и отправку купона
     */
    public function process_subscription() {
        $email = sanitize_email( $_POST['email'] );

        if ( ! is_email( $email ) ) {
            wp_send_json([ 'message' => 'Некорректный email' ]);
        }

        // Проверяем, есть ли уже подписчик
        if ( $this->is_subscriber_exists( $email ) ) {
            wp_send_json([ 'message' => 'Вы уже подписаны!' ]);
        }

        // Генерируем купон
        $coupon_code = 'DISCOUNT-' . wp_generate_password( 6, false );
        $this->create_coupon( $coupon_code );

        // Записываем email в страницу "Подписчики"
        $this->add_subscriber( $email, $coupon_code );


        // Отправляем email с купоном
        // wp_mail( $email, 'Ваш купон на 10% скидку', 'Ваш код купона: ' . $coupon_code );

        $this->send_coupon_email( $email, $coupon_code );

        wp_send_json([ 'message' => 'Купон отправлен на вашу почту!' ]);
    }

    /**
     * ✅ Проверяет, существует ли email в странице подписчиков
     */
    private function is_subscriber_exists( $email ) {
        $query = new WP_Query([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'title'       => $this->subscribers_page_title,
        ]);

        if ( ! $query->have_posts() ) {
            return false;
        }

        $page = $query->posts[0];
        return strpos( $page->post_content, $email ) !== false;
    }

    /**
     * ✅ Добавляет подписчика в страницу "Подписчики" в виде таблицы
     */
    private function add_subscriber( $email, $coupon_code ) {
        $query = new WP_Query([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'title'       => $this->subscribers_page_title,
        ]);

        if ( ! $query->have_posts() ) {
            return;
        }

        $page_id = $query->posts[0]->ID;
        $existing_content = get_post_field( 'post_content', $page_id );

        // Если таблицы ещё нет, создаём заголовки
        if ( strpos( $existing_content, '<table' ) === false ) {
            $new_content = '<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">
            <tr>
                <th>Email</th>
                <th>Купон</th>
                <th>Дата подписки</th>
            </tr>';
        } else {
            // Удаляем закрывающий тег `</table>` перед добавлением строки
            $new_content = substr( $existing_content, 0, -8 );
        }

        // Добавляем новую строку в таблицу
        $new_content .= "<tr>
        <td>$email</td>
        <td>$coupon_code</td>
        <td>" . date( 'Y-m-d H:i:s' ) . "</td>
    </tr>";

        // Закрываем таблицу
        $new_content .= '</table>';

        // Обновляем страницу "Подписчики"
        wp_update_post([
            'ID'           => $page_id,
            'post_content' => $new_content
        ]);
    }


    /**
     * ✅ Создаёт купон WooCommerce
     */
    private function create_coupon( $coupon_code ) {
        $coupon = new WC_Coupon();
        $coupon->set_code( $coupon_code );
        $coupon->set_discount_type( 'percent' );
        $coupon->set_amount( 10 );
        $coupon->set_individual_use( true );
        $coupon->set_usage_limit( 1 );
        $coupon->set_description( 'Скидка 10% за подписку' );
        $coupon->save();
    }



}
