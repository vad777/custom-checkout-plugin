jQuery(document).ready(function($) {
    $('#subscribe-button').on('click', function() {

        var email = $('#subscription-email').val();

        if (!email) {
            $('#subscription-message').text('Введите email!');
            return;
        }
        console.log(ajax_object.ajax_url);

        $.post(ajax_object.ajax_url, {
            action: 'subscribe_for_coupon',
            email: email
        }, function(response) {
            $('#subscription-message').text(response.message);
        }, 'json');
    });
});
