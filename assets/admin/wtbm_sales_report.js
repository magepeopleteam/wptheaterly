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


    $(document).on( 'click','#wtbm_find_booking', function (e) {
        e.preventDefault();
        let $this = $(this);

        // Collect all filter values
        let filterData = {
            action: 'wtbm_filter_bookings',

            order_search: $('#order_search_filter').val(),
            movie_id: $('#wtbm_movie_filter').val(),
            theater_id: $('#wtbm_theater_filter').val(),
            show_time: $('#wtbm_showtime_filter').val(),
            show_date: $('#wtbm_booking_date_filter').val(),
            booking_status: $('#wtbm_order_status_filter').val(),

            nonce: mptrs_admin_ajax.nonce
        };

        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: 'POST',
            data: filterData,
            beforeSend: function () {
                // Optional loader
                $this.text('Filtering...');
            },
            success: function (response) {
                if (response.success) {
                    $this.text('Apply Filters');
                    // Replace booking list HTML
                    $('#wtbm_bookings_table_body').html(response.data.html);
                    // $("#wtbm_showing_count").text( response.data.total_post_count );
                    $("#wtbm_showing_count").text( 10 );
                    $("#wtbm_total_booking_count").text( response.data.total_booking );
                    if( response.data.total_booking < 10 ){
                        $("#load-more-section").fadeOut();
                    }else{
                        $("#load-more-section").fadeIn();
                    }
                } else {
                    alert(response.data.message || 'No results found');
                }
            },
            error: function () {
                alert('AJAX error occurred');
            }
        });
    });

    // Clear filters
    $('#wtbm_clear_find_booking').on('click', function () {
        $('#wtbm_booking_filters')
            .find('input, select')
            .val('');
    });

    function wtbmSalesOpenPopup( popupContainerId ) {
        $("#wtbm_sales_popup").fadeIn();
        popupContainerId.fadeIn();
    }
    function wtbmSalesClosePopup() {
       $("#wtbm_sales_popup").fadeOut();
    }

}(jQuery));