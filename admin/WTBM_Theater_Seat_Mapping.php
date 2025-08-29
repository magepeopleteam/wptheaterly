<?php
/*
   * @Author 		rubelcuet10@gmail.com
   * Copyright: 	mage-people.com
   */
if (!defined('ABSPATH')) {
    die;
}
if( !class_exists( 'WTBM_Theater_Seat_Mapping ') ) {
    class WTBM_Theater_Seat_Mapping
    {
        public function __construct(){
        }

        public static function render_seat_mapping_meta_box( $post_id, $action_type, $rows = 20, $seatsPerRow = 30 ) {

            error_log( print_r( [ '$post_id' => $post_id ], true ) );

            $template_id = '';
            $selection = WTBM_Plan_ASSETS . 'images/tools/selection.png';
            $choice = WTBM_Plan_ASSETS . 'images/tools/choice.png';
            $add_seats = WTBM_Plan_ASSETS . 'images/tools/add-seats.png';
            $shapes = WTBM_Plan_ASSETS . 'images/tools/shapes.png';
            $text = WTBM_Plan_ASSETS . 'images/tools/text.png';
            $eraser = WTBM_Plan_ASSETS . 'images/tools/eraser.png';
            $undo = WTBM_Plan_ASSETS . 'images/tools/undo.png';
            $paste = WTBM_Plan_ASSETS . 'images/tools/paste.png';
            $group = WTBM_Plan_ASSETS . 'images/tools/group.png';
            $reset = WTBM_Plan_ASSETS . 'images/tools/reset.png';
            $import = WTBM_Plan_ASSETS . 'images/tools/import.png';
            $save_plan = WTBM_Plan_ASSETS . 'images/tools/save.png';
            $save_template = WTBM_Plan_ASSETS . 'images/tools/save_template.png';
            // Output the meta box content

            $theater_categories = WTBM_Manage_Showtimes::get_theater_categories( $post_id );
//            error_log( print_r( [ '$theater_categories' => $theater_categories ], true ) );
            ob_start();
            ?>
            <div class="mptrs_mapping_controls" id="<?php echo esc_attr( $post_id ); ?>">
                <input type="hidden" id="mptrs_mapping_plan_id" name="mptrs_mapping_plan_id" value="<?php echo esc_attr( $post_id ); ?>">
                <div class="mptrs_mapping_planControlHolder">
                    <button class="mptrs_mapping_multiselect tooltips" id="mptrs_mapping_multiselect" data-tooltip="<?php esc_html_e('Multi Select', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($selection); ?>" alt=""></button>
                    <button class="mptrs_mapping_singleSelect tooltips" id="mptrs_mapping_singleSelect" data-tooltip="<?php esc_html_e('Single Select', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($choice); ?>" alt=""></button>
                    <button class="mptrs_mapping_set_seat enable_set_seat tooltips" id="mptrs_mapping_set_seat"  data-tooltip="<?php esc_html_e('Add Seat', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($add_seats); ?>" alt=""></button>
                    <button class="mptrs_mapping_set_shape tooltips" id="mptrs_mapping_set_shape" data-tooltip="<?php esc_html_e('Add Shape', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($shapes); ?>" alt=""></button>
                    <button class="mptrs_mapping_setText tooltips" id="mptrs_mapping_setText" data-tooltip="<?php esc_html_e('Set Text', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($text); ?>" alt=""></button>
<!--                    <button class="mptrs_importFromTemplate tooltips" id="mptrs_importFromTemplate"  data-tooltip="--><?php //esc_html_e('Import From Template', 'wptheaterly'); ?><!--"><img class="mptrs_action_img" src="--><?php //echo esc_attr($import); ?><!--" alt=""></button>-->
                    <button class="mptrs_removeSelected tooltips" id="mptrs_removeSelected"  data-tooltip="<?php esc_html_e('Erase', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($eraser); ?>" alt=""></button>
                    <button class="mptrs_undo tooltips" id="mptrs_undo" data-tooltip="<?php esc_html_e('Undo', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($undo); ?>" alt=""></button>
                    <button class="mptrs_copyPaste tooltips" id="mptrs_copyPaste" data-tooltip="<?php esc_html_e('Paste', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($paste); ?>" alt=""></button>
                    <button class="mptrs_bindTableWidthChair tooltips" id="mptrs_bindTableWidthChair" data-tooltip="<?php esc_html_e('Group Table', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($group); ?>" alt=""></button>
                    <button class="tooltips" id="mptrs_clearAllPlan" data-tooltip="<?php esc_html_e('Clear All', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($reset); ?>" alt=""></button>
                   <?php if( $action_type === 'add' ){?>
                        <button class="mptrs_savePlan tooltips" data-theater-id="<?php echo esc_attr( $post_id );?>" id="wtbm_saveSeatPlan" data-tooltip="<?php esc_html_e('Save Plan', 'wptheaterly'); ?>"><img class="mptrs_action_img" src="<?php echo esc_attr($save_plan); ?>" alt=""></button>
                    <?php }?>
<!--                    <button class="mptrs_savePlan tooltips" id="mptrs_savePlanAsTemplate" data-tooltip="--><?php //esc_html_e('Save Plan with Template', 'wptheaterly'); ?><!--"><img class="mptrs_action_img" src="--><?php //echo esc_attr($save_template); ?><!--" alt=""></button>-->
                </div>
            </div>

            <div class="mptrs_seatContentHolder" id="mptrs_seatContentHolder">
                <div id="mptrs_popupContainer" class="mptrs_popup">
                    <div class="mptrs_popupContent">
                        <span id="mptrs_closePopup" class="mptrs_close">&times;</span>
                        <div id="mptrs_popupInnerContent"></div>
                    </div>
                </div>
                <div class="mptrs_seatPlanHolder">
            <?php
            $dynamic_shape_texts = array(
                'rectangle'    => array( 'Rectangle', 'rectangle' ),
                'circle'        => array( 'Circle', 'circle' ),
                'square'        => array( 'Square', 'square' ),
                'pentagon'      => array( 'Pentagon', 'pentagon' ),
                'hexagon'      => array( 'Hexagon', 'hexagon' ),
                'rhombus'      => array( 'Rhombus', 'rhombus' ),
                'parallelogram' => array( 'Parallelogram', 'parallelogram' ),
                'trapezoid'     => array( 'Trapezoid', 'trapezoid' ),
                'oval'          => array( 'Oval', 'oval' ),
            );

            $shapeText = '<span class="mptrs_setShapeTitle">' . esc_html__('Select Shape', 'wptheaterly') . '</span>';

            $category_html = '';
            if( is_array( $theater_categories ) && !empty( $theater_categories ) ) {
                foreach ($theater_categories as $key => $category) {
                    $category_html .= '
                                <div class="wtbm_CategoryHolder"
                                data-category-id="' . $key . '"
                                data-category-seats="' . $category['seats'] . '"
                                data-category-price="' . $category['price'] . '"
                                data-category-name="' . $category['category_name'] . '"
                                >
                                    <div class="wtbm_Categoryname">' . $category['category_name'] . '</div>
                                </div>
                                ';
                }
            }

            foreach ( $dynamic_shape_texts as $key => $val ) {
                $select_class = ( $key === 'rectangle' ) ? 'shapeTextSelected' : '';
                $src = esc_url( WTBM_Plan_ASSETS . 'images/icons/shape_icons/' . $val[1] . '.jpg' );
                $shapeText .= '<div class="mptrs_shapeText ' . esc_attr( $select_class ) . '" id="' . esc_attr( $key ) . '"><img class="shapeIcon" src="' . $src . '" /></div>';
            }

            $seat_mapping_info = get_option( 'mptrs_seat_mapping_info' );
            $box_size = isset($seat_mapping_info[ 'mptrs_box_size' ]) ? $seat_mapping_info[ 'mptrs_box_size' ] : 30;
            $rows = isset($seat_mapping_info[ 'mptrs_num_of_rows' ]) ? $seat_mapping_info[ 'mptrs_num_of_rows' ] : $rows;
            $columns = isset($seat_mapping_info[ 'mptrs_num_of_columns' ]) ? $seat_mapping_info[ 'mptrs_num_of_columns' ] : $seatsPerRow;


            $gap = isset( $get_create_box_data['boxGap'] ) ? absint( $get_create_box_data['boxGap'] ) : 10;

            $childWidth = $box_size;
            $childHeight = $box_size + 5;

            $seats = [];
            for ( $row = 0; $row < $rows; $row++ ) {
                for ( $col = 0; $col < $columns; $col++ ) {
                    $seats[] = array( 'col' => $row, 'row' => $col );
                }
            }

            $parent_width = $columns * ( $childWidth + $gap ) - $gap;
            $parent_height = $rows * ( $childHeight + $gap ) - $gap;

            echo '<div class="mptrs_parentDiv" id="mptrs_parentDiv" style="position: absolute; width: ' . esc_attr( $parent_width ) . 'px; height: ' . esc_attr( $parent_height ) . 'px;">';

            $templates = $template_id ? array_map( 'absint', explode( '_', $template_id ) ) : array( $post_id );

            foreach ( $templates as $template ) {
                $plan_data = get_post_meta( $template, 'wtbp_theater_seat_map', true );

                $plan_seats = isset( $plan_data['seat_data'] ) ? $plan_data['seat_data'] : array();
                $plan_seat_texts = isset( $plan_data['seat_text_data'] ) ? $plan_data['seat_text_data'] : array();
                $dynamic_shapes = isset( $plan_data['dynamic_shapes'] ) ? $plan_data['dynamic_shapes'] : '';

                if ( is_array( $dynamic_shapes ) && count( $dynamic_shapes ) > 0 ) {
                    foreach ( $dynamic_shapes as $dynamic_shape ) {
                        $tableBindID = isset( $dynamic_shape['tableBindID'] ) ? $dynamic_shape['tableBindID'] : '';
                        $shape_rotate_deg = isset( $dynamic_shape['shapeRotateDeg'] ) ? $dynamic_shape['shapeRotateDeg'] : 0;
                        if ( isset( $dynamic_shape['backgroundImage'] ) && $dynamic_shape['backgroundImage'] !== '' ) {
                            $table_background_img_url = esc_url( WTBM_Plan_ASSETS . 'images/icons/tableIcon/' . $dynamic_shape['backgroundImage'] . '.png' );
                        }else{
                            $table_background_img_url = '';
                        }
                        echo '<div id="'. esc_attr( $tableBindID ) .'" class="mptrs_dynamicShape ui-resizable ui-draggable ui-draggable-handle" data-shape-rotate="' . esc_attr( $shape_rotate_deg ) . '" style=" 
                                                left: ' . esc_attr( $dynamic_shape['textLeft'] ) . 'px; 
                                                top: ' . esc_attr( $dynamic_shape['textTop'] ) . 'px; 
                                                width: ' . esc_attr( $dynamic_shape['width'] ) . 'px;
                                                height: ' . esc_attr( $dynamic_shape['height'] ) . 'px;
                                                background-color: ' . esc_attr( $dynamic_shape['backgroundColor'] ) . '; 
                                                border-radius: ' . esc_attr( $dynamic_shape['borderRadius'] ) . ';
                                                clip-path: ' . esc_attr( $dynamic_shape['clipPath'] ) . ';
                                                transform: rotate(' . esc_attr( $shape_rotate_deg ) . 'deg);
                                                background-image:url(' . esc_url( $table_background_img_url ) . ');
                                                ">
                                            </div>';
                    }
                }

                if ( is_array( $plan_seat_texts ) && count( $plan_seat_texts ) > 0 ) {
                    foreach ( $plan_seat_texts as $plan_seat_text ) {
                        $text_rotate_deg = isset( $plan_seat_text['textRotateDeg'] ) ?  $plan_seat_text['textRotateDeg'] : 0;
                        echo '<div class="mptrs_text-wrapper" data-text-degree="' . esc_attr( $text_rotate_deg ) . '"
                                            style="
                                            position: absolute; 
                                            left: ' . esc_attr( $plan_seat_text['textLeft'] ) . 'px; 
                                            top: ' . esc_attr( $plan_seat_text['textTop'] ) . 'px; 
                                            transform: rotate(' . esc_attr( $text_rotate_deg ) . 'deg);">
                                            <span class="mptrs_dynamic-text" 
                                                style="
                                                    display: block; 
                                                    color: ' . esc_attr( $plan_seat_text['color'] ) . '; 
                                                    font-size: ' . esc_attr( $plan_seat_text['fontSize'] ) . ';
                                                    cursor: pointer;">
                                                ' . esc_html( $plan_seat_text['text'] ) . '
                                            </span>
                                        </div>';
                    }
                }
                foreach ( $seats as $seat ) {
                    $isSelected = false;
                    $row = $seat['row'];
                    $col = $seat['col'];
                    $left = $row * ( $childWidth + $gap ) + 10;
                    $top = $col * ( $childHeight + $gap ) + 10;
                    $seat_number = $col * $columns + $row;
                    $seat_num = '';
                    $seat_price = 0;
                    $background_color = '';
                    $zindex = 'auto';
                    $to = $top;
                    $le = $left;
                    $width = $childWidth;
                    $height = $childHeight;
                    $degree = 0;
                    $background_img_url = '';
                    $seat_icon_name = '';
                    $tableBind = '';

                    if ( is_array( $plan_seats ) && count( $plan_seats ) > 0 ) {
                        foreach ( $plan_seats as $plan_seat ) {
                            if ( $plan_seat['col'] == $row && $plan_seat['row'] == $col ) {

                                $isSelected = true;
                                $background_color = sanitize_text_field( $plan_seat['color'] ) ;
                                $seat_num = isset( $plan_seat['seat_number'] ) ? sanitize_text_field( $plan_seat['seat_number'] ) : '';
                                $tableBind = isset( $plan_seat['data_tableBind'] ) ? sanitize_text_field( $plan_seat['data_tableBind'] ) : '';
                                $seat_price = floatval( $plan_seat['price'] );
                                $width = isset( $plan_seat['width'] ) ? absint( $plan_seat['width'] ) : '' ;
                                $height = isset( $plan_seat['height'] ) ? absint( $plan_seat['height'] ) : '' ;
                                $zindex = isset( $plan_seat['z_index'] ) ? absint( $plan_seat['z_index'] ) : '' ;
                                $to = isset( $plan_seat['top'] ) ? absint( $plan_seat['top'] ) : '' ;
                                $le = isset( $plan_seat['left'] ) ? absint( $plan_seat['left'] ) : '' ;
                                $degree = isset( $plan_seat['data_degree'] ) ? absint( $plan_seat['data_degree'] ) : '';
                                if ( isset( $plan_seat['backgroundImage'] ) && $plan_seat['backgroundImage'] !== '' ) {
                                    $seat_icon_name = sanitize_file_name( $plan_seat['backgroundImage'] );
                                    $background_img_url = esc_url( WTBM_Plan_ASSETS . 'images/icons/seatIcons/' . $plan_seat['backgroundImage'] . '.png' );
                                }
                                break;
                            }
                        }
                    }

                    $class = $isSelected ? ' save ' : '';
                    $color = $isSelected ? $background_color : '';
                    $seat_number = $isSelected ? $seat_num : '';
                    $wi = $isSelected ? $width : $childWidth;
                    $hi = $isSelected ? $height : $childHeight;
                    $zindex = $isSelected ? $zindex : 'auto';
                    $top = $isSelected ? $to : $top;
                    $left = $isSelected ? $le : $left;

                    $hover_price = $seat_price === 0 ? '' : 'Price: ' . esc_attr( $seat_price );
                    $block = $seat_num ? 'block' : 'none';

                    echo '<div class=" mptrs_mappingSeat ' . esc_attr( $class ) . '"
                                        id = "div' . esc_attr( $col ) . '_' . esc_attr( $row ) . '"
                                        data-id="' . esc_attr( $col ) . '_' . esc_attr( $row ) . '" 
                                        data-row="' . esc_attr( $col ) . '" 
                                        data-col="' . esc_attr( $row ) . '" 
                                        data-seat-num=" ' . esc_attr( $seat_num ) . ' " 
                                        data-price=" ' . esc_attr( $seat_price ) . ' " 
                                        data-degree="' . esc_attr( $degree ) . '"
                                        data-tableBind="' . esc_attr( $tableBind ) . '"
                                        data-background-image="' . esc_attr( $seat_icon_name ) . '"
                                        style="
                                        left: ' . esc_attr( $left ) . 'px; 
                                        top: ' . esc_attr( $top ) . 'px;
                                        width: ' . esc_attr( $wi ) . 'px;
                                        height: ' . esc_attr( $hi ) . 'px;
                                        background-color: ' . esc_attr( $color ) . ';
                                        background-image:url(' . esc_url( $background_img_url ) . ');
                                        z-index: ' . esc_attr( $zindex ) . ';
                                        transform: rotate(' . esc_attr( $degree ) . 'deg);
                                        ">
                                        
                                        <div class="mptrs_showPriceHover" style="display: none;z-index: 999">' . esc_attr( $hover_price ) . '</div>
                                        <div class="mptrs_seatNumber" id="mptrs_seatNumber' . esc_attr( $col ) . '_' . esc_attr( $row ) . '" style="display: ' . esc_attr( $block ) . ';">' . esc_html( $seat_num ) . '</div>
                                    </div>';
                }
            }

            $seat_icons_dir = WTBM_Plan_PATH . '/assets/images/icons/seatIcons';
            $images = array_diff( scandir( $seat_icons_dir ), array( '.', '..' ) );
            $image_files = array_filter( $images, function( $file ) use ( $seat_icons_dir ) {
                $file_path = $seat_icons_dir . '/' . $file;
                $allowed_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );
                $extension = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
                return in_array( $extension, $allowed_extensions );
            } );
            $image_files = array_values( $image_files );

            $icon_images = '<div class="mptrs_seatIconHolder" id="mptrs_seatIconHolder">';
            foreach ( $image_files as $seat_icon ) {
                if ( $seat_icon === 'uploadIcon.png' || $seat_icon === 'remove.png' ) {
                    continue;
                }
                if ( $seat_icon ) {
                    $split_image = explode( '.', $seat_icon );
                    $icon_images .= '<img class="mptrs_seatIcon" id="' . esc_attr( $split_image[0] ) . '" src="' . esc_url( WTBM_Plan_ASSETS . 'images/icons/seatIcons/' . $seat_icon ) . '"/>';
                }
            }
            $icon_images .= '<img alt="No" class="mptrs_seatIcon" id="mptrs_seatnull" src="' . esc_url( WTBM_Plan_ASSETS . 'images/icons/seatIcons/remove.png' ) . '"/>
                                             <div class="seat-icon-upload-container" style="display: block">
                                                     <label for="mptrs_seatIconUpload" class="mptrs_seatIconUploadLabel">
                                                        <img src="' . esc_url( WTBM_Plan_ASSETS . 'images/icons/seatIcons/uploadIcon.png' ) . '" alt="Upload Icon" class="mptrs_seatIconImageUpload">
                                                     </label>
                                                    <input class="mptrs_seatIconUpload" type="file" id="mptrs_seatIconUpload" name="filename">
                                                 </div>  
                                             </div>';
            echo '</div> 
                                </div>
                                <div class="mptrs_seatActionControl">
                                    <div class="mptrs_dynamicShapeHolder" id="mptrs_dynamicShapeHolder">
                                        ' . wp_kses_post( $shapeText ) . '
                                    </div>
                                    <div class="mptrs_dynamicShapeColorHolder" style="display: none">
                                        <div class="mptrs_dynamicShapeControl">
                                            <div class="mptrs_dynamicShapeControlText">Shape Setting</div>
                                            <div class="mptrs_colorRemoveHolder">
                                                <div class="mptrs_shapeRotationHolder">
                                                    <img class="mptrs_shapeRotate" id="mptrs_shapeRotateRight" src="' . esc_url( WTBM_Plan_ASSETS . 'images/icons/rotate/rotate_right.webp' ) . '"/>
                                                    <img class="mptrs_shapeRotate" id="mptrs_shapeRotateLeft" src="' . esc_url( WTBM_Plan_ASSETS . 'images/icons/rotate/rotate_left.webp' ) . '"/>
                                                </div>
                                                <input type="color" id="mptrs_setShapeColor" value="#3498db">
                                                <button class="mptrs_removeDynamicShape" id="mptrs_removeDynamicShape">X</button>
                                            </div>
                                            <div class="mptrs_shapeDisplayIconHolder">
                                                <div class="mptrs_shapeIconTitleTextHolder"><span class="mptrs_shapeIconTitleText">Add Shape</span></div>
                                                    <div class="mptrs_shapeDisplayIcons">
                                                        <img class="mptrs_shapeDisplayIcon" id="table1" src="' . esc_url( WTBM_Plan_ASSETS . 'images/icons/tableIcon/table1.png' ) . '"/>
                                                        <img class="mptrs_shapeDisplayIcon" id="table2" src="' . esc_url( WTBM_Plan_ASSETS . 'images/icons/tableIcon/table2.png' ) . '"/>
                                                        <img class="mptrs_shapeDisplayIcon" id="table3" src="'. esc_url( WTBM_Plan_ASSETS.'images/icons/tableIcon/table3.png' ) .'"/>
                                                        <img class="mptrs_shapeDisplayIcon" id="table4" src="'. esc_url( WTBM_Plan_ASSETS.'images/icons/tableIcon/table4.png' ) .'"/>
                                                        <img class="mptrs_shapeDisplayIcon" id="dining2" src="'. esc_url( WTBM_Plan_ASSETS.'images/icons/tableIcon/dining2.png' ) .'"/>
                                                        <img class="mptrs_shapeDisplayIcon" id="dining1" src="'. esc_url( WTBM_Plan_ASSETS.'images/icons/tableIcon/dining1.png' ) .'"/>
                                                    </div>
                                                </div>
                                                <div class="mptrs_copyHolder">
                                                    <button class="mptrs_shapeCopyStore">Copy</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mptrs_dynamicTextControlHolder" style="display: none">
                                            <div class="mptrs_dynamicTextControlText">Text Setting</div>
                                            <div class="mptrs_dynamicTextControlContainer">
                                                <div class="mptrs_textControl">
                                                    <button class="mptrs_textZoomIn">+</button>
                                                    <button class="mptrs_textZoomOut">-</button>
                                                    <button class="mptrs_removeText">X</button>
                                                    <input type="color" id="mptrs_setTextColor" value="#3498db">
                                                </div>
                                                <div class="mptrs_textRotationHolder">
    
                                                    <img class="mptrs_textRotate" id="mptrs_textRotateRight" src="'.esc_url( WTBM_Plan_ASSETS.'images/icons/rotate/rotate_right.webp').'"/>
                                                    <img class="mptrs_textRotate" id="mptrs_textRotateLeft" src="'.esc_url(WTBM_Plan_ASSETS.'images/icons/rotate/rotate_left.webp').'"/>
                                                </div>
                                            </div>
                                            <div class="mptrs_copyHolder">
                                                <button class="mptrs_textCopy">Copy</button>
                                            </div>
                                        </div>
                                        <div class="mptrs_setPriceColorHolder" id="mptrs_setPriceColorHolder" style="display: none">
                                            <div class="mptrs_copyHolder">
                                                <button class="mptrs_seatCopyStore">Copy</button>
                                            </div>
                                            <div class="mptrs_rotateControls">
                                                <select class="mptrs_rotationHandle" name="mptrs_rotationHandle" id="mptrs_rotationHandle" style="display: none">
                                                    <option class="mptrs_rotateOptions" selected value="top-to-bottom">Rotate top to bottom</option>
                                                    <option class="mptrs_rotateOptions"  value="bottom-to-top">Rotate bottom to Top</option>
                                                    <option class="mptrs_rotateOptions"  value="right-to-left">Rotate right to Left</option>
                                                    <option class="mptrs_rotateOptions"  value="left-to-right">Rotate left to Right</option>
                                                </select>
                                                <div class="mptrs_seatRotateIconTextHolder">
                                                    <span class="mptrs_seatRotateIconText">Seat Rotate In Degree</span>
                                                    <div class="mptrs_seatRotateIconImgHolder"> 
                                                        <div class="mptrs_seatRotateIconHolder">
                                                            <img class="mptrs_shapeRotate" id="mptrs_rotateRight" src="'.esc_url(WTBM_Plan_ASSETS.'images/icons/rotate/rotate_right.webp').'"/>
                                                            <img class="mptrs_shapeRotate" id="mptrs_rotateLeft" src="'.esc_url(WTBM_Plan_ASSETS.'images/icons/rotate/rotate_left.webp').'"/>
                                                        </div>
                                                        <input class="mptrs_seatRotateDegree" type="number" name="mptrs_rotationAngle" id="mptrs_rotationAngle" value="10" placeholder="10 degree">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mptrs_seatIconContainer" style="display: none">
                                                <span class="mptrs_seatIconTitle">Select seat icon</span>
                                                '.wp_kses_post( $icon_images ).'
                                            </div>
                                            <div class="mptrs_movementHolder" id="mptrs_movementHolder">
                                                 <div class="mptrs_movementControl">
                                                    <span class="mptrs_movementText">Movement In Px</span>
                                                    <input class="mptrs_movementInPx" id="mptrs_movementInPx" name="mptrs_movementInPx" type="number" value="15" placeholder="movement in px" style="display: none">
                                                </div>
                                                <div class="mptrs_movementControl">
                                                    <div id="mptrs_leftMovement" class="mptrs_movementPlan"><i class="arrowIcon far fa-arrow-alt-circle-left"></i></div>
                                                    <div id="mptrs_topMovement" class="mptrs_movementPlan"><i class="arrowIcon far fa-arrow-alt-circle-up"></i></div>
                                                    <div id="mptrs_bottomMovement" class="mptrs_movementPlan"><i class="arrowIcon far fa-arrow-alt-circle-down"></i></div>
                                                    <div id="mptrs_rightMovement" class="mptrs_movementPlan"><i class="arrowIcon far fa-arrow-alt-circle-right"></i></div>
                                                </div>
                                            </div>
                                            <div class="mptrs_colorPriceHolder">
                                                <div>
                                                    <span>Select Color</span>:
                                                    <input type="color" id="mptrs_setSeatColor" value="#3498db">
                                                </div>
                                                <button id="mptrs_applyColorChanges">Set Color</button>
                                            </div>
                                            <div class="mptrs_colorPriceHolder">
                                                <div class="mptrs_textPriceHolder">
                                                    <span class="mptrs_priceText"> Set Price:</span>
                                                    <input type="number" id="mptrs_setSeatPrice" placeholder="Enter price">
                                                </div>
                                                <button id="mptrs_applyPriceChanges">Set Price</button>
                                            </div>
                                            <div class="mptrs_setSeatNumber"  style="display: block">
                                                 <div class="mptrs_seatNumberContainer">
                                                    <input type="text" id="mptrs_seatNumberPrefix" placeholder="Set Prefix">
                                                    <input type="number" id="mptrs_seatNumberCount" placeholder="1" value="0">
                                                 </div>
                                                <button class="mptrs_setSeatNumber" id="mptrs_setSeatNumber">Set Seat Number</button>
                                            </div>
                                            <div class="wtbm_setSeatCategoryContainer">
                                                 '.$category_html.'
                                                <button class="wtbm_addSeatCategory" id="wtbm_addSeatCategory">Set Category</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
            return ob_get_clean();
        }

    }

    new WTBM_Theater_Seat_Mapping();
}
