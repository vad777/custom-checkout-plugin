jQuery(document).ready(function($) {

    function openSideCart() {
        $('#woo-side-mini-cart').addClass('woo-side-mini-cart--active');
    }

    function closeSideCart() {
        $('#woo-side-mini-cart').removeClass('woo-side-mini-cart--active');
    }

    $('.woo-side-mini-cart__close, .woo-side-mini-cart__overlay').on('click', closeSideCart);

    // ✅ Принудительное обновление мини-корзины после добавления товара
    $(document.body).on('added_to_cart', function() {
        console.log('Товар добавлен! Обновляем мини-корзину...');
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'GET',
            data: { action: 'woocommerce_get_refreshed_fragments' },
            success: function(response) {
                if (response && response.fragments && response.fragments['div.woo-side-mini-cart__items']) {
                    $('.woo-side-mini-cart__items').html(response.fragments['div.woo-side-mini-cart__items']);
                    openSideCart();
                }
            },
            error: function() {
                console.error('Ошибка обновления мини-корзины.');
            }
        });
    });

    // ✅ Обновление количества товара по AJAX при клике на `+` или `-`
    $(document).on('click', '.qty-increase, .qty-decrease', function() {
        var inputField = $(this).siblings('.cart-qty-input');
        var cartItemKey = inputField.data('cart_item_key');
        var currentQty = parseInt(inputField.val());

        if ($(this).hasClass('qty-increase')) {
            currentQty++;
        } else if ($(this).hasClass('qty-decrease') && currentQty > 1) {
            currentQty--;
        }

        inputField.val(currentQty);
        updateCartQuantity(cartItemKey, currentQty);
    });

    // ✅ Обновление количества при изменении в `input`
    $(document).on('change', '.cart-qty-input', function() {
        var cartItemKey = $(this).data('cart_item_key');
        var newQty = parseInt($(this).val());

        if (isNaN(newQty) || newQty < 1) {
            newQty = 1;
            $(this).val(1);
        }

        updateCartQuantity(cartItemKey, newQty);
    });

    // ✅ Функция для обновления количества товара в корзине
    function updateCartQuantity(cartItemKey, newQty) {
        $.post(ajax_object.ajax_url, {
            action: 'update_cart_item_qty',
            cart_item_key: cartItemKey,
            new_qty: newQty
        }, function(response) {
            if (response.success && response.data.cart_html) {
                $('.woo-side-mini-cart__items').html(response.data.cart_html);
                openSideCart();
            } else {
                console.error('Ошибка обновления количества:', response);
            }
        });
    }

    // ✅ Принудительное обновление после удаления товара
    $(document.body).on('removed_from_cart', function() {
        console.log('Товар удалён! Обновляем мини-корзину...');
        $(document.body).trigger('wc_fragment_refresh');
    });

    // ✅ Принудительное обновление фрагментов после их загрузки
    $(document.body).on('wc_fragments_refreshed', function(event, fragments) {
        if (fragments && fragments['div.woo-side-mini-cart__items']) {
            $('.woo-side-mini-cart__items').html(fragments['div.woo-side-mini-cart__items']);
        }
        openSideCart();
    });

});
