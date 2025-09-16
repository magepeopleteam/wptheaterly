(function ($) {

    let frame;

    $(document).on( 'click' ,'#wtbm_upload_movie_poster', function (e) {
        e.preventDefault();
        if (frame) {
            frame.open();
            return;
        }
        frame = wp.media({
            title: 'Select or Upload Poster',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });
        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#wtbm_movie_poster_id').val(attachment.id);
            $('#wtbm_movie_poste_preview').html(
                '<img src="' + attachment.url + '" style="max-width:150px;height:auto;" />'
            );
            $('#wtbm_remove_movie_poster').show();
        });
        frame.open();
    });

    // Remove poster
    $(document).on( 'click', '#wtbm_remove_movie_poster', function () {
        $('#wtbm_movie_poster_id').val('');
        $('#wtbm_movie_poste_preview').html('');
        $(this).hide();
    });


    $(document).on('click', '.nav-item', function (e) {

        $('.nav-item').removeClass('active');
        $('.tab-content').hide();
        let nav_name = $(this).attr('data-tab').trim();
        let nav_container = nav_name+'_content';
        $("#"+nav_container).fadeIn();
        $(this).addClass('active');
        let alreadyLoadedBookingIds = [];

        if( nav_name === 'wtbm_bookings' ){
            let bookingHolder =  $("#wtbm_bookings_table_body");
            bookingHolder.empty();
            bookingHolder.html( '<div class="wtbm_booking_loader"><span class="">Booking Data Loading...</span></div>' );


            const load_more_rule = {
                action: "wtbm_get_load_more_booking_data",
                already_loaded_booking_ids: JSON.stringify( alreadyLoadedBookingIds ),
                display_limit: 10,
                _ajax_nonce: mptrs_admin_ajax.nonce,
            };
            $.ajax({
                url: mptrs_admin_ajax.ajax_url,
                type: "POST",
                data: load_more_rule,
                success: function (response) {
                    if (response.success) {

                        $("#wtbm_showing_number_of_booking").fadeIn();
                        let total_show = response.data.booking_count;
                        bookingHolder.html(response.data.booking_data);
                        $("#wtbm_showing_count").text( total_show );
                        if( response.data.booking_count > 9 ){
                            $("#wtbm_booking_load_more_btn").fadeIn();
                        }
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function (xhr, status, error) {
                    alert("Something went wrong!");
                }
            });
        }

    });
    $(document).on('click', '#mptrs_add_new_movie', function (e) {
        let clickedId = $(this);
        addMovie( 'add', 'mptrs_insert_movie_post', clickedId, '' );
    });

    $(document).on('click', '#mptrs_edit_movie', function (e) {
        let clickedId = $(this);
        let post_id = $(this).attr('data-edited-post-id');
        addMovie( 'edit', 'wtbm_update_movie_post', clickedId, post_id );
    });

    $(document).on('click', '#wtbp_add_new_theater', function (e) {
        addTheater( '' );
    });

    $(document).on('click', '#wtbm_update_theater', function (e) {
        let post_id = $(this).attr('date-theater-id');
        addTheater( post_id );
    });

    $(document).on('click', '#wtbm_add_new_show_time', function (e) {
        e.preventDefault();
        let clickBtn = $(this);
        clickBtn.text('Show Time Adding...');
        let showTimeId = '';
        let action = 'add';
        addShowtime( action ,showTimeId, clickBtn );
    });

    $(document).on('click', '#wtbm_edit_show_time', function (e) {
        e.preventDefault();
        let clickBtn = $(this);
        clickBtn.text('Show Time Updating...');
        let showTimeId = $(this).attr( 'data-showTimeId' );
        let action = 'edit';
        addShowtime( action ,showTimeId, clickBtn );
    });

    $(document).on('click', '.wtbpShowHideAddForm', function (e) {
        let clickedId = $(this).attr( 'id' ).trim();
        showAddMovieForm( clickedId );
    });

    $(document).on('change', '#pricing-type', function (e) {
        updatePricingFields();
    });

    $(document).on('click', '#wtbp_previewPricing', function (e) {
        previewPricing();
    });

    $(document).on('click', '#wtbp_add_new_pricing_rule', function (e) {
        let clickedBtn = $(this);
        addPricingRule( 'add', clickedBtn, '' );
    });

    $(document).on('click', '#wtbm_edit_pricing_rule', function (e) {
        let pricingId = $(this).attr('data-edit-pricing');
        let clickedBtn = $(this);
        addPricingRule('edit', clickedBtn, pricingId );
    });

    $(document).on('click', '.wtbm_edit_theater', function (e) {
        let addEditTheaterForm = $('#wtbmAddTheaterForm');
        addEditTheaterForm.fadeIn();
        addEditTheaterForm.empty();
        let theaterEditLoader = wtbm_add_edit_loader( 'Theater' )
        addEditTheaterForm.html( theaterEditLoader );

        let theaterId = $(this).attr('data-theater-id');
        const theater_rule = {
            action: "wtbp_add_edit_theater_form",
            post_id: theaterId,
            _ajax_nonce: mptrs_admin_ajax.nonce,
        };
        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: "POST",
            data: theater_rule,
            success: function( response ) {
                if ( response.success ) {
                    addEditTheaterForm.html( response.data );
                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert("Something went wrong!");
            }
        });

    });

    $(document).on('click', '.wtbm_delete_movie', function (e) {
        let deleteId = $(this).attr('data-delete-movie-id');
        let movie = 'movie_content_'+deleteId;

        wtbm_delete_custom_post( deleteId, movie );
    });
    $(document).on('click', '.wtbm_delete_theater', function (e) {
        let theaterId = $(this).attr('data-delete-theater-id');
        let theater = 'theater_content_'+theaterId;
        wtbm_delete_custom_post( theaterId, theater );

    });
    $(document).on('click', '.wtbm_delete_pricing_rules', function (e) {
        let pricing_rulesId = $(this).attr('data-pricing-rules-id');
        let pricing_rules = 'pricing_rules_content_'+pricing_rulesId;
        wtbm_delete_custom_post( pricing_rulesId, pricing_rules );
    });
    $(document).on('click', '.wtbm_delete_show_time', function (e) {
        let show_timeId = $(this).attr('data-delete-showtime-id');
        let show_time = 'show_time_content_'+show_timeId;
        wtbm_delete_custom_post( show_timeId, show_time );
    });

    function wtbm_delete_custom_post( post_id, content ) {
        const delete_rule = {
            action: "wtbt_delete_custom_post",
            post_id: post_id,
            _ajax_nonce: mptrs_admin_ajax.nonce,
        };
        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: "POST",
            data: delete_rule,
            success: function (response) {

                if (response.success) {
                    $('#'+content).fadeOut();
                    alert( response.data.message );
                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function (xhr, status, error) {
                alert("Something went wrong!");
            }
        });
    }

    // });

    $(document).on('click', '.wtbm_edit_pricing_rules', function (e) {

        let addPricingForm = $('#wtbm_AddPricingForm');
        addPricingForm.fadeIn();
        addPricingForm.empty();
        let loader = wtbm_add_edit_loader( 'Pricing' );
        addPricingForm.html( loader );

        const show_pricing_rule = {
            action: "wtbm_add_edit_pricing_form",
            post_id: $(this).attr('data-pricing-id'),
            _ajax_nonce: mptrs_admin_ajax.nonce,
        };
        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: "POST",
            data: show_pricing_rule,
            success: function( response ) {
                if ( response.success ) {
                    $('#wtbm_AddPricingForm').html( response.data );
                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert("Something went wrong!");
            }
        });
    });

    $(document).on('click', '.editwtbm_edit_show_time', function (e) {
        let showTimeId = $(this).attr('data-editShowtime');
        let addShowtimeForm= $('#wtbm_add-showtime-form');
        addShowtimeForm.fadeIn();
        addShowtimeForm.empty();
        let loader = wtbm_add_edit_loader( 'Showtime' )
        addShowtimeForm.html( loader );

        const show_time_rule = {
            action: "wtbm_add_edit_show_time_form",
            post_id: showTimeId,
            _ajax_nonce: mptrs_admin_ajax.nonce,
        };
        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: "POST",
            data: show_time_rule,
            success: function( response ) {
                if ( response.success ) {
                    addShowtimeForm.html( response.data );
                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert("Something went wrong!");
            }
        });

    });

    function wtbm_add_edit_loader( titleText = '' ){
        return `<div class="wtbm_add_edit_loader_holder">
                    <h2>${titleText} Editor Loading ...</h2>
                </div>`;
    }

    $(document).on('click', '.wtbm_edit_movie', function (e) {
        let wtbm_novieAddForm = $('#add-movie-form');
        let addEditMovieForm = $('#wtbm_add_edit_movie_form_holder');
        addEditMovieForm.empty();

        let loader = wtbm_add_edit_loader( 'Movie' )
        addEditMovieForm.html( loader );


        wtbm_novieAddForm.fadeIn();

        let postId = $(this).attr('data-edit-movie-id');
        const sent_data = {
            action: "wtbp_add_edit_movie_form",
            post_id: postId,
            _ajax_nonce: mptrs_admin_ajax.nonce,
        };
        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: "POST",
            data: sent_data,
            success: function( response ) {
                if ( response.success ) {
                    addEditMovieForm.html( response.data );
                    // $('#add-movie-form').fadeIn();
                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert("Something went wrong!");
            }
        });

    });

    function addMovie( action_type, action, clickedId, post_id ) {

        let afterClickBtnText = '';
        let beforeClickBtnText = '';
        if( action_type === 'add' ){
            afterClickBtnText = 'Adding New Movie...';
            beforeClickBtnText = 'Add Movie';
        }else{
            afterClickBtnText = 'Movie Updating...';
            beforeClickBtnText = 'Update Movie';
        }
        clickedId.text( afterClickBtnText );

        let response_type = 'Added';
        const isChecked = $("#wtbm_movie_active").is(":checked");
        let movieData = {
            action: action,
            title: $("#movie-title").val(),
            active: isChecked,
            genre: $("#movie-genre").val(),
            duration: $("#movie-duration").val(),
            rating: $("#movie-rating").val(),
            release_date: $("#movie-release-date").val(),
            poster: $("#movie-poster").val(),
            poster_id: $("#wtbm_movie_poster_id").val(),
            description: $("#movie-description").val(),
            status: "publish",
            _ajax_nonce: mptrs_admin_ajax.nonce
        };

        console.log( movieData );

        let edited_movie = '';
        if( action_type === 'edit' ){
            movieData.post_id = post_id;
            response_type = 'Edited';
            edited_movie = 'movie_content_'+post_id;
        }

        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: "POST",
            data: movieData,
            success: function(response) {
                if (response.success) {
                    let movie_id = response.data.ID;
                    $("#"+edited_movie).hide();
                    clickedId.text( beforeClickBtnText );
                    alert("Movie : "+response_type+' '+ response.data.movie_title);

                    $("#movies-table-body").prepend( response.data.updated_movie );
                    // renderMoviesTable( response.data, movie_id );

                    clearForm( "#add-movie-form" );
                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
                alert("Something went wrong!");
            }
        });

    }

    function addTheater( post_id ) {
        let seatPlanTexts = [];
        let selectedSeats = [];
        let dynamicShapes = [];

        if( post_id ) {
            $('.mptrs_mappingSeat.save').each(function () {
                if ($(this).css('background-color') !== 'rgb(255, 255, 255)') { // Not default white
                    const id = $(this).data('id');
                    const row = $(this).data('row');
                    const col = $(this).data('col');
                    const backgroundImage = $(this).data('background-image');
                    const seat_number = $(this).attr('data-seat-num');
                    const seat_category = $(this).attr('data-seat-category');
                    const data_degree = $(this).data('degree');
                    const data_tableBind = $(this).attr('data-tablebind');
                    const color = $(this).css('background-color');
                    const price = $(this).attr('data-price') || 0;
                    const width = $(this).css('width') || 0;
                    const height = $(this).css('height') || 0;
                    const z_index = $(this).css('z-index') || 0;
                    const left = $(this).css('left') || 0;
                    const top = $(this).css('top') || 0;
                    const border_radius = $(this).css('border-radius') || 0;
                    const seatText = $(this).find('.seatText').text();

                    selectedSeats.push({id, row, col, color, price, width, height, seat_number, left, top, z_index, data_degree, data_tableBind, border_radius, seatText, backgroundImage, seat_category
                    });
                }
            });

            // console.log( selectedSeats );
            $('.mptrs_text-wrapper').each(function () {
                const textLeft = parseInt($(this).css('left')) || 0;
                const textTop = parseInt($(this).css('top')) || 0;
                const class_name = $(this).data('class');
                const color = $(this).children('.mptrs_dynamic-text').css('color') || '';
                const fontSize = $(this).children('.mptrs_dynamic-text').css('font-size') || '';
                const text = $(this).children('.mptrs_dynamic-text').text() || '';
                const textRotateDeg = $(this).data('text-degree') || 0;

                seatPlanTexts.push({text, class_name, textLeft, textTop, color, fontSize, textRotateDeg});
            });
            $('.mptrs_dynamicShape').each(function () {
                const textLeft = parseInt($(this).css('left')) || 0;
                const textTop = parseInt($(this).css('top')) || 0;
                const width = parseInt($(this).css('width')) || 0;
                const height = parseInt($(this).css('height')) || 0;
                const backgroundColor = $(this).css('background-color') || '';
                const borderRadius = $(this).css('border-radius') || '';
                const clipPath = $(this).css('clip-path') || '';
                const shapeRotateDeg = $(this).data('shape-rotate') || 0;
                const tableBindID = $(this).attr('id').trim() || '';
                const backgroundImage = $(this).data('background-image');

                dynamicShapes.push({textLeft, textTop, width, height, backgroundColor, borderRadius, clipPath, shapeRotateDeg, tableBindID, backgroundImage
                });
            });
        }

        let wtbm_theater_categories = [];
        $("#wtbm_theater_categories_wrapper .wtbm_theater_category_box").each(function(){
            let categoryName = $(this).find("input[name='wtbm_theater_category_name']").val();
            let seats = $(this).find("input[name='wtbm_theater_seats']").val();
            let price = $(this).find("input[name='wtbm_theater_price']").val();
            let color = $(this).find("input[name='wtbm_theater_color']").val();

            wtbm_theater_categories.push({
                category_id: wtbm_generateUniqueId( categoryName ),
                category_name: categoryName,
                seats: seats,
                price: price,
                color: color,
            });
        });

        console.log( wtbm_theater_categories );

        wtbm_theater_categories = JSON.stringify( wtbm_theater_categories);


        // console.log( wtbm_categories );

        let selectedSeatsStr = JSON.stringify(selectedSeats);
        let seatPlanTextsStr = JSON.stringify(seatPlanTexts);
        let dynamicShapesStr = JSON.stringify(dynamicShapes);

        const rows = parseInt(document.getElementById('theater-rows').value);
        const seatsPerRow = parseInt(document.getElementById('theater-seats-per-row').value);

        let action = '';
        if( post_id ){
            action = 'mptrs_update_theater_post';
        }else{
            action = 'mptrs_insert_theater_post';
        }

        let theater = {
            action: action,
            post_id: post_id,
            seat_maps_meta_data: selectedSeatsStr,
            seatPlanTexts: seatPlanTextsStr,
            dynamicShapes: dynamicShapesStr,
            id: Date.now(),
            name: $('#theater-name').val(),
            description: $('#theater-description').val(),
            type: $('#theater-type').val(),
            rows: rows,
            seatsPerRow: seatsPerRow,
            soundSystem: $('#theater-sound').val(),
            status: $('#theater-status').val(),
            wtbm_categories: wtbm_theater_categories,
            _ajax_nonce: mptrs_admin_ajax.nonce
        };

       $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: "POST",
            data: theater,
            success: function(response) {
                if (response.success) {
                    if( post_id ){
                        let edited_theater = 'theater_content_'+post_id;
                        $("#"+edited_theater).fadeOut();
                        $("#theaters-table-body").prepend( response.data.new_theater );
                        alert( "Theater Edited" );
                        clearForm( "#wtbmAddTheaterForm" );
                    }else{

                        $("#wtbm_add_edit_theater_container").fadeOut();
                        $("#theaters-table-body").prepend( response.data.new_theater );
                        $("#wtbm_SeatMappingSection").html( response.data.seat_map );
                        alert( "Theater Added" );
                    }

                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
                alert("Something went wrong!");
            }
        });
    }

    function wtbm_simpleHash(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = ((hash << 5) - hash) + str.charCodeAt(i);
            hash |= 0;
        }
        return Math.abs(hash).toString(36);
    }
    function wtbm_generateUniqueId( categoryName, length = 8) {
        let input = categoryName + Date.now() + Math.random();
        let hash = wtbm_simpleHash(input);
        return hash.slice(0, length);
    }

    function addShowtime( action_type, showTimeId, clickBtn ) {

        let action = '';
        if( showTimeId ){
            action = 'wtbm_update_show_time_post';
        }else{
            action = 'wtbm_insert_show_time_post';
        }

        const showtime = {
            action: action,
            id: Date.now(),
            showTimeId: showTimeId,
            action_type: action_type,
            title: $('#showTimeName').val(),
            movieId: parseInt($('#showtime-movie').val(), 10),
            theaterId: parseInt($('#wtbm_showtime_theater').val(), 10),
            date: $('#showtime-date').val(),
            start_date: $('#showtime_date_start').val(),
            end_date: $('#showtime_date_end').val(),
            startTime: $('#showtime-time-start').val(),
            endTime: $('#showtime-time-end').val(),
            price: parseFloat($('#showtime-price').val()),
            description: $('#showTime-description').val(),
            showtime_off_days: $('#wtbm_showtime_off_days').val(),
            _ajax_nonce: mptrs_admin_ajax.nonce,
        };
        $.ajax({
            url: mptrs_admin_ajax.ajax_url, // admin-ajax.php
            type: "POST",
            data: showtime,
            success: function(response) {
                if (response.success) {
                    if( action_type === 'add' ){
                        alert(" Show Time Added ");
                        $("#showtimes-table-body").prepend( response.data );
                        clickBtn.text('Add Showtime');
                    }else{
                        let edited_show_time = 'show_time_content_'+showTimeId;
                        $( "#"+edited_show_time ).fadeOut();

                        $("#showtimes-table-body").prepend( response.data );
                        clickBtn.text('Update Showtime');
                        alert(" Show Time Updated ");
                    }
                    clearForm( "#wtbm_add-showtime-form" );


                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
                alert("Something went wrong!");
            }
        });

    }

    $(document).on('change', ".wtbm_groupCheckBox input[type=checkbox]", function (e) {
        let hiddenInput = $("#wtbm_showtime_off_days");
        let selected = hiddenInput.val() ? hiddenInput.val().split(",") : [];
        let day = $(this).data("checked");
        if ($(this).is(":checked")) {
            if ($.inArray(day, selected) === -1) {
                selected.push(day);
            }
        } else {
            selected = selected.filter(function(value) {
                return value !== day;
            });
        }
        hiddenInput.val(selected.join(","));
    });

    function addPricingRule( action_type, clickedBtn, post_id ) {
        const name = $('#pricing-name').val();
        const type = $('#pricing-type').val();
        const multiplier = parseFloat($('#pricing-multiplier').val());

        let afterClickBtnText = '';
        let beforeClickBtnText = '';
        let btnText = '';
        if( action_type === 'add' ){
            afterClickBtnText = 'Pricing Rules Adding...';
            beforeClickBtnText = 'Add Rule';
        }else{
            afterClickBtnText = 'Pricing Rules Updating...';
            beforeClickBtnText = 'Update Rule';
        }

        clickedBtn.text( afterClickBtnText );

        if ( !name || !multiplier ) {
            alert('Please fill in required fields: Name and Price Multiplier');
            return;
        }

        const rule = {
            action: "wtbm_insert_pricing_rules_post",
            id: Date.now(),
            name: name,
            type: type,
            multiplier: multiplier,
            post_id: post_id,
            action_type: action_type,
            active: $('#pricing-status').val() === 'true',
            priority: parseInt($('#pricing-priority').val()) || 10,
            description: $('#pricing-description').val(),
            minSeats: parseInt($('#pricing-min-seats').val()) || 1,
            combinable: $('#pricing-combinable').is(':checked'),
            _ajax_nonce: mptrs_admin_ajax.nonce,
        };

        rule.timeRange = '';
        rule.days = '';
        rule.startDate = '';
        rule.endDate = '';
        rule.dateRange = '';
        rule.theaterType = '';

        // console.log( rule );

        // Add type-specific properties
        switch( type ) {
            case 'time':
                rule.timeRange = $('#pricing-time-range').val();
                break;
            case 'day':
                const selectedDays = $('input[name="pricing-days[]"]:checked').map(function() {
                    return $(this).val();
                }).get();
                rule.days = selectedDays;

                console.log( rule.days  );
                break;
            case 'date':
                rule.startDate = $('#pricing-start-date').val();
                rule.endDate = $('#pricing-end-date').val();
                rule.dateRange = `${rule.startDate || 'start'} to ${rule.endDate || 'end'}`;
                break;
            case 'theater':
                rule.theaterType = $('#pricing-theater-type').val();
                break;
        }

        $.ajax({
            url: mptrs_admin_ajax.ajax_url, // admin-ajax.php
            type: "POST",
            data: rule,
            success: function( response ) {
                if ( response.success ) {
                    if( action_type === 'add' ){
                        $("#pricing-table-body").prepend( response.data );
                        alert( "Pricing Rules Added" );
                        // clearPricingForm();
                    }else{
                        let edited_pricing = 'pricing_rules_content_'+post_id;
                        $("#"+edited_pricing).fadeOut();
                        alert("Pricing Rules Updated: " );
                        $("#pricing-table-body").prepend( response.data );
                    }

                    clickedBtn.text( beforeClickBtnText );
                    clearForm( "#wtbm_AddPricingForm" );


                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert("Something went wrong!");
            }
        });
    }
    function showAddMovieForm( clickedId ) {
        if( clickedId === 'wtbpAddedMovieForm' ){

            let wtbm_novieAddForm = $('#add-movie-form');
            wtbm_novieAddForm.fadeIn();
            let addEditMovieForm = $('#wtbm_add_edit_movie_form_holder');

            addEditMovieForm.empty();
            let movieFormLoader = wtbm_add_edit_loader( 'Movie' )
            addEditMovieForm.html( movieFormLoader );


            const rule = {
                action: "wtbp_add_edit_movie_form",
                _ajax_nonce: mptrs_admin_ajax.nonce,
            };
            $.ajax({
                url: mptrs_admin_ajax.ajax_url,
                type: "POST",
                data: rule,
                success: function( response ) {
                    if ( response.success ) {
                        addEditMovieForm.html( response.data );
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert("Something went wrong!");
                }
            });
        }else if( clickedId === 'wtbpTheaterAddForm' ){
            let wtbm_theaterAddForm = $('#wtbmAddTheaterForm');
            wtbm_theaterAddForm.fadeIn();

            wtbm_theaterAddForm.empty();
            let movieFormLoader = wtbm_add_edit_loader( 'Theater' );
            wtbm_theaterAddForm.html( movieFormLoader );


            const theater_rule = {
                action: "wtbp_add_edit_theater_form",
                post_id: "",
                _ajax_nonce: mptrs_admin_ajax.nonce,
            };
            $.ajax({
                url: mptrs_admin_ajax.ajax_url,
                type: "POST",
                data: theater_rule,
                success: function( response ) {
                    if ( response.success ) {
                        wtbm_theaterAddForm.html( response.data );
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert("Something went wrong!");
                }
            });

        }else if( clickedId === 'wtbpShowtimeAddForm' ){

            let wtbm_showTimeAddForm = $('#wtbm_add-showtime-form');
            wtbm_showTimeAddForm.fadeIn();

            wtbm_showTimeAddForm.empty();
            let wtbm_showTimeLoader = wtbm_add_edit_loader( 'Showtime' );
            wtbm_showTimeAddForm.html( wtbm_showTimeLoader );

            const show_time_rule = {
                action: "wtbm_add_edit_show_time_form",
                post_id: "",
                _ajax_nonce: mptrs_admin_ajax.nonce,
            };
            $.ajax({
                url: mptrs_admin_ajax.ajax_url,
                type: "POST",
                data: show_time_rule,
                success: function( response ) {
                    if ( response.success ) {
                        wtbm_showTimeAddForm.html( response.data );
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert("Something went wrong!");
                }
            });


        }else if( clickedId === 'wtbpPricingAddForm' ){
            let wtbm_pricingAddForm = $('#wtbm_AddPricingForm');
            wtbm_pricingAddForm.fadeIn();

            wtbm_pricingAddForm.empty();
            let wtbm_pricingLoader = wtbm_add_edit_loader( 'Pricing' );
            wtbm_pricingAddForm.html( wtbm_pricingLoader );


            const show_pricing_rule = {
                action: "wtbm_add_edit_pricing_form",
                post_id: "",
                _ajax_nonce: mptrs_admin_ajax.nonce,
            };
            $.ajax({
                url: mptrs_admin_ajax.ajax_url,
                type: "POST",
                data: show_pricing_rule,
                success: function( response ) {
                    if ( response.success ) {
                        wtbm_pricingAddForm.html( response.data );
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert("Something went wrong!");
                }
            });
            $('#add-pricing-form').fadeIn();
        }else{
            $('#add-movie-form').fadeIn();
        }

    }
    function renderMoviesTable( movie, movie_id ) {

        // console.log( movie );

            let movie_html  =  `
                        <tr class="wtbm_movie_content" id="movie_content_${movie_id}" data-movie-id="${movie_id}">
                            <td>
                                <div class="flex items-center">
                                    <img src="${movie.poster_image_url}" alt="${movie.title}" class="movie-poster" loading="lazy">
                                    <div>
                                        <div class="font-medium text-gray-900">${movie.title}</div>
                                        ${movie.release_date ? `<div class="text-sm text-gray-500">Released: ${movie.release_date}</div>` : ''}
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm text-gray-900">${movie.genre}</td>
                            <td class="text-sm text-gray-900">${movie.duration}</td>
                            <td class="text-sm font-medium">⭐ ${movie.rating}</td>
                            <td>
                                <span class="status-badge status-${movie.status.toLowerCase()}">
                                    ${movie.status}
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn-icon edit wtbm_edit_movie" data-edit-movie-id="${movie_id}" id="wrbm_edit_${movie_id}"
                                        title="Edit Movie">✏️</button>
                                    <button class="btn-icon delete wtbm_delete_movie" id="wrbm_delete_${movie_id}"
                                        title="Delete Movie" data-delete-movie-id="${movie_id}">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    `;
                    $("#movies-table-body").prepend( movie_html );

    }
    function hideAddMovieForm() {
        document.getElementById('add-movie-form').classList.add('hidden');
        clearMovieForm();
    }

    function clearForm( formSelector ) {
        $(formSelector).find("input[type=text], input[type=number], input[type=date], input[type=url], textarea").val("");
        $(formSelector).find("select").prop("selectedIndex", 0);
        $(formSelector).find("[data-edited-post-id]").attr("data-edited-post-id", "");
        $(formSelector).fadeOut();
    }

    $(document).on("click", "#wtbm_clear_add_movie_form", function(e) {
        e.preventDefault();
        clearForm("#add-movie-form");

    });

    $(document).on("click", "#wtbm_clear_theater_from", function(e) {
        e.preventDefault();
        clearForm("#wtbmAddTheaterForm");
    });

    $(document).on("click", "#wtbm_clear_show_time_form", function(e) {
        e.preventDefault();
        clearForm("#wtbm_add-showtime-form");
    });

    $(document).on("click", "#wtbm_clear_pricing_form", function(e) {
        e.preventDefault();
        clearForm("#wtbm_AddPricingForm");

    });

    $(document).on('click', '#wtbm_saveSeatPlan', function (e) {
        e.preventDefault();
        let theater_id = $(this).attr('data-theater-id').trim();
        let seatPlanTexts = [];
        let selectedSeats = [];
        let dynamicShapes = [];
        $('.mptrs_mappingSeat.save').each(function () {
            if ( $(this).css('background-color') !== 'rgb(255, 255, 255)') { // Not default white
                const id = $(this).data('id');
                const row = $(this).data('row');
                const col = $(this).data('col');
                const backgroundImage = $(this).data('background-image');
                const seat_number = $(this).attr('data-seat-num');
                const seat_category = $(this).attr('data-seat-category');
                const data_degree = $(this).data('degree');
                const data_tableBind = $(this).attr('data-tablebind');
                const color = $(this).css('background-color');
                const price = $(this).attr('data-price') || 0;
                const width =$(this).css('width') || 0;
                const height = $(this).css('height') || 0;
                const z_index = $(this).css('z-index') || 0;
                const left = $(this).css('left') || 0;
                const top = $(this).css('top') || 0;
                const border_radius = $(this).css('border-radius') || 0;
                const seatText = $(this).find('.seatText').text();

                selectedSeats.push({ id, row, col, color, price, width, height, seat_number, left, top, z_index, data_degree, data_tableBind, border_radius, seatText, backgroundImage, seat_category });
            }
        });
        $('.mptrs_text-wrapper').each(function () {
            const textLeft = parseInt($(this).css('left')) || 0;
            const textTop = parseInt($(this).css('top')) || 0;
            const class_name = $(this).data('class');
            const color = $(this).children('.mptrs_dynamic-text' ).css('color') || '';
            const fontSize = $(this).children('.mptrs_dynamic-text').css('font-size') || '';
            const text = $(this).children('.mptrs_dynamic-text').text() || '';
            const textRotateDeg = $(this).data('text-degree') || 0;

            seatPlanTexts.push({ text, class_name, textLeft, textTop, color, fontSize, textRotateDeg});
        });
        $('.mptrs_dynamicShape').each(function () {
            const textLeft = parseInt($(this).css('left')) || 0;
            const textTop = parseInt($(this).css('top')) || 0;
            const width = parseInt($(this).css('width')) || 0;
            const height = parseInt($(this).css('height')) || 0;
            const backgroundColor = $(this).css('background-color') || '';
            const borderRadius = $(this).css('border-radius') || '';
            const clipPath = $(this).css('clip-path') || '';
            const shapeRotateDeg = $(this).data('shape-rotate') || 0;
            const tableBindID = $(this).attr('id').trim() || '';
            const backgroundImage = $(this).data('background-image');

            dynamicShapes.push({ textLeft, textTop, width, height,  backgroundColor, borderRadius, clipPath, shapeRotateDeg,tableBindID, backgroundImage });
        });

        let selectedSeatsStr = JSON.stringify(selectedSeats);
        let seatPlanTextsStr = JSON.stringify(seatPlanTexts);
        let dynamicShapesStr = JSON.stringify(dynamicShapes);

        let theater_seat_map = {
            action: 'wtbm_theater_seat_map_add',
            post_id: theater_id,
            seat_maps_meta_data: selectedSeatsStr,
            seatPlanTexts: seatPlanTextsStr,
            dynamicShapes: dynamicShapesStr,
            _ajax_nonce: mptrs_admin_ajax.nonce
        };
        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: "POST",
            data: theater_seat_map,
            success: function( response ) {

                console.log( response.success );

                if ( response.success ) {
                    if( theater_id ){
                        alert( "Theater Map Added" );
                        clearForm( "#wtbmAddTheaterForm" );
                    }

                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
                alert("Something went wrong!");
            }
        });


    });

    function clearMovieForm() {
        document.getElementById('movie-title').value = '';
        document.getElementById('movie-genre').value = '';
        document.getElementById('movie-duration').value = '';
        document.getElementById('movie-rating').value = '';
        document.getElementById('movie-release-date').value = '';
        document.getElementById('movie-poster').value = '';
        document.getElementById('movie-description').value = '';
    }

    function updatePricingFields() {
            var type = $('#pricing-type').val();
            $('#time-range-group, #days-group, #date-range-group, #theater-group').hide();
            switch(type) {
                case 'time':
                    $('#time-range-group').show();
                    break;
                case 'day':
                    $('#days-group').show();
                    break;
                case 'date':
                    $('#date-range-group').show();
                    break;
                case 'theater':
                    $('#theater-group').show();
                    break;
            }
        }

    function previewPricing() {
        const type = document.getElementById('pricing-type').value;
        const multiplier = parseFloat(document.getElementById('pricing-multiplier').value) || 1.0;
        const basePrice = 15.99; // Example base price
        const finalPrice = (basePrice * multiplier).toFixed(2);

        let conditions = '';
        switch(type) {
            case 'time':
                const timeRange = document.getElementById('pricing-time-range').value;
                conditions = timeRange ? `during ${timeRange}` : 'at any time';
                break;
            case 'day':
                const selectedDays = Array.from(document.getElementById('pricing-days').selectedOptions).map(o => o.text);
                conditions = selectedDays.length ? `on ${selectedDays.join(', ')}` : 'on any day';
                break;
            case 'date':
                const startDate = document.getElementById('pricing-start-date').value;
                const endDate = document.getElementById('pricing-end-date').value;
                conditions = `from ${startDate || 'start'} to ${endDate || 'end'}`;
                break;
            case 'theater':
                const theaterType = document.getElementById('pricing-theater-type').value;
                conditions = theaterType ? `in ${theaterType} theaters` : 'in any theater';
                break;
        }

        const previewContent = `
                    <div><strong>Base Price:</strong> ${basePrice}</div>
                    <div><strong>Multiplier:</strong> ${multiplier}x</div>
                    <div><strong>Final Price:</strong> ${finalPrice}</div>
                    <div><strong>Applies:</strong> ${conditions}</div>
                    <div><strong>Effect:</strong> ${multiplier > 1 ? `+${((multiplier - 1) * 100).toFixed(0)}% markup` : multiplier < 1 ? `-${((1 - multiplier) * 100).toFixed(0)}% discount` : 'No change'}</div>
                `;

        document.getElementById('preview-content').innerHTML = previewContent;
        document.getElementById('pricing-preview').style.display = 'block';
    }


    let wtbm_theater_count = 1;

    function addCategory() {
        wtbm_theater_count++;
        let categoryHTML = `
          <div class="wtbm_theater_category_box" data-id="${wtbm_theater_count}">
            <button type="button" class="wtbm_theater_remove_btn">&times;</button>
            <h4>Seating Category ${wtbm_theater_count}</h4>
            <div class="wtbm_theater_form_group_holder">
                <div class="wtbm_theater_form_group">
                  <label>Category Name</label>
                  <input type="text" placeholder="e.g., Regular, Premium" name="wtbm_theater_category_name" required>
                </div>
                
                <div class="wtbm_theater_form_group">
                  <label>Number of Seats</label>
                  <input type="number" placeholder="50" name="wtbm_theater_seats" required>
                </div>
                
                <div class="wtbm_theater_form_group">
                  <label>Base Price ($)</label>
                  <input type="number" step="0.01" placeholder="12.99" name="wtbm_theater_price" required>
                </div>
                <div class="wtbm_theater_form_group">
                    <label>Set Color</label>
                    <input type="color" name="wtbm_theater_color" class="wtbm_theater_color" value="#2e8708" required>
                </div>
            </div>
          </div>`;

        $("#wtbm_theater_categories_wrapper").append(categoryHTML);
    }

    // Add button click
    $(document).on( "click", ".wtbm_theater_add_btn","click", function(){
        addCategory();
    });

    // Remove category
    $(document).on("click", ".wtbm_theater_remove_btn", function(){
        $(this).closest(".wtbm_theater_category_box").remove();
    });

    // Remove category
    $(document).on("change", "#wtbm_showtime_theater", function(){
        let selectedTheaterId = parseInt( $(this).val() );
        if( selectedTheaterId ) {
            const show_pricing_rule = {
                action: "wtbm_get_theater_categories",
                post_id: selectedTheaterId,
                _ajax_nonce: mptrs_admin_ajax.nonce,
            };
            $.ajax({
                url: mptrs_admin_ajax.ajax_url,
                type: "POST",
                data: show_pricing_rule,
                success: function (response) {
                    if (response.success) {
                        $('#wtbm_pricing_categories').html(response.data);
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function (xhr, status, error) {
                    alert("Something went wrong!");
                }
            });
        }else{
            $('#wtbm_pricing_categories').empty();
        }
    });

    // Load More Booking
    $(document).on("click", "#wtbm_booking_load_more_btn", function(){
        let alreadyLoadedBookingIds = [];
        let wtbm_showing_count =  parseInt($("#wtbm_showing_count").text(), 10);

        jQuery("#wtbm_bookings_table_body tr").each(function () {
            alreadyLoadedBookingIds.push(jQuery(this).data("order-id"));
        });

        if( alreadyLoadedBookingIds ) {
            const load_more_rule = {
                action: "wtbm_get_load_more_booking_data",
                already_loaded_booking_ids: JSON.stringify( alreadyLoadedBookingIds ),
                display_limit: 10,
                _ajax_nonce: mptrs_admin_ajax.nonce,
            };
            $.ajax({
                url: mptrs_admin_ajax.ajax_url,
                type: "POST",
                data: load_more_rule,
                success: function (response) {
                    if (response.success) {

                        let total_show = wtbm_showing_count + response.data.booking_count;
                        $('#wtbm_bookings_table_body').append(response.data.booking_data);
                        $("#wtbm_showing_count").text( total_show );
                        if( response.data.booking_count < 10 ){
                            $("#wtbm_booking_load_more_btn").fadeOut();
                        }
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function (xhr, status, error) {
                    alert("Something went wrong!");
                }
            });
        }else{
            $('#wtbm_pricing_categories').empty();
        }
    });


}(jQuery));