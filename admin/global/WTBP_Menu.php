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

            <div class="container mptrs-admin">
                <div class="sidebar">
                    <div class="sidebar-header">
                        <h1 class="sidebar-title"><i class="mi mi-settings"></i> <?php esc_attr_e( 'Theaterly', 'wptheaterly' ); ?></h1>
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
                        <button class="nav-item" data-tab="wtbm_bookings">
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
                                    WTBM_Layout_Functions::display_movies_data( $movie_data );
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
                                <h3 class="section-title"><?php esc_attr_e( 'Pricing Rules', 'wptheaterly' ); ?></h3>
                                <button class="btn btn-primary wtbpShowHideAddForm" id="wtbpPricingAddForm">
                                    ➕ <?php esc_attr_e( 'Add Pricing Rule', 'wptheaterly' ); ?>
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
                    <?php
                        do_action( 'wtbm_bookings_content', 'Booking Management' );
                    ?>

                </div>
            </div>

       <?php }

    }

    new WTBP_Menu();
}