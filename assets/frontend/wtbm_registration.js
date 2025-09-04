(function ($) {


    $(document).on('click', '.wtbm_booking_date_date_card', function () {
        $('#wtbm_bookingDateSelector .wtbm_booking_date_date_card').removeClass('active');
        $(this).addClass('active');

        let date = $(this).data('date').trim();
        $.ajax({
            url: wtbm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wtbm_get_movies_data_by_date',
                date: date,
                nonce: wtbm_ajax.nonce,
            },
            success: function(response) {

                if( response.data  ) {
                    $("#wtbm_movieSection").html(response.data);
                }else{
                    $("#wtbm_movieSection").html( '<h6>No Movies Found</h6>');
                }
                console.log(response);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    });

    $(document).on('click', '.wtbm_booking_movie_card', function () {

        $("#wtbm_seatSection").fadeOut();

        $('.wtbm_booking_movie_card').removeClass('wtbm_movieActive');
        $(this).addClass('wtbm_movieActive');
        let movie_id = $(this).attr('data-movie-id').trim();
        let activeDate = $('#wtbm_bookingDateSelector .wtbm_booking_date_date_card.active').data('date');
        $.ajax({
            url: wtbm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wtbm_get_theater_show_time_data',
                date: activeDate,
                movie_id: movie_id,
                nonce: wtbm_ajax.nonce,
            },
            success: function(response) {
                if( response.data  ) {
                    $("#wtbm_displayHallsList").html(response.data);
                }else{
                    $("#wtbm_displayHallsList").html( '<h6>No Movies Found</h6>');
                }
                $("#wtbm_hallSection").fadeIn();
                console.log( response.data );
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    });

    $(document).on('click', '.wtbm_timeSlot', function () {

        $("#wtbm_seatSection").fadeOut();

        let theaterId = $(this).attr('data-wtbm-theater').trim();
        let movieTimeSlot = $(this).attr('data-time-slot').trim();
        let movieDate = $('#wtbm_bookingDateSelector .wtbm_booking_date_date_card.active').data('date');

        $.ajax({
            url: wtbm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wtbm_get_theater_seat_map_data',
                theater_id: theaterId,
                movie_time_slot: movieTimeSlot,
                movie_date: movieDate,
                nonce: wtbm_ajax.nonce,
            },
            success: function(response) {
                if( response.data  ) {
                    $("#wtbm_seatsGrid").html(response.data.wtbm_seatMaps);
                }else{
                    $("#wtbm_seatsGrid").html( '<h6>No Movies Found</h6>');
                }

                $("#wtbm_seatSection").fadeIn();
                $("#wtbm_hallSection").fadeIn();
                console.log( response.data );
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });

    });






}(jQuery));
