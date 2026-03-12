jQuery(document).ready(function($) {
    // $('#wtbm-install-btn').on('click', function(e) {
    //     e.preventDefault();
    //     var $btn = $(this);
    //     var $spinner = $btn.siblings('.spinner');

    //     $btn.attr('disabled', true).text('Installing...');
    //     $spinner.addClass('is-active');

    //     $.post(ajaxurl, {
    //         action: 'wtbm_install_woocommerce',
    //         security: $btn.data('nonce')
    //     }, function(response) {
    //         if (response.success) {
    //             $btn.text('Success! Redirecting...');
    //             window.location.href = response.data; // Redirect to your status page
    //         } else {
    //             alert('Error: ' + response.data);
    //             $btn.attr('disabled', false).text('Try Again');
    //             $spinner.removeClass('is-active');
    //         }
    //     });
    // });
});


jQuery(document).ready(function($) {
    // 1. Initialize the Dialog
    var $dialog = $("#wtbm-dialog-container").dialog({
        autoOpen: true, // Opens immediately
        modal: true,    // Greys out the background
        width: 400,
        resizable: false,
        dialogClass: 'wp-dialog',
        closeOnEscape: false,
        open: function(event, ui) {
            // Hide the 'X' close button so they MUST choose an action
            $(".ui-dialog-titlebar-close", ui.dialog || ui).hide();
        }
    });

    // 2. Handle the Installation
    $('#wtbm-install-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $spinner = $btn.siblings('.spinner');

        $btn.attr('disabled', true).text('Processing...');
        $spinner.addClass('is-active');

        $.post(ajaxurl, {
            action: 'wtbm_install_woocommerce',
            security: $btn.data('nonce')
        }, function(response) {
            if (response.success) {
                // Success: Close dialog and redirect
                $dialog.dialog('close');
                window.location.href = response.data;
            } else {
                alert('Installation Error: ' + response.data);
                $btn.attr('disabled', false).text('Try Again');
                $spinner.removeClass('is-active');
            }
        });
    });
});