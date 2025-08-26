(function ($) {

    $(document).on('click', '.nav-item', function (e) {

        $('.nav-item').removeClass('active');
        $('.tab-content').hide();
        let nav_name = $(this).attr('data-tab').trim();
        let nav_container = nav_name+'_content';
        $("#"+nav_container).fadeIn();
        $(this).addClass('active');

    });
    $(document).on('click', '#mptrs_add_new_movie', function (e) {
        addMovie( 'add', 'mptrs_insert_movie_post', '' );
    });

    $(document).on('click', '#mptrs_edit_movie', function (e) {
        let post_id = $(this).attr('data-edited-post-id');
        addMovie( 'edit', 'wtbm_update_movie_post', post_id );
    });

    $(document).on('click', '#wtbp_add_new_theater', function (e) {
        addTheater( '' );
    });

    $(document).on('click', '#wtbm_update_theater', function (e) {
        let post_id = $(this).attr('wtbp_theater_id');
        addTheater( post_id );
    });

    $(document).on('click', '#wtbm_add_new_show_time', function (e) {
        e.preventDefault();
        let showTimeId = '';
        let action = 'add';
        addShowtime( action ,showTimeId );
    });

    $(document).on('click', '#wtbm_edit_show_time', function (e) {
        e.preventDefault();
        let showTimeId = $(this).attr( 'data-showTimeId' );
        let action = 'edit';
        addShowtime( action ,showTimeId );
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
        addPricingRule( '', 'add' );
    });

    $(document).on('click', '#wtbm_edit_pricing_rule', function (e) {
        let pricingId = $(this).attr('data-edit-pricing');
        addPricingRule( pricingId, 'edit' );
    });

    $(document).on('click', '.wtbm_edit_theater', function (e) {

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
                    $('#wtbmAddTheaterForm').html( response.data );
                    $('#wtbmAddTheaterForm').fadeIn();
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

    // $(document).on('click', '.wtbm_delete_movie', function (e) {

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
                    $('#wtbm_AddPricingForm').fadeIn();
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
                    $('#wtbm_add-showtime-form').html( response.data );
                    $('#wtbm_add-showtime-form').fadeIn();
                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert("Something went wrong!");
            }
        });

    });

    $(document).on('click', '.wtbm_edit_movie', function (e) {

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
                    $('#wtbm_add_edit_movie_form_holder').html( response.data );
                    $('#add-movie-form').fadeIn();
                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert("Something went wrong!");
            }
        });

    });

    function addMovie( action_type, action, post_id, edited_movie ) {

        let response_type = 'Added'
        let movieData = {
            action: action,
            title: $("#movie-title").val(),
            genre: $("#movie-genre").val(),
            duration: $("#movie-duration").val(),
            rating: $("#movie-rating").val(),
            release_date: $("#movie-release-date").val(),
            poster: $("#movie-poster").val(),
            description: $("#movie-description").val(),
            status: "publish",
            _ajax_nonce: mptrs_admin_ajax.nonce
        };

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
                    alert("Movie : "+response_type+' '+ response.data.post_title);
                    renderMoviesTable( movieData, movie_id );

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

        // addMovieData.push(movie);
        // movies.push(movie);


        // hideAddMovieForm();
    }
    function addTheater( post_id ) {
        const rows = parseInt(document.getElementById('theater-rows').value);
        const seatsPerRow = parseInt(document.getElementById('theater-seats-per-row').value);

        let action = 'mptrs_insert_theater_post';
        if( post_id ){
            action = 'mptrs_update_theater_post';
        }

        var theater = {
            action: action,
            post_id: post_id,
            id: Date.now(),
            name: $('#theater-name').val(),
            description: $('#theater-description').val(),
            type: $('#theater-type').val(),
            rows: rows,
            seatsPerRow: seatsPerRow,
            soundSystem: $('#theater-sound').val(),
            status: $('#theater-status').val(),
            _ajax_nonce: mptrs_admin_ajax.nonce
        };

        $.ajax({
            url: mptrs_admin_ajax.ajax_url, // admin-ajax.php
            type: "POST",
            data: theater,
            success: function(response) {
                if (response.success) {
                    if( post_id ){
                        let edited_theater = 'theater_content_'+post_id;
                        $("#"+edited_theater).fadeOut();
                        $("#theaters-table-body").prepend( response.data );
                        alert( "Theater Edited" );
                    }else{
                        $("#theaters-table-body").prepend(response.data);
                        alert( "Theater Added" );
                    }

                    clearForm( "#wtbmAddTheaterForm" );

                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
                alert("Something went wrong!");
            }
        });

        console.log( theater );

        /*theaters.push(theater);
        renderTheatersTable();
        hideAddTheaterForm();*/
    }

    function addShowtime( action_type, showTimeId ) {
        const showtime = {
            action: "wtbp_insert_show_time_post",
            id: Date.now(),
            showTimeId: showTimeId,
            action_type: action_type,
            title: $('#showTimeName').val(),
            movieId: parseInt($('#showtime-movie').val(), 10),
            theaterId: parseInt($('#showtime-theater').val(), 10),
            date: $('#showtime-date').val(),
            startTime: $('#showtime-time-start').val(),
            endTime: $('#showtime-time-end').val(),
            price: parseFloat($('#showtime-price').val()),
            description: $('#showTime-description').val(),
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
                    }else{
                        let edited_show_time = 'show_time_content_'+showTimeId;
                        $( "#"+edited_show_time ).fadeOut();

                        $("#showtimes-table-body").prepend( response.data );
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

    function addPricingRule( post_id, action_type ) {
        const name = $('#pricing-name').val();
        const type = $('#pricing-type').val();
        const multiplier = parseFloat($('#pricing-multiplier').val());

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
                    clearForm( "#wtbm_AddPricingForm" );


                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert("Something went wrong!");
            }
        });

        // pricingRules.push(rule);
        // renderPricingTable();
        // hideAddPricingForm();

        // Show success message
        alert(`Pricing rule "${name}" added successfully!`);
    }


    function showAddMovieForm( clickedId ) {
        if( clickedId === 'wtbpAddedMovieForm' ){
            // let aaa = $(this).closest('#wtbm_movies_content').find('#mptrs_add_new_movie').length;
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
                        $('#wtbm_add_edit_movie_form_holder').html( response.data );
                        $('#add-movie-form').fadeIn();
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert("Something went wrong!");
                }
            });
        }else if( clickedId === 'wtbpTheaterAddForm' ){

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
                        $('#wtbmAddTheaterForm').html( response.data );
                        $('#wtbmAddTheaterForm').fadeIn();
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert("Something went wrong!");
                }
            });

        }else if( clickedId === 'wtbpShowtimeAddForm' ){

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
                        $('#wtbm_add-showtime-form').html( response.data );
                        $('#wtbm_add-showtime-form').fadeIn();
                    } else {
                        alert("Error: " + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert("Something went wrong!");
                }
            });


        }else if( clickedId === 'wtbpPricingAddForm' ){

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
                        $('#wtbm_AddPricingForm').html( response.data );
                        $('#wtbm_AddPricingForm').fadeIn();
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
            let movie_html  =  `
                        <tr class="wtbm_movie_content" id="movie_content_${movie_id}" data-movie-id="${movie_id}">
                            <td>
                                <div class="flex items-center">
                                    <img src="${movie.poster}" alt="${movie.title}" class="movie-poster" loading="lazy">
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

            // Hide all conditional fields
            $('#time-range-group, #days-group, #date-range-group, #theater-group').hide();

            // Show relevant field based on type
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

    function clearPricingForm() {
        document.getElementById('pricing-name').value = '';
        document.getElementById('pricing-type').value = 'time';
        document.getElementById('pricing-time-range').value = '';
        document.getElementById('pricing-days').selectedIndex = -1;
        document.getElementById('pricing-start-date').value = '';
        document.getElementById('pricing-end-date').value = '';
        document.getElementById('pricing-theater-type').value = '';
        document.getElementById('pricing-multiplier').value = '';
        document.getElementById('pricing-priority').value = '';
        document.getElementById('pricing-status').value = 'true';
        document.getElementById('pricing-min-seats').value = '';
        document.getElementById('pricing-description').value = '';
        document.getElementById('pricing-combinable').checked = false;
        updatePricingFields();
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

// Bookings Management



}(jQuery));