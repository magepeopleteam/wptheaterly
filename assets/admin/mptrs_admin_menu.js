(function ($) {

    $(document).on('click', '.nav-item', function (e) {

        $('.nav-item').removeClass('active');
        $('.tab-content').hide();
        let nav_name = $(this).attr('data-tab').trim();
        let nav_container = nav_name+'-content';
        $("#"+nav_container).fadeIn();
        $(this).addClass('active');

    });
    $(document).on('click', '#mptrs_add_new_movie', function (e) {
        addMovie();
    });
    $(document).on('click', '#mptrsAddedMovieForm', function (e) {
        showAddMovieForm();
    });


    function addMovie( ) {

        let addMovieData = [];

        let movieData = {
            action: "mptrs_insert_movie_post", // WordPress action hook
            title: $("#movie-title").val(),
            genre: $("#movie-genre").val(),
            duration: $("#movie-duration").val(),
            rating: $("#movie-rating").val(),
            release_date: $("#movie-release-date").val(),
            poster: $("#movie-poster").val(),
            description: $("#movie-description").val(),
            status: "publish",
            _ajax_nonce: mptrs_admin_ajax.nonce // security nonce
        };

        alert( mptrs_admin_ajax.ajax_url );
        $.ajax({
            url: mptrs_admin_ajax.ajax_url, // admin-ajax.php
            type: "POST",
            data: movieData,
            success: function(response) {
                if (response.success) {
                    alert("Movie Added: " + response.data.post_title);
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
        console.log( addMovieData );

        renderMoviesTable( movieData );
        // hideAddMovieForm();
    }



let movies = [
    {
        id: 1,
        title: "Guardians of the Galaxy Vol. 3",
        genre: "Action, Adventure, Comedy",
        duration: "2h 30m",
        rating: 8.2,
        poster: "https://via.placeholder.com/200x300/4A90E2/ffffff?text=GOTG+Vol.3",
        description: "The beloved Guardians must reunite for their most dangerous mission yet.",
        status: "active",
        releaseDate: "2025-05-05"
    },
    {
        id: 2,
        title: "Spider-Man: Across the Spider-Verse",
        genre: "Animation, Action, Adventure",
        duration: "2h 20m",
        rating: 9.1,
        poster: "https://via.placeholder.com/200x300/E74C3C/ffffff?text=Spider-Verse",
        description: "Miles Morales catapults across the Multiverse with Gwen Stacy.",
        status: "active",
        releaseDate: "2025-06-02"
    }
];

let theaters = [
    {
        id: 1,
        name: "Screen 1",
        type: "Standard",
        status: "active",
        rows: 8,
        seatsPerRow: 12,
        soundSystem: "Dolby Digital"
    },
    {
        id: 2,
        name: "Screen 2",
        type: "Premium",
        status: "active",
        rows: 8,
        seatsPerRow: 12,
        soundSystem: "Dolby Atmos"
    },
    {
        id: 3,
        name: "Screen 3",
        type: "IMAX",
        status: "maintenance",
        rows: 10,
        seatsPerRow: 12,
        soundSystem: "IMAX Enhanced"
    }
];

let showtimes = [
    { id: 1, movieId: 1, theaterId: 1, date: "2025-08-16", time: "10:30", price: 12.99 },
    { id: 2, movieId: 1, theaterId: 2, date: "2025-08-16", time: "14:15", price: 15.99 },
    { id: 3, movieId: 2, theaterId: 1, date: "2025-08-16", time: "18:45", price: 18.99 }
];

let pricingRules = [
    { id: 1, name: "Matinee", type: "time", timeRange: "09:00-14:00", multiplier: 0.8, active: true, priority: 10, description: "Morning and early afternoon discount" },
    { id: 2, name: "Evening", type: "time", timeRange: "14:01-18:00", multiplier: 1.0, active: true, priority: 20, description: "Standard evening pricing" },
    { id: 3, name: "Prime Time", type: "time", timeRange: "18:01-23:00", multiplier: 1.5, active: true, priority: 30, description: "Peak evening hours" },
    { id: 4, name: "Weekend", type: "day", days: ["saturday", "sunday"], multiplier: 1.2, active: true, priority: 25, description: "Weekend surcharge" },
    { id: 5, name: "IMAX Premium", type: "theater", theaterType: "IMAX", multiplier: 1.8, active: true, priority: 40, description: "IMAX theater premium pricing" }
];

// Movies Management
function renderMoviesTable( movie ) {
    const tbody = document.getElementById('movies-table-body');

        const row = document.createElement('tr');
        row.innerHTML = `
                    <td>
                        <div class="flex items-center">
                            <img src="${movie.poster}" alt="${movie.title}" class="movie-poster">
                            <div>
                                <div class="font-medium text-gray-900">${movie.title}</div>
                                
                                <div class="text-sm text-gray-500">Released: ${movie.releaseDate}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-gray-900">${movie.genre}</td>
                    <td class="text-sm text-gray-900">${movie.duration}</td>
                    <td class="text-sm font-medium">⭐ ${movie.rating}</td>
                    <td>
                        <span class="status-badge status-${movie.status}">${movie.status}</span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <button class="btn-icon edit" onclick="editMovie(${movie.id})" title="Edit Movie">✏️</button>
                            <button class="btn-icon delete" onclick="deleteMovie(${movie.id})" title="Delete Movie">🗑️</button>
                        </div>
                    </td>
                `;
        tbody.appendChild(row);

}

function showAddMovieForm() {
    $('#add-movie-form').fadeIn();
}

function hideAddMovieForm() {
    document.getElementById('add-movie-form').classList.add('hidden');
    clearMovieForm();
}

function clearMovieForm() {
    document.getElementById('movie-title').value = '';
    document.getElementById('movie-genre').value = '';
    document.getElementById('movie-duration').value = '';
    document.getElementById('movie-rating').value = '';
    document.getElementById('movie-release-date').value = '';
    document.getElementById('movie-poster').value = '';
    document.getElementById('movie-description').value = '';
}



function editMovie(id) {
    alert('Edit movie functionality - ID: ' + id);
}

function deleteMovie(id) {
    if (confirm('Are you sure you want to delete this movie?')) {
        movies = movies.filter(m => m.id !== id);
        renderMoviesTable();
    }
}

// Theaters Management
function renderTheatersTable() {
    const tbody = document.getElementById('theaters-table-body');
    tbody.innerHTML = '';

    theaters.forEach(theater => {
        const capacity = theater.rows * theater.seatsPerRow;
        const row = document.createElement('tr');
        row.innerHTML = `
                    <td>
                        <div class="font-medium text-gray-900">${theater.name}</div>
                        <div class="text-sm text-gray-500">${theater.rows} × ${theater.seatsPerRow} layout</div>
                    </td>
                    <td class="text-sm text-gray-900">${theater.type}</td>
                    <td class="text-sm text-gray-900">${capacity} seats</td>
                    <td class="text-sm text-gray-900">${theater.soundSystem}</td>
                    <td>
                        <span class="status-badge status-${theater.status}">${theater.status}</span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <button class="btn-icon edit" onclick="editTheater(${theater.id})" title="Edit Theater">✏️</button>
                            <button class="btn-icon delete" onclick="deleteTheater(${theater.id})" title="Delete Theater">🗑️</button>
                        </div>
                    </td>
                `;
        tbody.appendChild(row);
    });
}

function showAddTheaterForm() {
    document.getElementById('add-theater-form').classList.remove('hidden');
}

function hideAddTheaterForm() {
    document.getElementById('add-theater-form').classList.add('hidden');
    clearTheaterForm();
}

function clearTheaterForm() {
    document.getElementById('theater-name').value = '';
    document.getElementById('theater-type').value = 'Standard';
    document.getElementById('theater-rows').value = '';
    document.getElementById('theater-seats-per-row').value = '';
    document.getElementById('theater-sound').value = 'Dolby Digital';
    document.getElementById('theater-status').value = 'active';
}

function addTheater() {
    const rows = parseInt(document.getElementById('theater-rows').value);
    const seatsPerRow = parseInt(document.getElementById('theater-seats-per-row').value);

    const theater = {
        id: Date.now(),
        name: document.getElementById('theater-name').value,
        type: document.getElementById('theater-type').value,
        rows: rows,
        seatsPerRow: seatsPerRow,
        soundSystem: document.getElementById('theater-sound').value,
        status: document.getElementById('theater-status').value
    };

    theaters.push(theater);
    renderTheatersTable();
    hideAddTheaterForm();
}

function editTheater(id) {
    alert('Edit theater functionality - ID: ' + id);
}

function deleteTheater(id) {
    if (confirm('Are you sure you want to delete this theater?')) {
        theaters = theaters.filter(t => t.id !== id);
        renderTheatersTable();
    }
}

// Showtimes Management
function renderShowtimesTable() {
    const tbody = document.getElementById('showtimes-table-body');
    tbody.innerHTML = '';

    showtimes.forEach(showtime => {
        const movie = movies.find(m => m.id === showtime.movieId);
        const theater = theaters.find(t => t.id === showtime.theaterId);

        const row = document.createElement('tr');
        row.innerHTML = `
                    <td>
                        <div class="text-sm font-medium text-gray-900">${movie?.title || 'Unknown'}</div>
                        <div class="text-sm text-gray-500">${movie?.genre || 'N/A'}</div>
                    </td>
                    <td class="text-sm text-gray-900">${theater?.name || 'Unknown'}</td>
                    <td class="text-sm text-gray-900">${showtime.date}</td>
                    <td class="text-sm text-gray-900">${showtime.time}</td>
                    <td class="text-sm font-medium text-gray-900">${showtime.price}</td>
                    <td>
                        <div class="flex gap-2">
                            <button class="btn-icon edit" onclick="editShowtime(${showtime.id})" title="Edit Showtime">✏️</button>
                            <button class="btn-icon delete" onclick="deleteShowtime(${showtime.id})" title="Delete Showtime">🗑️</button>
                        </div>
                    </td>
                `;
        tbody.appendChild(row);
    });
}

function populateShowtimeSelects() {
    const movieSelect = document.getElementById('showtime-movie');
    const theaterSelect = document.getElementById('showtime-theater');

    movieSelect.innerHTML = '<option value="">Select Movie</option>';
    theaterSelect.innerHTML = '<option value="">Select Theater</option>';

    movies.forEach(movie => {
        movieSelect.innerHTML += `<option value="${movie.id}">${movie.title}</option>`;
    });

    theaters.forEach(theater => {
        theaterSelect.innerHTML += `<option value="${theater.id}">${theater.name}</option>`;
    });
}

function showAddShowtimeForm() {
    document.getElementById('add-showtime-form').classList.remove('hidden');
    populateShowtimeSelects();
}

function hideAddShowtimeForm() {
    document.getElementById('add-showtime-form').classList.add('hidden');
    clearShowtimeForm();
}

function clearShowtimeForm() {
    document.getElementById('showtime-movie').value = '';
    document.getElementById('showtime-theater').value = '';
    document.getElementById('showtime-date').value = '';
    document.getElementById('showtime-time').value = '';
    document.getElementById('showtime-price').value = '';
}

function addShowtime() {
    const showtime = {
        id: Date.now(),
        movieId: parseInt(document.getElementById('showtime-movie').value),
        theaterId: parseInt(document.getElementById('showtime-theater').value),
        date: document.getElementById('showtime-date').value,
        time: document.getElementById('showtime-time').value,
        price: parseFloat(document.getElementById('showtime-price').value)
    };

    showtimes.push(showtime);
    renderShowtimesTable();
    hideAddShowtimeForm();
}

function editShowtime(id) {
    alert('Edit showtime functionality - ID: ' + id);
}

function deleteShowtime(id) {
    if (confirm('Are you sure you want to delete this showtime?')) {
        showtimes = showtimes.filter(s => s.id !== id);
        renderShowtimesTable();
    }
}

// Pricing Management
function renderPricingTable() {
    const tbody = document.getElementById('pricing-table-body');
    tbody.innerHTML = '';

    // Sort by priority (higher priority first)
    const sortedRules = [...pricingRules].sort((a, b) => (b.priority || 0) - (a.priority || 0));

    sortedRules.forEach(rule => {
        const row = document.createElement('tr');
        const ruleDetails = getPricingRuleDetails(rule);

        row.innerHTML = `
                    <td>
                        <div class="text-sm font-medium text-gray-900">${rule.name}</div>
                        <div class="text-sm text-gray-500">${rule.type || 'time'}-based rule</div>
                    </td>
                    <td class="text-sm text-gray-900">${ruleDetails}</td>
                    <td class="text-sm text-gray-900">${rule.multiplier}x</td>
                    <td>
                        <span class="status-badge ${rule.active ? 'status-active' : 'status-inactive'}">
                            ${rule.active ? 'Active' : 'Inactive'}
                        </span>
                        ${rule.priority ? `<div class="text-xs text-gray-500 mt-1">Priority: ${rule.priority}</div>` : ''}
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <button class="btn-icon edit" onclick="editPricingRule(${rule.id})" title="Edit Rule">✏️</button>
                            <button class="btn-icon delete" onclick="deletePricingRule(${rule.id})" title="Delete Rule">🗑️</button>
                        </div>
                    </td>
                `;
        tbody.appendChild(row);
    });
}

function getPricingRuleDetails(rule) {
    switch(rule.type) {
        case 'time':
            return rule.timeRange || 'All day';
        case 'day':
            return rule.days ? rule.days.join(', ') : 'All days';
        case 'date':
            return rule.dateRange || 'All dates';
        case 'theater':
            return rule.theaterType || 'All theaters';
        default:
            return rule.timeRange || 'All times';
    }
}

function updatePricingFields() {
    const type = document.getElementById('pricing-type').value;

    // Hide all conditional fields
    document.getElementById('time-range-group').style.display = 'none';
    document.getElementById('days-group').style.display = 'none';
    document.getElementById('date-range-group').style.display = 'none';
    document.getElementById('theater-group').style.display = 'none';

    // Show relevant field based on type
    switch(type) {
        case 'time':
            document.getElementById('time-range-group').style.display = 'block';
            break;
        case 'day':
            document.getElementById('days-group').style.display = 'block';
            break;
        case 'date':
            document.getElementById('date-range-group').style.display = 'block';
            break;
        case 'theater':
            document.getElementById('theater-group').style.display = 'block';
            break;
    }
}

function showAddPricingForm() {
    document.getElementById('add-pricing-form').classList.remove('hidden');
    updatePricingFields(); // Initialize field visibility
}

function hideAddPricingForm() {
    document.getElementById('add-pricing-form').classList.add('hidden');
    document.getElementById('pricing-preview').style.display = 'none';
    clearPricingForm();
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

function addPricingRule() {
    const name = document.getElementById('pricing-name').value;
    const type = document.getElementById('pricing-type').value;
    const multiplier = parseFloat(document.getElementById('pricing-multiplier').value);

    if (!name || !multiplier) {
        alert('Please fill in required fields: Name and Price Multiplier');
        return;
    }

    const rule = {
        id: Date.now(),
        name: name,
        type: type,
        multiplier: multiplier,
        active: document.getElementById('pricing-status').value === 'true',
        priority: parseInt(document.getElementById('pricing-priority').value) || 10,
        description: document.getElementById('pricing-description').value,
        minSeats: parseInt(document.getElementById('pricing-min-seats').value) || 1,
        combinable: document.getElementById('pricing-combinable').checked
    };

    // Add type-specific properties
    switch(type) {
        case 'time':
            rule.timeRange = document.getElementById('pricing-time-range').value;
            break;
        case 'day':
            const selectedDays = Array.from(document.getElementById('pricing-days').selectedOptions).map(o => o.value);
            rule.days = selectedDays;
            break;
        case 'date':
            rule.startDate = document.getElementById('pricing-start-date').value;
            rule.endDate = document.getElementById('pricing-end-date').value;
            rule.dateRange = `${rule.startDate || 'start'} to ${rule.endDate || 'end'}`;
            break;
        case 'theater':
            rule.theaterType = document.getElementById('pricing-theater-type').value;
            break;
    }

    pricingRules.push(rule);
    renderPricingTable();
    hideAddPricingForm();

    // Show success message
    alert(`Pricing rule "${name}" added successfully!`);
}

function editPricingRule(id) {
    const rule = pricingRules.find(r => r.id === id);
    if (rule) {
        alert(`Edit pricing rule: ${rule.name}\nType: ${rule.type}\nMultiplier: ${rule.multiplier}x\nStatus: ${rule.active ? 'Active' : 'Inactive'}`);
    }
}

function deletePricingRule(id) {
    const rule = pricingRules.find(r => r.id === id);
    if (rule && confirm(`Are you sure you want to delete the pricing rule "${rule.name}"?`)) {
        pricingRules = pricingRules.filter(r => r.id !== id);
        renderPricingTable();
        alert(`Pricing rule "${rule.name}" deleted successfully!`);
    }
}

// Bookings Management
function renderBookingsTable() {
    const tbody = document.getElementById('bookings-table-body');
    tbody.innerHTML = '';

    filteredBookings.forEach(booking => {
        const movie = movies.find(m => m.id === booking.movieId);
        const theater = theaters.find(t => t.id === booking.theaterId);

        const row = document.createElement('tr');
        row.innerHTML = `
                    <td class="text-sm font-medium text-gray-900">${booking.id}</td>
                    <td>
                        <div class="text-sm font-medium text-gray-900">${booking.customerName}</div>
                        <div class="text-sm text-gray-500">${booking.customerEmail}</div>
                    </td>
                    <td>
                        <div class="text-sm font-medium text-gray-900">${movie?.title || 'Unknown'}</div>
                        <div class="text-sm text-gray-500">${movie?.genre || 'N/A'}</div>
                    </td>
                    <td class="text-sm text-gray-900">${theater?.name || 'Unknown'}</td>
                    <td>
                        <div class="text-sm text-gray-900">${booking.date}</div>
                        <div class="text-sm text-gray-500">${booking.time}</div>
                    </td>
                    <td class="text-sm text-gray-900">${booking.seats.join(', ')}</td>
                    <td class="text-sm font-medium text-gray-900">${booking.totalAmount}</td>
                    <td>
                        <span class="status-badge status-${booking.bookingStatus}">${booking.bookingStatus}</span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                        </div>
                    </td>
                `;
        tbody.appendChild(row);
    });
}

function populateBookingFilters() {
    const movieFilter = document.getElementById('movie-filter');
    const theaterFilter = document.getElementById('theater-filter');

    movieFilter.innerHTML = '<option value="">All Movies</option>';
    theaterFilter.innerHTML = '<option value="">All Theaters</option>';

    movies.forEach(movie => {
        movieFilter.innerHTML += `<option value="${movie.id}">${movie.title}</option>`;
    });

    theaters.forEach(theater => {
        theaterFilter.innerHTML += `<option value="${theater.id}">${theater.name}</option>`;
    });

    // Add event listeners
    document.getElementById('search-filter').addEventListener('input', applyFilters);
    document.getElementById('movie-filter').addEventListener('change', applyFilters);
    document.getElementById('theater-filter').addEventListener('change', applyFilters);
    document.getElementById('status-filter').addEventListener('change', applyFilters);
}

function applyFilters() {
    const searchTerm = document.getElementById('search-filter').value.toLowerCase();
    const movieId = document.getElementById('movie-filter').value;
    const theaterId = document.getElementById('theater-filter').value;
    const status = document.getElementById('status-filter').value;

    filteredBookings = bookings.filter(booking => {
        return (
            (!movieId || booking.movieId.toString() === movieId) &&
            (!theaterId || booking.theaterId.toString() === theaterId) &&
            (!status || booking.bookingStatus === status) &&
            (!searchTerm ||
                booking.customerName.toLowerCase().includes(searchTerm) ||
                booking.customerEmail.toLowerCase().includes(searchTerm) ||
                booking.id.toLowerCase().includes(searchTerm))
        );
    });

    renderBookingsTable();
    updateBookingStats();
    document.getElementById('bookings-count').textContent = `Total: ${filteredBookings.length} bookings`;
}

function clearFilters() {
    document.getElementById('search-filter').value = '';
    document.getElementById('movie-filter').value = '';
    document.getElementById('theater-filter').value = '';
    document.getElementById('status-filter').value = '';
    applyFilters();
}

function toggleFilters() {
    const filtersDiv = document.getElementById('booking-filters');
    showFilters = !showFilters;
    if (showFilters) {
        filtersDiv.classList.remove('hidden');
    } else {
        filtersDiv.classList.add('hidden');
    }
}

function updateBookingStats() {
    const totalBookings = filteredBookings.length;
    const totalRevenue = filteredBookings.reduce((sum, booking) => sum + booking.totalAmount, 0);
    const paidBookings = filteredBookings.filter(b => b.paymentStatus === 'paid').length;
    const cancelledBookings = filteredBookings.filter(b => b.bookingStatus === 'cancelled').length;

    document.getElementById('stat-total-bookings').textContent = totalBookings;
    document.getElementById('stat-total-revenue').textContent = `${totalRevenue.toFixed(2)}`;
    document.getElementById('stat-paid-bookings').textContent = paidBookings;
    document.getElementById('stat-cancelled-bookings').textContent = cancelledBookings;
}


}(jQuery));