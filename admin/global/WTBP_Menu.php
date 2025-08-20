<?php

if (!defined('ABSPATH')) {
    die;
}
if( !class_exists( 'WTBP_Menu' ) ) {

    class WTBP_Menu {

        public function __construct() {
            add_action('admin_menu', [$this, 'register_admin_menu']);
        }

        /**
         * ✅ Main menu + submenus
         */
        public function register_admin_menu() {
            $label = MPTRS_Function::get_name();
            $cpt   = MPTRS_Function::get_cpt();

            // Main menu
            add_menu_page(
                $label,
                $label,
                'manage_options',
                'mptrs_main_menu',
                [$this, 'main_menu_page'],
                'dashicons-admin-multisite',
                20
            );

            add_submenu_page(
                'mptrs_main_menu',
                $label,
                $label,
                'manage_options',
                'mptrs_main_menu',
                [$this, 'main_menu_page']
            );


        }
        /**
         * Main menu page callback
         */
        public function main_menu_page() {?>

            <div class="container">
                <div class="sidebar">
                    <div class="sidebar-header">
                        <h1 class="sidebar-title">⚙️ CineMax Admin</h1>
                    </div>

                    <nav class="nav-menu">
                        <button class="nav-item active"" data-tab="movies">
                            🎬 Movies
                        </button>
                        <button class="nav-item" data-tab="theaters">
                            🏛️ Theaters
                        </button>
                        <button class="nav-item" data-tab="showtimes">
                            📅 Showtimes
                        </button>
                        <button class="nav-item" data-tab="pricing">
                            💰 Pricing
                        </button>
                        <button class="nav-item" data-tab="bookings" style="display: none">
                            👥 Bookings
                        </button>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="main-content">
                    <div class="header">
                        <h2>Backend Configuration</h2>
                        <p>Manage your cinema booking system settings</p>
                    </div>

                    <?php do_action( 'movie_content');?>
                    <!-- Movies Tab -->
                    <div id="movies-content" class="tab-content active">
                        <div class="section">
                            <div class="section-header">
                                <h3 class="section-title">Movies Management</h3>
                                <button class="btn btn-primary wtbpShowHideAddForm" id="wtbpAddedMovieForm">
                                    ➕ Add Movie
                                </button>
                            </div>
                        </div>

                        <!-- Add Movie Form -->
                        <div id="add-movie-form" class="form-section" style="display: none">
                            <h4 class="mb-4 font-semibold">Add New Movie</h4>
                            <div class="grid grid-cols-2 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Movie Title</label>
                                    <input type="text" id="movie-title" class="form-input" placeholder="Movie Title">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Genre</label>
                                    <input type="text" id="movie-genre" class="form-input" placeholder="Genre">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Duration</label>
                                    <input type="text" id="movie-duration" class="form-input" placeholder="2h 30m">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Rating</label>
                                    <input type="number" id="movie-rating" class="form-input" step="0.1" placeholder="8.5">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Release Date</label>
                                    <input type="date" id="movie-release-date" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Poster URL</label>
                                    <input type="url" id="movie-poster" class="form-input" placeholder="https://...">
                                </div>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label">Description</label>
                                <textarea id="movie-description" class="form-input" rows="3" placeholder="Movie description"></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button class="btn btn-success mptrs_add_new_movie" id="mptrs_add_new_movie">Add Movie</button>
                                <button class="btn btn-secondary" onclick="hideAddMovieForm()">Cancel</button>
                            </div>
                        </div>

                        <!-- Movies Table -->
                        <div class="section">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Movie</th>
                                    <th>Genre</th>
                                    <th>Duration</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody id="movies-table-body">


                                <tr>
                                    <td>
                                        <div class="flex items-center">
                                            <img src="https://via.placeholder.com/200x300/4A90E2/ffffff?text=GOTG+Vol.3" alt="Guardians of the Galaxy Vol. 3" class="movie-poster">
                                            <div>
                                                <div class="font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                                <div class="text-sm text-gray-500">Released: 2025-05-05</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-900">Action, Adventure, Comedy</td>
                                    <td class="text-sm text-gray-900">2h 30m</td>
                                    <td class="text-sm font-medium">⭐ 8.2</td>
                                    <td>
                                        <span class="status-badge status-active">active</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon edit" title="Edit Movie">✏️</button>
                                            <button class="btn-icon delete" title="Delete Movie">🗑️</button>
                                        </div>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Theaters Tab -->
                    <div id="theaters-content" class="tab-content">
                        <div class="section">
                            <div class="section-header">
                                <h3 class="section-title">Theater Management</h3>
                                <button class="btn btn-primary wtbpShowHideAddForm" id="wtbpTheaterAddForm">
                                    ➕ Add Theater
                                </button>
                            </div>
                        </div>

                        <!-- Add Theater Form -->
                        <div id="add-theater-form" class="form-section" style="display: none">
                            <h4 class="mb-4 font-semibold">Add New Theater</h4>
                            <div class="grid grid-cols-2 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Theater Name</label>
                                    <input type="text" id="theater-name" class="form-input" placeholder="Screen 1">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Theater Type</label>
                                    <select id="theater-type" class="form-input">
                                        <option value="Standard">Standard</option>
                                        <option value="Premium">Premium</option>
                                        <option value="IMAX">IMAX</option>
                                        <option value="VIP">VIP</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Rows</label>
                                    <input type="number" id="theater-rows" class="form-input" placeholder="8">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Seats per Row</label>
                                    <input type="number" id="theater-seats-per-row" class="form-input" placeholder="12">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sound System</label>
                                    <select id="theater-sound" class="form-input">
                                        <option value="Dolby Digital">Dolby Digital</option>
                                        <option value="Dolby Atmos">Dolby Atmos</option>
                                        <option value="IMAX Enhanced">IMAX Enhanced</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select id="theater-status" class="form-input">
                                        <option value="active">Active</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label">Description</label>
                                <textarea id="theater-description" class="form-input" rows="3" placeholder="theater description"></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button class="btn btn-success" id="wtbp_add_new_theater">Add Theater</button>
                                <button class="btn btn-secondary" >Cancel</button>
                            </div>
                        </div>

                        <!-- Theaters Table -->
                        <div class="section">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Capacity</th>
                                    <th>Sound System</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody id="theaters-table-body">
                                    <tr>
                                        <td>
                                            <div class="font-medium text-gray-900">Screen 1</div>
                                            <div class="text-sm text-gray-500">8 × 12 layout</div>
                                        </td>
                                        <td class="text-sm text-gray-900">Standard</td>
                                        <td class="text-sm text-gray-900">96 seats</td>
                                        <td class="text-sm text-gray-900">Dolby Digital</td>
                                        <td>
                                            <span class="status-badge status-active">active</span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button class="btn-icon edit" title="Edit Theater">✏️</button>
                                                <button class="btn-icon delete" title="Delete Theater">🗑️</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Showtimes Tab -->
                    <div id="showtimes-content" class="tab-content">
                        <div class="section">
                            <div class="section-header">
                                <h3 class="section-title">Showtimes Management</h3>
                                <button class="btn btn-primary wtbpShowHideAddForm" id="wtbpShowtimeAddForm">
                                    ➕ Add Showtime
                                </button>
                            </div>
                        </div>

                        <!-- Add Showtime Form -->
                        <div id="add-showtime-form" class="form-section hidden" style="display: none">
                            <h4 class="mb-4 font-semibold">Create New Showtime</h4>
                            <div class="form-group">
                                <label class="form-label">Show time Name</label>
                                <input type="text" id="showTimeName" class="form-input" placeholder="Show time 1">
                            </div>
                            <div class="grid grid-cols-3 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Movie</label>
                                    <select id="showtime-movie" class="form-input">
                                        <option value="">Select Movie</option>
                                        <option value="1">Guardians of the Galaxy Vol. 3</option>
                                        <option value="2">Spider-Man: Across the Spider-Verse</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Theater</label>
                                    <select id="showtime-theater" class="form-input">
                                        <option value="">Select Theater</option>
                                        <option value="1">Screen 1</option>
                                        <option value="2">Screen 2</option>
                                        <option value="3">Screen 3</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Date</label>
                                    <input type="date" id="showtime-date" class="form-input" min="2025-08-19">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" id="showtime-time-start" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">End Time</label>
                                    <input type="time" id="showtime-time-end" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Price ($)</label>
                                    <input type="number" id="showtime-price" class="form-input" step="0.01" placeholder="12.99">
                                </div>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label">Description</label>
                                <textarea id="showTime-description" class="form-input" rows="3" placeholder="Show time description"></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button class="btn btn-success" id="wtbm_add_new_show_time">Add Showtime</button>
                                <button class="btn btn-secondary" onclick="hideAddShowtimeForm()">Cancel</button>
                            </div>
                        </div>

                        <!-- Showtimes Table -->
                        <div class="section">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Movie</th>
                                    <th>Theater</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody id="showtimes-table-body">
                                    <tr>
                                        <td>
                                            <div class="text-sm font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                            <div class="text-sm text-gray-500">Action, Adventure, Comedy</div>
                                        </td>
                                        <td class="text-sm text-gray-900">Screen 1</td>
                                        <td class="text-sm text-gray-900">2025-08-16</td>
                                        <td class="text-sm text-gray-900">10:30</td>
                                        <td class="text-sm font-medium text-gray-900">12.99</td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button class="btn-icon edit" onclick="editShowtime(1)" title="Edit Showtime">✏️</button>
                                                <button class="btn-icon delete" onclick="deleteShowtime(1)" title="Delete Showtime">🗑️</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pricing Tab -->
                    <div id="pricing-content" class="tab-content">
                        <div class="section">
                            <div class="section-header">
                                <h3 class="section-title">Pricing Rules</h3>
                                <button class="btn btn-primary wtbpShowHideAddForm" id="wtbpPricingAddForm">
                                    ➕ Add Pricing Rule
                                </button>
                            </div>
                        </div>

                        <!-- Add Pricing Form -->
                        <div id="add-pricing-form" class="form-section hidden" style="display: none">
                            <h4 class="mb-4 font-semibold">Add New Pricing Rule</h4>
                            <div class="grid grid-cols-2 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Rule Name</label>
                                    <input type="text" id="pricing-name" class="form-input" placeholder="e.g., Matinee, Evening, Weekend" required="">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Rule Type</label>
                                    <select id="pricing-type" class="form-input">
                                        <option value="time">Time-based</option>
                                        <option value="day">Day-based</option>
                                        <option value="date">Date-based</option>
                                        <option value="theater">Theater-based</option>
                                    </select>
                                </div>
                                <div class="form-group" id="time-range-group">
                                    <label class="form-label">Time Range</label>
                                    <input type="text" id="pricing-time-range" class="form-input" placeholder="e.g., 09:00-14:00">
                                </div>
                                <div class="form-group" id="days-group" style="display: none;">
                                    <label class="form-label">Days of Week</label>
                                    <select id="pricing-days" class="form-input" multiple="">
                                        <option value="monday">Monday</option>
                                        <option value="tuesday">Tuesday</option>
                                        <option value="wednesday">Wednesday</option>
                                        <option value="thursday">Thursday</option>
                                        <option value="friday">Friday</option>
                                        <option value="saturday">Saturday</option>
                                        <option value="sunday">Sunday</option>
                                    </select>
                                </div>
                                <div class="form-group" id="date-range-group" style="display: none;">
                                    <label class="form-label">Date Range</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="date" id="pricing-start-date" class="form-input" placeholder="Start Date">
                                        <input type="date" id="pricing-end-date" class="form-input" placeholder="End Date">
                                    </div>
                                </div>
                                <div class="form-group" id="theater-group" style="display: none;">
                                    <label class="form-label">Theater Type</label>
                                    <select id="pricing-theater-type" class="form-input">
                                        <option value="">All Theaters</option>
                                        <option value="Standard">Standard</option>
                                        <option value="Premium">Premium</option>
                                        <option value="IMAX">IMAX</option>
                                        <option value="VIP">VIP</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Price Multiplier</label>
                                    <input type="number" id="pricing-multiplier" class="form-input" step="0.1" min="0.1" max="5.0" placeholder="1.0" required="">
                                    <div class="text-sm text-gray-500 mt-1">1.0 = base price, 0.8 = 20% discount, 1.5 = 50% markup</div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Priority</label>
                                    <input type="number" id="pricing-priority" class="form-input" min="1" max="100" placeholder="10">
                                    <div class="text-sm text-gray-500 mt-1">Higher numbers = higher priority (1-100)</div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select id="pricing-status" class="form-input">
                                        <option value="true">Active</option>
                                        <option value="false">Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Minimum Seats</label>
                                    <input type="number" id="pricing-min-seats" class="form-input" min="1" placeholder="1">
                                    <div class="text-sm text-gray-500 mt-1">Minimum seats required for this rule to apply</div>
                                </div>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label">Description</label>
                                <textarea id="pricing-description" class="form-input" rows="2" placeholder="Brief description of when this pricing rule applies"></textarea>
                            </div>
                            <div class="form-group mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="pricing-combinable" class="mr-2">
                                    <span class="text-sm">Can be combined with other rules</span>
                                </label>
                            </div>
                            <div class="flex gap-2">
                                <button class="btn btn-success" id="wtbp_add_new_pricing_rule">Add Pricing Rule</button>
                                <button class="btn btn-secondary" onclick="hideAddPricingForm()">Cancel</button>
                                <button class="btn btn-secondary" id="wtbp_previewPricing" type="button" >Preview Pricing</button>
                            </div>

                            <!-- Preview Section -->
                            <div id="pricing-preview" class="mt-4 p-4 bg-gray-50 rounded-lg" style="display: none;">
                                <h5 class="font-semibold mb-2">Pricing Preview</h5>
                                <div id="preview-content" class="text-sm"></div>
                            </div>
                        </div>

                        <div class="section">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Rule Name</th>
                                    <th>Time Range</th>
                                    <th>Multiplier</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody id="pricing-table-body"><tr>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">IMAX Premium</div>
                                        <div class="text-sm text-gray-500">theater-based rule</div>
                                    </td>
                                    <td class="text-sm text-gray-900">IMAX</td>
                                    <td class="text-sm text-gray-900">1.8x</td>
                                    <td>
                        <span class="status-badge status-active">
                            Active
                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Priority: 40</div>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon edit" onclick="editPricingRule(5)" title="Edit Rule">✏️</button>
                                            <button class="btn-icon delete" onclick="deletePricingRule(5)" title="Delete Rule">🗑️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Prime Time</div>
                                        <div class="text-sm text-gray-500">time-based rule</div>
                                    </td>
                                    <td class="text-sm text-gray-900">18:01-23:00</td>
                                    <td class="text-sm text-gray-900">1.5x</td>
                                    <td>
                        <span class="status-badge status-active">
                            Active
                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Priority: 30</div>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon edit" onclick="editPricingRule(3)" title="Edit Rule">✏️</button>
                                            <button class="btn-icon delete" onclick="deletePricingRule(3)" title="Delete Rule">🗑️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Weekend</div>
                                        <div class="text-sm text-gray-500">day-based rule</div>
                                    </td>
                                    <td class="text-sm text-gray-900">saturday, sunday</td>
                                    <td class="text-sm text-gray-900">1.2x</td>
                                    <td>
                        <span class="status-badge status-active">
                            Active
                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Priority: 25</div>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon edit" onclick="editPricingRule(4)" title="Edit Rule">✏️</button>
                                            <button class="btn-icon delete" onclick="deletePricingRule(4)" title="Delete Rule">🗑️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Evening</div>
                                        <div class="text-sm text-gray-500">time-based rule</div>
                                    </td>
                                    <td class="text-sm text-gray-900">14:01-18:00</td>
                                    <td class="text-sm text-gray-900">1x</td>
                                    <td>
                        <span class="status-badge status-active">
                            Active
                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Priority: 20</div>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon edit" onclick="editPricingRule(2)" title="Edit Rule">✏️</button>
                                            <button class="btn-icon delete" onclick="deletePricingRule(2)" title="Delete Rule">🗑️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Matinee</div>
                                        <div class="text-sm text-gray-500">time-based rule</div>
                                    </td>
                                    <td class="text-sm text-gray-900">09:00-14:00</td>
                                    <td class="text-sm text-gray-900">0.8x</td>
                                    <td>
                        <span class="status-badge status-active">
                            Active
                        </span>
                                        <div class="text-xs text-gray-500 mt-1">Priority: 10</div>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon edit" onclick="editPricingRule(1)" title="Edit Rule">✏️</button>
                                            <button class="btn-icon delete" onclick="deletePricingRule(1)" title="Delete Rule">🗑️</button>
                                        </div>
                                    </td>
                                </tr></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Bookings Tab -->
                    <div id="bookings-content" class="tab-content">
                        <div class="section">
                            <div class="section-header">
                                <div>
                                    <h3 class="section-title">Booking Management</h3>
                                    <p class="text-sm text-gray-500" id="bookings-count">Total: 0 bookings</p>
                                </div>
                                <button class="btn btn-secondary" onclick="toggleFilters()">
                                    🔍 Filters
                                </button>
                            </div>
                        </div>

                        <!-- Stats Grid - Moved to top -->
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value" style="color: #2563eb;" id="stat-total-bookings">17</div>
                                <div class="stat-label">Total Bookings</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value" style="color: #059669;" id="stat-total-revenue">705.57</div>
                                <div class="stat-label">Total Revenue</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value" style="color: #d97706;" id="stat-paid-bookings">7</div>
                                <div class="stat-label">Paid Bookings</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value" style="color: #dc2626;" id="stat-cancelled-bookings">3</div>
                                <div class="stat-label">Cancelled</div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div id="booking-filters" class="filters-section hidden">
                            <h4 class="mb-4 font-semibold">Filters</h4>
                            <div class="grid grid-cols-4 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Search</label>
                                    <input type="text" id="search-filter" class="form-input" placeholder="Name, Email, ID">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Movie</label>
                                    <select id="movie-filter" class="form-input"><option value="">All Movies</option><option value="1">Guardians of the Galaxy Vol. 3</option><option value="2">Spider-Man: Across the Spider-Verse</option></select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Theater</label>
                                    <select id="theater-filter" class="form-input"><option value="">All Theaters</option><option value="1">Screen 1</option><option value="2">Screen 2</option><option value="3">Screen 3</option></select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Show Time</label>
                                    <select id="showtime-filter" class="form-input">
                                        <option value="">All Show Times</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-4 mb-4">
                                <div class="form-group">
                                    <label class="form-label">Show Date</label>
                                    <input type="date" id="date-filter" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Payment Status</label>
                                    <select id="payment-status-filter" class="form-input">
                                        <option value="">All Payments</option>
                                        <option value="paid">💚 Paid</option>
                                        <option value="pending">🟡 Pending</option>
                                        <option value="processing">🔄 Processing</option>
                                        <option value="partially_paid">🟠 Partially Paid</option>
                                        <option value="failed">❌ Failed</option>
                                        <option value="refunded">🔙 Refunded</option>
                                        <option value="overdue">🔴 Overdue</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Booking Status</label>
                                    <select id="status-filter" class="form-input">
                                        <option value="">All Bookings</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="pending">Pending</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Amount Range</label>
                                    <div class="grid grid-cols-2 gap-1">
                                        <input type="number" id="min-amount-filter" class="form-input" placeholder="Min $" step="0.01">
                                        <input type="number" id="max-amount-filter" class="form-input" placeholder="Max $" step="0.01">
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-secondary" onclick="clearFilters()">Clear Filters</button>
                        </div>

                        <!-- Bookings Table -->
                        <div class="section">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Movie</th>
                                    <th>Theater</th>
                                    <th>Show Date</th>
                                    <th>Seats</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody id="bookings-table-body"><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001234</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">John Smith</div>
                                        <div class="text-sm text-gray-500">john.smith@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                        <div class="text-sm text-gray-500">Action, Adventure, Comedy</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 1</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-16</div>
                                        <div class="text-sm text-gray-500">10:30</div>
                                    </td>
                                    <td class="text-sm text-gray-900">A5, A6</td>
                                    <td class="text-sm font-medium text-gray-900">25.98</td>
                                    <td>
                                        <span class="status-badge status-confirmed">confirmed</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001235</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Sarah Johnson</div>
                                        <div class="text-sm text-gray-500">sarah.j@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                        <div class="text-sm text-gray-500">Action, Adventure, Comedy</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 2</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-16</div>
                                        <div class="text-sm text-gray-500">14:15</div>
                                    </td>
                                    <td class="text-sm text-gray-900">C7, C8, C9</td>
                                    <td class="text-sm font-medium text-gray-900">47.97</td>
                                    <td>
                                        <span class="status-badge status-confirmed">confirmed</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001236</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Mike Chen</div>
                                        <div class="text-sm text-gray-500">mike.chen@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Spider-Man: Across the Spider-Verse</div>
                                        <div class="text-sm text-gray-500">Animation, Action, Adventure</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 1</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-16</div>
                                        <div class="text-sm text-gray-500">18:45</div>
                                    </td>
                                    <td class="text-sm text-gray-900">F10, F11</td>
                                    <td class="text-sm font-medium text-gray-900">31.98</td>
                                    <td>
                                        <span class="status-badge status-pending">pending</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001237</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Emily Davis</div>
                                        <div class="text-sm text-gray-500">emily.davis@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                        <div class="text-sm text-gray-500">Action, Adventure, Comedy</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 3</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-17</div>
                                        <div class="text-sm text-gray-500">20:00</div>
                                    </td>
                                    <td class="text-sm text-gray-900">D5, D6, D7</td>
                                    <td class="text-sm font-medium text-gray-900">56.97</td>
                                    <td>
                                        <span class="status-badge status-confirmed">confirmed</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001238</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Robert Wilson</div>
                                        <div class="text-sm text-gray-500">rob.wilson@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Spider-Man: Across the Spider-Verse</div>
                                        <div class="text-sm text-gray-500">Animation, Action, Adventure</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 2</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-17</div>
                                        <div class="text-sm text-gray-500">15:30</div>
                                    </td>
                                    <td class="text-sm text-gray-900">B12</td>
                                    <td class="text-sm font-medium text-gray-900">18.99</td>
                                    <td>
                                        <span class="status-badge status-cancelled">cancelled</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001239</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Lisa Garcia</div>
                                        <div class="text-sm text-gray-500">lisa.garcia@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                        <div class="text-sm text-gray-500">Action, Adventure, Comedy</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 1</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-18</div>
                                        <div class="text-sm text-gray-500">19:15</div>
                                    </td>
                                    <td class="text-sm text-gray-900">E8, E9</td>
                                    <td class="text-sm font-medium text-gray-900">35.98</td>
                                    <td>
                                        <span class="status-badge status-confirmed">confirmed</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001240</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">David Brown</div>
                                        <div class="text-sm text-gray-500">david.brown@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Spider-Man: Across the Spider-Verse</div>
                                        <div class="text-sm text-gray-500">Animation, Action, Adventure</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 3</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-18</div>
                                        <div class="text-sm text-gray-500">21:30</div>
                                    </td>
                                    <td class="text-sm text-gray-900">G1, G2, G3, G4</td>
                                    <td class="text-sm font-medium text-gray-900">79.96</td>
                                    <td>
                                        <span class="status-badge status-pending">pending</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001241</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Jennifer Lee</div>
                                        <div class="text-sm text-gray-500">jennifer.lee@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                        <div class="text-sm text-gray-500">Action, Adventure, Comedy</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 2</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-19</div>
                                        <div class="text-sm text-gray-500">16:45</div>
                                    </td>
                                    <td class="text-sm text-gray-900">H3, H4</td>
                                    <td class="text-sm font-medium text-gray-900">29.98</td>
                                    <td>
                                        <span class="status-badge status-cancelled">cancelled</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001242</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Michael Zhang</div>
                                        <div class="text-sm text-gray-500">michael.zhang@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Spider-Man: Across the Spider-Verse</div>
                                        <div class="text-sm text-gray-500">Animation, Action, Adventure</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 1</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-19</div>
                                        <div class="text-sm text-gray-500">22:00</div>
                                    </td>
                                    <td class="text-sm text-gray-900">J1, J2, J3</td>
                                    <td class="text-sm font-medium text-gray-900">44.97</td>
                                    <td>
                                        <span class="status-badge status-pending">pending</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001243</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Amanda Rodriguez</div>
                                        <div class="text-sm text-gray-500">amanda.rodriguez@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                        <div class="text-sm text-gray-500">Action, Adventure, Comedy</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 3</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-20</div>
                                        <div class="text-sm text-gray-500">13:30</div>
                                    </td>
                                    <td class="text-sm text-gray-900">K5, K6, K7, K8</td>
                                    <td class="text-sm font-medium text-gray-900">67.96</td>
                                    <td>
                                        <span class="status-badge status-pending">pending</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001244</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Kevin Taylor</div>
                                        <div class="text-sm text-gray-500">kevin.taylor@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Spider-Man: Across the Spider-Verse</div>
                                        <div class="text-sm text-gray-500">Animation, Action, Adventure</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 2</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-20</div>
                                        <div class="text-sm text-gray-500">09:00</div>
                                    </td>
                                    <td class="text-sm text-gray-900">L10, L11</td>
                                    <td class="text-sm font-medium text-gray-900">21.98</td>
                                    <td>
                                        <span class="status-badge status-pending">pending</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001245</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Nicole White</div>
                                        <div class="text-sm text-gray-500">nicole.white@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                        <div class="text-sm text-gray-500">Action, Adventure, Comedy</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 1</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-21</div>
                                        <div class="text-sm text-gray-500">12:00</div>
                                    </td>
                                    <td class="text-sm text-gray-900">M1</td>
                                    <td class="text-sm font-medium text-gray-900">12.99</td>
                                    <td>
                                        <span class="status-badge status-confirmed">confirmed</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001246</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Christopher Adams</div>
                                        <div class="text-sm text-gray-500">chris.adams@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Spider-Man: Across the Spider-Verse</div>
                                        <div class="text-sm text-gray-500">Animation, Action, Adventure</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 3</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-21</div>
                                        <div class="text-sm text-gray-500">17:00</div>
                                    </td>
                                    <td class="text-sm text-gray-900">N2, N3</td>
                                    <td class="text-sm font-medium text-gray-900">33.98</td>
                                    <td>
                                        <span class="status-badge status-confirmed">confirmed</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001247</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Ashley Martinez</div>
                                        <div class="text-sm text-gray-500">ashley.martinez@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                        <div class="text-sm text-gray-500">Action, Adventure, Comedy</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 2</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-22</div>
                                        <div class="text-sm text-gray-500">11:30</div>
                                    </td>
                                    <td class="text-sm text-gray-900">O5, O6, O7</td>
                                    <td class="text-sm font-medium text-gray-900">41.97</td>
                                    <td>
                                        <span class="status-badge status-pending">pending</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001248</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Matthew Thompson</div>
                                        <div class="text-sm text-gray-500">matt.thompson@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Spider-Man: Across the Spider-Verse</div>
                                        <div class="text-sm text-gray-500">Animation, Action, Adventure</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 1</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-22</div>
                                        <div class="text-sm text-gray-500">23:00</div>
                                    </td>
                                    <td class="text-sm text-gray-900">P10, P11, P12</td>
                                    <td class="text-sm font-medium text-gray-900">53.97</td>
                                    <td>
                                        <span class="status-badge status-cancelled">cancelled</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001249</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Jessica Clark</div>
                                        <div class="text-sm text-gray-500">jessica.clark@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Guardians of the Galaxy Vol. 3</div>
                                        <div class="text-sm text-gray-500">Action, Adventure, Comedy</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 3</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-23</div>
                                        <div class="text-sm text-gray-500">14:45</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Q1, Q2</td>
                                    <td class="text-sm font-medium text-gray-900">27.98</td>
                                    <td>
                                        <span class="status-badge status-confirmed">confirmed</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr><tr>
                                    <td class="text-sm font-medium text-gray-900">BK001250</td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Daniel Lewis</div>
                                        <div class="text-sm text-gray-500">daniel.lewis@email.com</div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-900">Spider-Man: Across the Spider-Verse</div>
                                        <div class="text-sm text-gray-500">Animation, Action, Adventure</div>
                                    </td>
                                    <td class="text-sm text-gray-900">Screen 2</td>
                                    <td>
                                        <div class="text-sm text-gray-900">2025-08-23</div>
                                        <div class="text-sm text-gray-500">20:15</div>
                                    </td>
                                    <td class="text-sm text-gray-900">R7, R8, R9, R10</td>
                                    <td class="text-sm font-medium text-gray-900">71.96</td>
                                    <td>
                                        <span class="status-badge status-pending">pending</span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
                                            <button class="btn-icon edit" title="Edit Booking">✏️</button>
                                        </div>
                                    </td>
                                </tr></tbody>
                            </table>

                            <!-- Load More Section -->
                            <div id="load-more-section" class="text-center py-4 border-t border-gray-200">
                                <div class="mb-3">
                                    <span class="text-sm text-gray-600" id="showing-info">Showing 1-8 of 12 bookings</span>
                                </div>
                                <button id="load-more-btn" onclick="loadMoreBookings()" class="btn btn-primary">
                                    📄 Load More Bookings (+8)
                                </button>
                                <div id="no-more-data" class="text-sm text-gray-500 hidden">
                                    ✅ All bookings loaded
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

       <?php }

    }

    new WTBP_Menu();
}