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

    $(document).on('click', '#wtbm_booking_data_download_btn',function () {
        let bookingIds = [];
        $("#wtbm_bookings_table_body tr").each(function () {
            bookingIds.push(jQuery(this).data("order-id"));
        });
        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wtbm_prepare_booking_pdf',
                ids: bookingIds,
                nonce: mptrs_admin_ajax.nonce
            },
            success: function (res) {
                if (res.success) {
                    window.location.href = res.data.download_url;
                } else {
                    alert(res.data);
                }
            }
        });
    });

    $(document).on('click', '#wtbm_booking_data_csv_btn', function(e){
        alert('clicked');

        e.preventDefault();


        let bookingIds = [];
        $("#wtbm_bookings_table_body tr").each(function () {
            bookingIds.push(jQuery(this).data("order-id"));
        });

        if(bookingIds.length === 0){
            alert('Select at least one booking');
            return;
        }
        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wtbm_prepare_booking_csv',
                ids: bookingIds,
                nonce: mptrs_admin_ajax.nonce
            },
            success: function(res){
                if(res.success){
                    // Redirect to download URL → browser automatically downloads CSV
                    window.location.href = res.data.download_url;
                } else {
                    alert(res.data || 'Failed to prepare CSV.');
                }
            },
            error: function(xhr,status,error){
                console.error(error);
                alert('AJAX error: ' + status);
            }
        });
    });

    $(document).on('click','.wtbm_show_filter', function(e) {
        e.preventDefault();
        $('#wtbm_booking_filters').slideToggle(300);
    });

    $(document).on('click','.wtbm_delete_booking', function(e) {
        let $this = $(this);
        let bookingId = $(this).closest('tr').attr('data-order-id');

        if (!confirm('Are you sure you want to delete this booking?')) {
            return; // stop if cancelled
        }

        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wtbm_delete_booking',
                booking_id: bookingId,
                nonce: mptrs_admin_ajax.nonce
            },
            success: function(response) {
                $this.closest('tr').fadeOut(200);
            }
        });
    });

    $(document).on('click','.wtbm_edit_booking', function(e) {
        let bookingId = $(this).closest('tr').attr('data-order-id');
        let booking_edit_overlay = $('.wtbm_booking_edit_overlay');
        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wtbm_get_booking_html',
                booking_id: bookingId,
                nonce: mptrs_admin_ajax.nonce
            },
            success: function(response) {
                booking_edit_overlay.remove();
                booking_edit_overlay.fadeIn(200);
                $('body').append(response);
                booking_edit_overlay.fadeIn(200);
            }
        });

    });

    $(document).on('click', '.wtbm_booking_edit_close_icon, .wtbm_booking_edit_close_btn', function() {
        $('.wtbm_booking_edit_overlay').fadeOut(200, function() {
            $(this).remove();
        });
    });


    $(document).on('click', '#wtbm_update_booking', function (e) {
        e.preventDefault();

        let $body = $('.wtbm_booking_edit_body');

        let data = {
            action: 'wtbm_update_booking_data',
            booking_id: $body.find('.wtbm_booking_edit_id').val(),
            order_id: $body.find('.wtbm_order_edit_id').val(),

            attendee_name:  $body.find('[name="wtbm_booking_attendee_name"]').val(),
            attendee_phone: $body.find('[name="wtbm_booking_attendee_phone"]').val(),
            attendee_email: $body.find('[name="wtbm_booking_attendee_email"]').val(),

            seat_number: $body.find('[name="wtbm_booking_seat_number"]').val(),
            seat_ids: $body.find('[name="wtbm_booking_seat_ids"]').val(),
            booking_status: $body.find('[name="wtbm_booking_status"]').val(),

            movie_id: $body.find('.wtbm_movie_edit_id').val(),
            theater_id: $body.find('.wtbm_theater_edit_id').val(),
            movie_time: $body.find('.wtbm_movie_time').val(),
            nonce: mptrs_admin_ajax.nonce
        };

        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#wtbm_update_booking').prop('disabled', true).text('Updating...');
            },
            success: function (response) {
                if (response.success) {
                    // alert('Booking updated successfully!');
                    $('.wtbm_booking_edit_overlay').fadeOut(200, function () {
                        $(this).remove();
                    });
                    location.reload();
                } else {
                    alert(response.data || 'Update failed');
                }
            },
            error: function () {
                alert('Something went wrong. Please try again.');
            },
            complete: function () {
                $('#wtbm_update_booking').prop('disabled', false).text('Update');
            }
        });
    });

    $(document).on('click', '.wtbm_get_available_seat', function () {
        let parent = $("#wtbm_booking_edit_overlay");
        let theaterId = parent.find('input[name="wtbm_edit_theater_id"]').val();
        let movieTimeSlot = parent.find('input[name="wtbm_movie_time_slot"]').val();
        let movieId = parent.find('input[name="wtbm_edit_movie_id"]').val();
        let movieDate = '2026-01-21';

        $.ajax({
            url: wtbm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wtbm_get_theater_available_seats',
                theater_id: theaterId,
                activeMovieId: movieId,
                movie_time_slot: movieTimeSlot,
                movie_date: movieDate,
                nonce: wtbm_ajax.nonce,
            },
            success: function(response) {
                if( response.data  ) {
                    $("#wtbm_available_seats").html(response.data.wtbm_seatMaps);
                }else{
                    $("#wtbm_available_seats").html( '<h6>No Seats Found Found</h6>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });

        // console.log( theaterId, movieTimeSlot, movieId );
    });

    $(document).on('click', '.remove-seat', function () {
        const seatEl   = $(this).closest('.wtbm_seat_name');
        const seatNum  = seatEl.data('seat').toString();
        const seatId   = seatEl.data('seat-id').toString();

        const seatNumInput = $('input[name="wtbm_booking_seat_number"]');
        let seatNums = seatNumInput.val().split(',').map(s => s.trim());

        seatNums = seatNums.filter(val => val !== seatNum);
        seatNumInput.val(seatNums.join(', '));

        const seatIdInput = $('input[name="wtbm_booking_seat_ids"]');
        let seatIds = seatIdInput.val().split(',').map(s => s.trim());

        seatIds = seatIds.filter(val => val !== seatId);
        seatIdInput.val(seatIds.join(', '));
        seatEl.fadeOut(200, function () {
            $(this).remove();
        });
    });

    $(document).on('click', '.wtbm_add_seat_name', function () {

        const seatEl  = $(this);
        const seatNum = String(seatEl.data('seat')).trim();
        const seatId  = String(seatEl.data('seat-id')).trim();

        const seatNumInput = $('input[name="wtbm_booking_seat_number"]');
        let seatNums = seatNumInput.val()
            ? seatNumInput.val().split(',').map(s => s.trim())
            : [];

        if (!seatNums.includes(seatNum)) {
            seatNums.push(seatNum);
        }

        seatNumInput.val(seatNums.join(', '));
        const seatIdInput = $('input[name="wtbm_booking_seat_ids"]');
        let seatIds = seatIdInput.val()
            ? seatIdInput.val().split(',').map(s => s.trim())
            : [];

        if (!seatIds.includes(seatId)) {
            seatIds.push(seatId);
        }

        seatIdInput.val(seatIds.join(', '));
        seatEl.fadeOut(200);
        const selectedSeat = `
        <span class="wtbm_seat_name" data-seat-id="${seatId}" data-seat="${seatNum}">
            ${seatNum}
            <span class="remove-seat">✕</span>
        </span>
    `;

        $(".wtbm_booked_seats_display").append(selectedSeat);
    });



}(jQuery));