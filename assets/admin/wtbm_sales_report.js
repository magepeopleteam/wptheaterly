(function ($) {

    $(document).on( 'click', '.wtbm_get_daily_report', function () {
        $(".wtbm_sales_report").fadeOut();
        let dataReport = $(this).attr( 'data-wtbm-report' );

        let popupContainerId = '';
        if( dataReport === 'daily' ){
            popupContainerId = $('#wtbm_dily_sales_report');
        }else if( dataReport === 'movie' ){
            popupContainerId = $('#wtbm_dily_movies_performance');
        }else if( dataReport === 'theater' ){
            popupContainerId = $('#wtbm_daily_theater_performance');
        }else{
            popupContainerId = $('#wtbm_dily_sales_report');
        }

        wtbmSalesOpenPopup( popupContainerId );
    })

    $(document).on( 'click', '.wtbm_sales_close-btn', function () {
        wtbmSalesClosePopup();
    })
    function wtbmSalesOpenPopup( popupContainerId ) {
        $("#wtbm_sales_popup").fadeIn();
        popupContainerId.fadeIn();
    }
    function wtbmSalesClosePopup() {
       $("#wtbm_sales_popup").fadeOut();
    }

}(jQuery));