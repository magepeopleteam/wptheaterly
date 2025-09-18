(function ($) {

    function wtbm_loader( text ){
        return `<h5>${text}</h5>`
    }

    $(document).on('click', '.wtbm_booking_date_date_card', function () {
        $('#wtbm_bookingDateSelector .wtbm_booking_date_date_card').removeClass('active');
        $(this).addClass('active');
        wtbm_time_slot_click_make_empty( 'wtbm_date' );


        let wtbm_movieSection = $("#wtbm_movieSection");
        let date = $(this).data('date').trim();
        let wtbm_dateFormated = new Date(date);
        let options = { month: 'short', day: '2-digit' };
        let wtbm_formatted = wtbm_dateFormated.toLocaleDateString('en-US', options) + ", " + wtbm_dateFormated.getFullYear().toString().slice(-2);
        $("#wtbm_summaryDateDisplay").text(wtbm_formatted);
        $("#wtbm_summeryDate").val(date);
        wtbm_movieSection.empty();
        $("#wtbm_hallSection").fadeOut();
        $("#wtbm_seatSection").fadeOut();
        wtbm_movieSection.append( wtbm_loader( 'Movie Loading...' ) );

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
                    wtbm_movieSection.html(response.data);
                }else{
                    wtbm_movieSection.html( '<h6>No Movies Found</h6>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    });

    $(document).on('click', '.wtbm_booking_movie_card', function () {

        $("#wtbm_seatSection").fadeOut();

        let click_btn = 'wtbm_movie';
        wtbm_time_slot_click_make_empty( click_btn );
        let movi_poster_url = $(this).find('.wtbm_booking_movies_poster img').attr("src");
        let movie_name = $(this).attr('data-movie-name').trim();
        let movie_duration = $(this).attr('data-movie-duration').trim();
        let movie_poster = movi_poster_url;
        let selectedMovie = `
                    <div class="selected-movie" style="background-image:url('${movi_poster_url}')"></div>
                    <div id="wtbm_movieName" style="font-weight: bold;">${movie_name}</div>
                    <div id="wtbm_movieDuration" style="color: #666; font-size: 12px;">${movie_duration}</div>
                `;

        $('.wtbm_booking_movie_card').removeClass('wtbm_movieActive');
        $(this).addClass('wtbm_movieActive');
        let movie_id = $(this).attr('data-movie-id').trim();

        $("#wtbm_summeryMovieId").val(movie_id);

        $("#wtbm_hallSection").fadeIn();

        let wtbm_displayHallsList = $("#wtbm_displayHallsList");
        wtbm_displayHallsList.empty();
        wtbm_displayHallsList.append( wtbm_loader( 'Show Time Loading...' ) );

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
                    wtbm_displayHallsList.html(response.data);
                    $("#wtbm_selectedMovieDisplay").html( selectedMovie );
                }else{
                    wtbm_displayHallsList.html( '<h6>No Movies Found</h6>');
                }
                $("#wtbm_hallSection").fadeIn();
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    });

    $(document).on('click', '.wtbm_timeSlot', function () {
        let this_class = $(this);
        let click_btn = 'time_slot'
        wtbm_time_slot_click_make_empty( click_btn );

        $("#wtbm_seatSection").fadeOut();
        $(".wtbm_timeSlot").removeClass( 'selected' );
        this_class.addClass('selected');

        let theaterId = $(this).attr('data-wtbm-theater').trim();
        let movieTimeSlot = $(this).attr('data-time-slot').trim();
        let theaterName = $(this).attr('data-wtbm-theater-name').trim();
        let movieDate = $('#wtbm_bookingDateSelector .wtbm_booking_date_date_card.active').data('date');
        let timeSlotDisplay = $(this).text();

        $("#wtbm_summeryTheaterId").val(theaterId);
        $("#wtbm_summeryTime").val(movieTimeSlot);

        $("#wtbm_summaryTheaterName").text(theaterName);
        $("#wtbm_summaryTimeSlot").text(timeSlotDisplay);

        let wtbm_seat_loader = $("#wtbm_seat_loader");
        wtbm_seat_loader.fadeIn();
        wtbm_seat_loader.empty();
        wtbm_seat_loader.append( wtbm_loader( 'Seat Map Loading...' ) );

        let activeMovieId = $(".wtbm_booking_movie_card.wtbm_movieActive").data("movie-id");

        $.ajax({
            url: wtbm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wtbm_get_theater_seat_map_data',
                theater_id: theaterId,
                activeMovieId: activeMovieId,
                movie_time_slot: movieTimeSlot,
                movie_date: movieDate,
                nonce: wtbm_ajax.nonce,
            },
            success: function(response) {
                wtbm_seat_loader.fadeOut();
                if( response.data  ) {
                    $("#wtbm_seatsGrid").html(response.data.wtbm_seatMaps);
                }else{
                    $("#wtbm_seatsGrid").html( '<h6>No Movies Found</h6>');
                }
                $("#wtbm_seatSection").fadeIn();
                $("#wtbm_hallSection").fadeIn();
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });

    });


    let wtbm_seatBooked = [];
    let wtbm_seatBookedName = [];
    let wtbm_total_price = 0;
    let wtbm_total_seat_count = 0;

    $(document).on( 'click', '.wtbm_mappedSeat', function (e) {
        e.preventDefault();
        const seatId = $(this).attr('id');
        const price = parseFloat($(this).data('price'));
        const seatNum = $(this).attr('data-seat-num');

        if (wtbm_seatBooked.includes( seatId ) ) {
            wtbm_seatBooked = wtbm_seatBooked.filter(seat => seat !== seatId);
            $("#"+seatId).children().css('background-color', '#2e8708');
            wtbm_total_price = wtbm_total_price - price;
            wtbm_total_seat_count--;
        } else {
            wtbm_seatBooked.push(seatId);
            $("#"+seatId).children().css('background-color', '#667eea');
            wtbm_total_price = wtbm_total_price + price;
            wtbm_total_seat_count++;
        }

        if (wtbm_seatBookedName.includes(seatNum)) {
            wtbm_seatBookedName = wtbm_seatBookedName.filter(seatName => seatName !== seatNum ); // Remove if exists
        } else {
            wtbm_seatBookedName.push( seatNum );
        }

        const seatSummary = wtbm_seatBookedName.join(", ");
        const seatSummaryIds = wtbm_seatBooked.join(", ");

        $("#wtbm_summaryQuantity").text( wtbm_total_seat_count );
        $("#wtbm_summaryTotal").text( wtbm_total_price+''+wtbm_ajax.wc_currency_symbol );
        $("#wtbm_summarySeats").text(seatSummary);


        $("#wtbm_summeryTotalAmount").val( wtbm_total_price );
        $("#wtbm_summerySeatNumber").val( seatSummary );
        $("#wtbm_summerySeatIds").val( seatSummaryIds );

        $("#wtbm_registrationSidebar").fadeIn();

        // console.log(`Seat ID: ${seatId}, Price: $${price}, Seat number: ${seatNum}`, wtbm_total_price, wtbm_total_seat_count );
    });

    function wtbm_time_slot_click_make_empty( click_btn ){

        if(  click_btn === 'wtbm_date' ){
            let selectMovie = `<div id="wtbm_movieName" ></div>
                                <div id="wtbm_movieDuration">Select a movie</div>`;
            $("#wtbm_selectedMovieDisplay").html( selectMovie );
        }

        if( click_btn === 'wtbm_movie' || click_btn === 'wtbm_date' ){
            $("#wtbm_summaryTheaterName").text( '—' );
            $("#wtbm_summaryTimeSlot").text('—');
            $("#wtbm_summeryTheaterId").val( '' );
            $("#wtbm_summeryTime").val( '' );
        }

        $("#wtbm_summaryQuantity").text( 0 );
        $("#wtbm_summaryTotal").text( 0+''+wtbm_ajax.wc_currency_symbol );
        $("#wtbm_summarySeats").text('—' );

        $("#wtbm_summeryTotalAmount").val( 0 );
        $("#wtbm_summerySeatNumber").val( "" );
        $("#wtbm_summerySeatIds").val( "" );
        wtbm_seatBooked = [];
        wtbm_seatBookedName = [];
        wtbm_total_price = 0;
        wtbm_total_seat_count = 0;

    }

    $(document).on( 'click', '#wtbm_ticketPurchaseBtn', function (e) {

        let movieId = $("#wtbm_summeryMovieId").val().trim();
        let theaterId = $("#wtbm_summeryTheaterId").val().trim();
        let bookingDate = $("#wtbm_summeryDate").val().trim();
        let bookingTime = $("#wtbm_summeryTime").val().trim();
        let totalAmount = $("#wtbm_summeryTotalAmount").val().trim();
        let userName = $("#wtbm_getUserName").val().trim();
        let userPhoneNum = $("#wtbm_getUserPhone").val().trim();

        // let wtbm_seatBookedNameStr = $("#wtbm_summerySeatNumber").val().trim();
        let wtbm_seatBookedNameStr = JSON.stringify( wtbm_seatBookedName );
        let wtbm_seatBookedIds = JSON.stringify( wtbm_seatBooked );
        let wtbm_summerySeatIds = $("#wtbm_summerySeatIds").val().trim();
        // console.log( wtbm_summerySeatNumber, wtbm_summerySeatIds );

        let button = $(this);
        let action = 'wtbm_theater_ticket_booking';
        const booking_data = {
            action: action,
            movie_id: movieId,
            theater_id: theaterId,
            booking_date: bookingDate,
            booking_time: bookingTime,
            total_amount: totalAmount,
            seat_count: wtbm_total_seat_count,
            seat_names: wtbm_seatBookedNameStr,
            booked_seat_ids: wtbm_seatBookedIds,
            userName: userName,
            userPhoneNum: userPhoneNum,
            nonce: wtbm_ajax.nonce,
        };
        console.log( booking_data );
        $.ajax({
            type: 'POST',
            url: wtbm_ajax.ajax_url,
            data: booking_data,
            beforeSend: function () {
                button.text('Adding...');
            },
            success: function (response) {
                if (response.success) {
                    button.text('Added to Cart ✅');
                    setTimeout(  function () {
                        button.text('Process Checkout')
                    },1000);

                    wtbm_seatBooked = [];
                    wtbm_seatBookedName = [];
                    wtbm_total_price = 0;
                    wtbm_total_seat_count = 0;

                    window.location.href = wtbm_ajax.site_url+'/checkout/';
                } else {
                    alert(response.data);
                    button.text('Add to Cart');
                }
            }
        });

    });


    $(document).on( 'click', '#wtbm_adminTicketPurchaseBtn', function (e) {

        let movieId = $("#wtbm_summeryMovieId").val().trim();
        let theaterId = $("#wtbm_summeryTheaterId").val().trim();
        let bookingDate = $("#wtbm_summeryDate").val().trim();
        let bookingTime = $("#wtbm_summeryTime").val().trim();
        let totalAmount = $("#wtbm_summeryTotalAmount").val().trim();
        let userName = $("#wtbm_getUserName").val().trim();
        let userPhoneNum = $("#wtbm_getUserPhone").val().trim();

        let wtbm_seatBookedNameStr = JSON.stringify( wtbm_seatBookedName );
        let wtbm_seatBookedIds = JSON.stringify( wtbm_seatBooked );

        let button = $(this);
        let action = 'wtbm_theater_ticket_booking_admin';
        const booking_data = {
            action: action,
            movie_id: movieId,
            theater_id: theaterId,
            booking_date: bookingDate,
            booking_time: bookingTime,
            total_amount: totalAmount,
            seat_count: wtbm_total_seat_count,
            seat_names: wtbm_seatBookedNameStr,
            booked_seat_ids: wtbm_seatBookedIds,
            userName: userName,
            userPhoneNum: userPhoneNum,
            nonce: wtbm_ajax.nonce,
        };

        $("#wtbm_seatsGrid").empty();
        $.ajax({
            type: 'POST',
            url: wtbm_ajax.ajax_url,
            data: booking_data,
            beforeSend: function () {
                button.text('Adding...');
            },
            success: function (response) {
                if (response.success) {
                    button.text('Added to Cart ✅');
                    setTimeout(  function () {
                        button.text('Order Complete')
                    },1000);

                    wtbm_seatBooked = [];
                    wtbm_seatBookedName = [];
                    wtbm_total_price = 0;
                    wtbm_total_seat_count = 0;

                    if( response.data.seat_map  ) {
                        $("#wtbm_seatsGrid").html(response.data.seat_map);
                    }else{
                        $("#wtbm_seatsGrid").html( '<h6>No Movies Found</h6>');
                    }

                    $("#wtbm_summaryTotal").text( response.data.wtbm_total_price );

                } else {
                    alert(response.data);
                    button.text('Add to Cart');
                }
            }
        });

    });


    let wtbmWelcomepopup = $('#wtbm_pa-welcome-popup');

    if ( wtbmWelcomepopup.length ) {
        wtbmWelcomepopup.fadeIn(300); // Show popup with animation

        wtbmWelcomepopup.find('.wtbm_pa_close').on('click', function() {
            wtbmWelcomepopup.fadeOut(200);
        });

        wtbmWelcomepopup.on('click', function(e) {
            if ($(e.target).is(wtbmWelcomepopup)) {
                wtbmWelcomepopup.fadeOut(200);
            }
        });
    }





}(jQuery));
