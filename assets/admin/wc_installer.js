jQuery(document).ready(function($) {

    // 1. Initialize the Dialog
    /*var $dialog = $("#wtbm-dialog-container").dialog({
        autoOpen: true,
        modal: true,
        width: 400,
        resizable: false,
        dialogClass: 'wp-dialog',
        closeOnEscape: false,
        open: function(event, ui) {
            $(".ui-dialog-titlebar-close", ui.dialog || ui).hide();
        }
    });*/

    // 2. Handle the Installation
    $('#wtbm-install-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $spinner = $btn.siblings('.spinner');

        $btn.attr('disabled', true).text('Processing...');
        $spinner.addClass('is-active');

        // FIX: Use wtbmInstallerData instead of the generic ajaxurl
        $.post(wtbmInstallerData.ajaxurl, {
            action: 'wtbm_install_woocommerce',
            // FIX: security must match the key 'security' in your PHP check_ajax_referer
            security: wtbmInstallerData.nonce 
        }, function(response) {
            if (response.success) {
                $btn.text('Success! Redirecting...');
                $dialog.dialog('close');
                window.location.href = response.data;
            } else {
                // response.data now contains the specific error from PHP
                alert('Installation Error: ' + response.data);
                $btn.attr('disabled', false).text('Try Again');
                $spinner.removeClass('is-active');
            }
        });
    });


    $('#wtbm-install-pdf-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $spinner = $btn.siblings('.spinner');

        $btn.prop('disabled', true).text('Downloading from GitHub...');
        $spinner.addClass('is-active');

        $.post(wtbmInstallerData.ajaxurl, {
            action: 'wtbm_install_required_plugins',
            security: wtbmInstallerData.nonce
        }, function(response) {
            if (response.success) {
                $btn.text('Success! Redirecting...');
                window.location.href = response.data;
            } else {
                alert(response.data);
                $btn.prop('disabled', false).text('Try Again');
                $spinner.removeClass('is-active');
            }
        });
    });

jQuery(document).ready(function($) {
    $(document).on('click', '.wtbm-install-pdf-support', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var $spinner = $btn.find('.spinner');
        var nonce = $btn.data('nonce');

        if ($btn.hasClass('updating')) return;

        $btn.addClass('updating').css('opacity', '0.7');
        $spinner.addClass('is-active');

        $.post(ajaxurl, {
            action: 'wtbm_install_pdf_component',
            security: nonce
        }, function(response) {
            if (response.success) {
                // Success: Reload the page to swap the button
                location.reload();
            } else {
                alert(response.data || 'Installation failed.');
                $btn.removeClass('updating').css('opacity', '1');
                $spinner.removeClass('is-active');
            }
        }).fail(function() {
            alert('Server error occurred.');
            $btn.removeClass('updating');
            $spinner.removeClass('is-active');
        });
    });
});


});