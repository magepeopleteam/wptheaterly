<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WTBM_Sales_Report' ) ) {
    class WTBM_Sales_Report{
        public function __construct(){
            add_action('wtbm_sales_report', [ $this, 'sales_report_display' ]);

        }

        public function sales_report_display(){ ?>

            <div id="wtbm_sales_report_content" class="tab-content">
                <div class="section">
                    <!--<div class="wtbm_section_header">
                        <h2 class="section-title"><?php /*esc_attr_e( 'Sales Report', 'wptheaterly' ); */?></h2>
                        <p class="">View sales analytics and generate detailed reports</p>
                    </div>-->
                    <div class="section-header">
                        <h2><?php esc_attr_e( 'Sales Report', 'wptheaterly' ); ?></h2>
                        <!-- <p><?php esc_attr_e( 'View sales analytics and generate detailed reports', 'wptheaterly' ); ?></p> -->
                    </div>
                </div>
            </div>


        <?php }

    }

    new WTBM_Sales_Report();
}