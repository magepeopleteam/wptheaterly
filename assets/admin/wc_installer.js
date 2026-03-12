jQuery(document).ready(function($) {
    $('#wtbm-install-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $spinner = $btn.siblings('.spinner');

        $btn.attr('disabled', true).text('Installing...');
        $spinner.addClass('is-active');

        $.post(ajaxurl, {
            action: 'wtbm_install_woocommerce',
            security: $btn.data('nonce')
        }, function(response) {
            if (response.success) {
                $btn.text('Success! Redirecting...');
                window.location.href = response.data; // Redirect to your status page
            } else {
                alert('Error: ' + response.data);
                $btn.attr('disabled', false).text('Try Again');
                $spinner.removeClass('is-active');
            }
        });
    });
});