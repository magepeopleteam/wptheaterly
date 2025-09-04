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




    // Data
    var movies = {
        '2025-08-18': [
            { id: 1, title: 'Weapons', duration: '2h 8m', poster: '🔫' },
            { id: 2, title: 'MISSION: IMPOSSIBLE THE FINAL RECKONING', duration: '2h 30m', poster: '🎬' },
            { id: 3, title: 'Jurassic World Rebirth', duration: '2h 15m', poster: '🦕' },
            { id: 4, title: 'I Know What You Did Last Summer', duration: '1h 45m', poster: '😱' },
            { id: 5, title: 'Sicario 3', duration: '2h 0m', poster: '🔫' },
            { id: 6, title: 'Superman (3D)', duration: '2h 20m', poster: '🦸' },
            { id: 7, title: 'The Fantastic Four: First Steps', duration: '2h 10m', poster: '🌟' }
        ],
        '2025-08-19': [
            { id: 1, title: 'Weapons', duration: '2h 8m', poster: '🔫' },
            { id: 3, title: 'Jurassic World Rebirth', duration: '2h 15m', poster: '🦕' },
            { id: 6, title: 'Superman (3D)', duration: '2h 20m', poster: '🦸' },
            { id: 8, title: 'Avatar 3', duration: '3h 0m', poster: '🌊' },
            { id: 9, title: 'Fast & Furious 11', duration: '2h 25m', poster: '🏎️' }
        ]
    };

    var halls = {
        1: {
            halls: [
                { id: 'hall1', name: 'Hall 1', times: ['2:00 PM', '5:30 PM', '8:45 PM'] },
                { id: 'hall2', name: 'Hall 2', times: ['1:30 PM', '7:20 PM'] }
            ]
        },
        2: {
            halls: [
                { id: 'hall3', name: 'Hall 3', times: ['3:00 PM', '9:15 PM'] }
            ]
        }
    };
// State
    var currentState = {
        selectedDate: '2025-08-18',
        selectedMovie: null,
        selectedHall: null,
        selectedTime: null,
        selectedSeats: []
    };

// Initialize
    $(document).ready(function () {
        // loadMovies(currentState.selectedDate);
        setupEventListeners();
        updateSummaryDate();
    });

    function setupEventListeners() {
        // Date selection
        /*$(document).on('click', '.wtbm_booking_date_date_card', function () {
            $('#dateSelector .wtbm_booking_date_date_card').removeClass('active');
            $(this).addClass('active');

            currentState.selectedDate = $(this).data('date');
            loadMovies(currentState.selectedDate);
            resetSelections();
            updateSummaryDate();
        });*/

        // Movie selection
        $(document).on('click', '#moviesGrid .movie-card', function () {
            $('#moviesGrid .movie-card').removeClass('selected');
            $(this).addClass('selected');

            currentState.selectedMovie = parseInt($(this).data('movie-id'));
            loadHalls(currentState.selectedMovie);
            updateMovieSummary();
        });

        // Time slot selection
        $(document).on('click', '#hallsList .time-slot', function () {
            $('#hallsList .time-slot').removeClass('selected');
            $(this).addClass('selected');

            currentState.selectedTime = $(this).text();
            currentState.selectedHall = $(this).data('hall');
            generateSeatMap();
            updateStep(3);
            updateTimeSummary();
        });

        // Purchase button
        $(document).on('click', '#purchaseBtn', function () {
            if (currentState.selectedSeats.length > 0) {
                alert('Booking confirmed! Thank you for your purchase.');
            }
        });
    }

    function loadMovies(date) {
        var moviesList = movies[date] || [];
        var html = $.map(moviesList, function (movie) {
            return `
            <div class="movie-card" data-movie-id="${movie.id}">
                <div class="movie-poster">${movie.poster}</div>
                <div class="movie-info">
                    <div class="movie-title">${movie.title}</div>
                    <div class="movie-details">Duration - ${movie.duration}</div>
                </div>
            </div>`;
        }).join('');

        $('#moviesGrid').html(html);
    }

    function loadHalls(movieId) {
        var movieHalls = halls[movieId] || { halls: [] };

        if (movieHalls.halls.length === 0) {
            $('#hallsList').html('<div style="text-align: center; padding: 40px; color: #666;">No shows available for this movie</div>');
            return;
        }

        var html = $.map(movieHalls.halls, function (hall) {
            var timesHtml = $.map(hall.times, function (time) {
                return `<div class="time-slot" data-hall="${hall.id}" data-time="${time}">${time}</div>`;
            }).join('');

            return `
            <div class="hall-card">
                <div class="hall-name">${hall.name}</div>
                <div class="time-slots">${timesHtml}</div>
            </div>`;
        }).join('');

        $('#hallsList').html(html);
        $('#hallSection').removeClass('hidden');
    }

    function generateSeatMap() {
        var rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        var seatsPerRow = 12;
        var seatMapHtml = '';

        $.each(rows, function (i, row) {
            var rowHtml = `<div class="seat-row"><div class="row-label">${row}</div>`;
            for (var seatNum = 1; seatNum <= seatsPerRow; seatNum++) {
                var seatId = row + seatNum;
                var isOccupied = Math.random() < 0.3;
                var seatClass = isOccupied ? 'occupied' : 'available';
                rowHtml += `<div class="seat ${seatClass}" data-seat="${seatId}">${seatNum}</div>`;
            }
            rowHtml += '</div>';
            seatMapHtml += rowHtml;
        });

        $('#seatsGrid').html(seatMapHtml);
        $('#seatSection').removeClass('hidden');
    }

// Seat selection
    $(document).on('click', '#seatsGrid .seat.available', function () {
        $(this).toggleClass('selected');
        var seatId = $(this).data('seat');

        if ($(this).hasClass('selected')) {
            currentState.selectedSeats.push(seatId);
        } else {
            currentState.selectedSeats = $.grep(currentState.selectedSeats, function (id) { return id !== seatId; });
        }

        updateSeatSummary();
        updateStep(4);
    });

    function resetSelections() {
        currentState.selectedMovie = null;
        currentState.selectedHall = null;
        currentState.selectedTime = null;
        currentState.selectedSeats = [];

        $('#hallSection, #seatSection').addClass('hidden');
        updateStep(1);
        resetSummary();
    }

    function updateStep(step) {
        $('.step').removeClass('active completed').each(function (index) {
            if (index + 1 < step) {
                $(this).addClass('completed');
            } else if (index + 1 === step) {
                $(this).addClass('active');
            }
        });
    }

    function updateSummaryDate() {
        var date = new Date(currentState.selectedDate);
        var options = { month: 'short', day: 'numeric', year: '2-digit' };
        $('#summaryDate').text(date.toLocaleDateString('en-US', options));
    }

    function updateMovieSummary() {
        var movie = (movies[currentState.selectedDate] || []).find(m => m.id === currentState.selectedMovie);
        if (movie) {
            $('#selectedMovieDisplay').html(`
            <div style="width: 60px; height: 80px; background: linear-gradient(45deg, #ff6b6b, #4ecdc4); border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px;">${movie.poster}</div>
            <div style="font-weight: bold;">${movie.title}</div>
            <div style="color: #666; font-size: 12px;">${movie.duration}</div>
        `);
        }
    }

    function updateTimeSummary() {
        $('#summaryTime').text(currentState.selectedTime || '--');
        $('#summaryHall').text(currentState.selectedHall ? currentState.selectedHall.replace('hall', 'Hall ') : '--');
    }

    function updateSeatSummary() {
        var quantity = currentState.selectedSeats.length;
        var pricePerSeat = 400;
        var total = quantity * pricePerSeat;

        $('#summaryQuantity').text(quantity);
        $('#summarySeats').text(currentState.selectedSeats.join(', ') || '--');
        $('#summaryTotal').text(total + ' BDT');
        $('#purchaseBtn').prop('disabled', quantity === 0);
    }

    function resetSummary() {
        $('#selectedMovieDisplay').html(`
        <div style="width: 60px; height: 80px; background: #ddd; border-radius: 8px; margin-bottom: 15px;"></div>
        <div style="color: #666;">Select a movie</div>
    `);
        $('#summaryHall').text('--');
        $('#summaryTime').text('--');
        $('#summaryQuantity').text('0');
        $('#summarySeats').text('--');
        $('#summaryTotal').text('0 BDT');
        $('#purchaseBtn').prop('disabled', true);
    }



}(jQuery));
