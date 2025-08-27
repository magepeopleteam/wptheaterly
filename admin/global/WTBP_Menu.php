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
                        <h1 class="sidebar-title">⚙️ <?php esc_attr_e( 'CineMax Admin', 'wptheaterly' ); ?></h1>
                    </div>

                    <nav class="nav-menu">
                        <button class="nav-item active" data-tab="wtbm_movies">
                            🎬 <?php esc_attr_e( 'Movies', 'wptheaterly' ); ?>
                        </button>
                        <button class="nav-item" data-tab="wtbm_theaters">
                            🏛️ <?php esc_attr_e( 'Theaters', 'wptheaterly' ); ?>
                        </button>
                        <button class="nav-item" data-tab="wtbm_showtimes">
                            📅 <?php esc_attr_e( 'Showtimes', 'wptheaterly' ); ?>
                        </button>
                        <button class="nav-item" data-tab="wtbm_pricing">
                            💰 <?php esc_attr_e( 'Pricing', 'wptheaterly' ); ?>
                        </button>
                        <button class="nav-item" data-tab="wtbm_bookings" style="display: none">
                            👥 <?php esc_attr_e( 'Bookings', 'wptheaterly' ); ?>
                        </button>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="main-content">
                    <div class="header">
                        <h2><?php esc_attr_e( 'Backend Configuration', 'wptheaterly' ); ?></h2>
                        <p><?php esc_attr_e( 'Manage your cinema booking system settings', 'wptheaterly' ); ?></p>
                    </div>

                    <?php do_action( 'movie_content');?>
                    <!-- Movies Tab -->
                    <div id="wtbm_movies_content" class="tab-content active">
                        <div class="section">
                            <div class="section-header">
                                <h3 class="section-title"><?php esc_attr_e( 'Movies Management', 'wptheaterly' ); ?></h3>
                                <button class="btn btn-primary wtbpShowHideAddForm" id="wtbpAddedMovieForm">
                                    ➕ <?php esc_attr_e( 'Add Movie', 'wptheaterly' ); ?>
                                </button>
                            </div>
                        </div>

                        <div id="add-movie-form" class="form-section" style="display: none">
                            <h4 class="mb-4 font-semibold"><?php esc_attr_e( 'Add New Movie', 'wptheaterly' ); ?></h4>
                            <div id="wtbm_add_edit_movie_form_holder"></div>
                        </div>

                        <!-- Movies Table -->
                        <div class="section">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th><?php esc_attr_e( 'Movie', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Genre', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Duration', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Rating', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Status', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Actions', 'wptheaterly' ); ?></th>
                                </tr>
                                </thead>
                                <tbody id="movies-table-body">
                                    <?php
                                    $movie_data = WTBM_Layout_Functions::get_and_display_movies();
                                    echo WTBM_Layout_Functions::display_movies_data( $movie_data );
                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Theaters Tab -->
                    <div id="wtbm_theaters_content" class="tab-content">
                        <div class="section">
                            <div class="section-header">
                                <h3 class="section-title"><?php esc_attr_e( 'Theater Management', 'wptheaterly' ); ?></h3>
                                <button class="btn btn-primary wtbpShowHideAddForm" id="wtbpTheaterAddForm">
                                    ➕ <?php esc_attr_e( 'Add Theater', 'wptheaterly' ); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Add Theater Form -->
                        <div id="wtbmAddTheaterForm" class="form-section" style="display: none">
                            <!--Here-->
                            <?php
//                                echo WTBM_Layout_Functions::add_edit_theater_html( 'add', '' );
                            ?>
                        </div>

                        <!-- Theaters Table -->
                        <div class="section">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th><?php esc_attr_e( 'Name', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Type', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Capacity', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Sound System', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Status', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Actions', 'wptheaterly' ); ?></th>
                                </tr>
                                </thead>
                                <tbody id="theaters-table-body">
                                    <!--Here Theater Data-->
                                <?php
                                    $theater_data = WTBM_Layout_Functions::get_and_display_theater_date();
//                                    error_log( print_r( [ '$theater_data' => $theater_data ], true ) );
                                    echo WTBM_Layout_Functions::display_theater_date( $theater_data );
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Showtimes Tab -->
                    <div id="wtbm_showtimes_content" class="tab-content">
                        <div class="section">
                            <div class="section-header">
                                <h3 class="section-title"><?php esc_attr_e( 'Showtimes Management', 'wptheaterly' ); ?></h3>
                                <button class="btn btn-primary wtbpShowHideAddForm" id="wtbpShowtimeAddForm">
                                    ➕ <?php esc_attr_e( 'Add Showtime', 'wptheaterly' ); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Add Showtime Form -->
                        <div id="wtbm_add-showtime-form" class="form-section hidden" style="display: none">
                           <?php
                           $action_type = 'add';
//                            echo WTBM_Manage_Showtimes::add_edit_show_time_html();
                           ?>
                        </div>

                        <!-- Showtimes Table -->
                        <div class="section">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th><?php esc_attr_e( 'Movie', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Theater', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Date', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Time', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Price', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Actions', 'wptheaterly' ); ?></th>
                                </tr>
                                </thead>
                                <tbody id="showtimes-table-body">
                                    <?php
                                        $show_time_data = WTBM_Layout_Functions::get_show_time_data();
                                        echo WTBM_Manage_Showtimes::display_show_times_data( $show_time_data );
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pricing Tab -->
                    <div id="wtbm_pricing_content" class="tab-content">
                        <div class="section">
                            <div class="section-header">
                                <h3 class="section-title">Pricing Rules</h3>
                                <button class="btn btn-primary wtbpShowHideAddForm" id="wtbpPricingAddForm">
                                    ➕ Add Pricing Rule
                                </button>
                            </div>
                        </div>

                        <!-- Add Pricing Form -->
                        <div id="wtbm_AddPricingForm" class="form-section hidden" style="display: none"></div>

                        <div class="section">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th><?php esc_attr_e( 'Rule Name', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Time Range', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Multiplier', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Status', 'wptheaterly' ); ?></th>
                                    <th><?php esc_attr_e( 'Actions', 'wptheaterly' ); ?></th>
                                </tr>
                                </thead>
                                <tbody id="pricing-table-body">
                                    <?php
                                        $pricing_rules_date =  WTBM_Layout_Functions::get_pricing_rules_data();
                                        echo WTBM_Pricing_Rules::pricing_rules_data_display( $pricing_rules_date );
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Bookings Tab -->
                    <div id="wtbm_bookings_content" class="tab-content">
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