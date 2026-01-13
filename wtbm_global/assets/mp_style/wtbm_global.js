
(function ($) {
    "use strict";
    $(document).on('click', '.wtbm [data-href]', function () {
        let href = $(this).data('href');
        if (href) {
            window.location.href = href;
        }
    });
}(jQuery));