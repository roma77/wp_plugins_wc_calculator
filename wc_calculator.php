<?php
/*
 * Plugin Name: Calculator
 * Plugin URI: 
 * Description: 
 * Version: 1.1.1
 * Author: Roman Didenko
 * Author URI: 
 * License: GPLv2
 */
 
function calc_include_loadscript() {
	/*
	 * recommend to add additional conditions just to not to load the scipts on each page
	 * like:
	 * if ( !in_array('post-new.php','post.php') ) return;
	 */
	if ( ! did_action( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}
 	wp_enqueue_script( 'load_image_script', plugin_dir_url( __FILE__ ) . 'assets/js/load_image.js', array('jquery'), null, false );
}
add_action( 'admin_enqueue_scripts', 'calc_include_loadscript' );

function calculator_add_scripts() {
	wp_enqueue_script( 'calculator_editor', plugin_dir_url( __FILE__ ) . 'assets/js/calculator_editor.js', array('jquery'), null, true );
}
add_action( 'wp_enqueue_scripts', 'calculator_add_scripts' );

/**
 * Register meta boxes.
 */
function calculator_register_meta_boxes() {
    add_meta_box( 'calculator', 'Калькулятор', 'calculator_display_callback', 'product', 'side', 'low' );
}
add_action( 'add_meta_boxes', 'calculator_register_meta_boxes' );

/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function calculator_display_callback( $post ) {
	?>
	<div class="events_box">
		<p class="meta-options calculator_field">
			<label for="calculator_number1">Число 1</label>
			<input id="calculator_number1" name="calculator_number1" type="number" min="0" step="1" value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'calculator_number1', true ) ); ?>" />
		</p>	
		<p class="meta-options calculator_field">
			<label for="calculator_number2">Число 2</label>
			<input id="calculator_number2" name="calculator_number2" type="number" min="0" step="1" value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'calculator_number2', true ) ); ?>" />
		</p>	
		<p class="meta-options calculator_field">
			<label for="calculator_text">Teкст</label>
			<input id="calculator_text" type="text" name="calculator_text" value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'calculator_text', true ) ); ?>">
		</p>
	</div>
	
	<?php
	
	global $content_width, $_wp_additional_image_sizes;
	$image_id = get_post_meta( $post->ID, '_listing_image_id', true );
	$old_content_width = $content_width;
	$content_width = 254;
	if ( $image_id && get_post( $image_id ) ) {
		if ( ! isset( $_wp_additional_image_sizes['post-thumbnail'] ) ) {
			$thumbnail_html = wp_get_attachment_image( $image_id, array( $content_width, $content_width ) );
		} else {
			$thumbnail_html = wp_get_attachment_image( $image_id, 'post-thumbnail' );
		}
		if ( ! empty( $thumbnail_html ) ) {
			$content = $thumbnail_html;
			$content .= '<p class="hide-if-no-js"><a href="javascript:;" id="remove_listing_image_button" >' . esc_html__( 'Remove image', 'text-domain' ) . '</a></p>';
			$content .= '<input type="hidden" id="upload_listing_image" name="_listing_cover_image" value="' . esc_attr( $image_id ) . '" />';
		}
		$content_width = $old_content_width;
	} else {
		$content = '<img src="' . plugin_dir_url( __FILE__ ) . 'assets/img/img_not_available.png" style="width:' . esc_attr( $content_width ) . 'px;height:auto;border:0;" />';
		$content .= '<p class="hide-if-no-js"><a title="' . esc_attr__( 'Set image', 'text-domain' ) . '" href="javascript:;" id="upload_listing_image_button" id="set-listing-image" data-uploader_title="' . esc_attr__( 'Choose an image', 'text-domain' ) . '" data-uploader_button_text="' . esc_attr__( 'Set image', 'text-domain' ) . '">' . esc_html__( 'Set image', 'text-domain' ) . '</a></p>';
		$content .= '<input type="hidden" id="upload_listing_image" name="_listing_cover_image" value="" />';
	}
	echo $content;
	
}
 

/**
 * Save meta box content.
 *
 * @param int $post_id Post ID
 */
function calculator_save_meta_box( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( $parent_id = wp_is_post_revision( $post_id ) ) {
        $post_id = $parent_id;
    }
    $fields = [
        'calculator_number1',
        'calculator_number2',
		'calculator_text'
    ];
    foreach ( $fields as $field ) {
        if ( array_key_exists( $field, $_POST ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
        }
     }
	 
	if( isset( $_POST['_listing_cover_image'] ) ) {
		$image_id = (int) $_POST['_listing_cover_image'];
		update_post_meta( $post_id, '_listing_image_id', $image_id );
	}	 
}
add_action( 'save_post', 'calculator_save_meta_box' );


add_action( 'woocommerce_single_product_summary', 'calculator_action', 6 );
function calculator_action() { 
	global $product, $content_width, $_wp_additional_image_sizes;
	$product_id = $product->get_id();
	$image_id = get_post_meta( $product_id, '_listing_image_id', true );
	$number_1 = get_post_meta( $product_id, 'calculator_number1', true ) ? get_post_meta( $product_id, 'calculator_number1', true ) : '0';
	$number_2 = get_post_meta( $product_id, 'calculator_number2', true ) ? get_post_meta( $product_id, 'calculator_number2', true ) : '0';
	$product = wc_get_product( $product_id );
	$product_price = $product->get_price() ? $product->get_price() : '0';
	
	$calculator_text = get_post_meta( $product_id, 'calculator_text', true );
	$old_content_width = $content_width;
	$content_width = 254;
	$content = '<div class="wc_calculator"><h5>' . __( 'Калькулятор', 'text-domain' ) . '</h5>';
	if ( $image_id && get_post( $image_id ) ) {
		if ( ! isset( $_wp_additional_image_sizes['post-thumbnail'] ) ) {
			$thumbnail_html = wp_get_attachment_image( $image_id, array( $content_width, $content_width ) );
		} else {
			$thumbnail_html = wp_get_attachment_image( $image_id, 'medium' );
		}
		if ( ! empty( $thumbnail_html ) ) {
			$content .= $thumbnail_html;
		}
		$content_width = $old_content_width;
	}
	
	if ( ! empty( get_post_meta($product_id, 'calculator_text', 1) ) ) {
		$content .= '<p>' . get_post_meta($product_id, 'calculator_text', 1) . '</p>';
	}
	$content .= '<input class="input_calc" data-number-1="' . $number_1 . '" data-number-2="' . $number_2 . '" data-price="' . $product_price . '" type="number" min="0" step="1" name="calculate1" value="" style="" />';
	$content .= '<p class="result_1"></p>';
	$content .= '<button type="submit" name="calculate_1" class="js_calculator js_calculator_1">Рассчитать 1</button></br></br>';
	$content .= '<input class="input_calc" data-text="' . $calculator_text . '" type="number" min="0" step="1" name="calculate2" value="" style="" />';
	$content .= '<p class="result_2"></p>'; 
	$content .= '<button type="submit" name="calculate_2" class="js_calculator js_calculator_2">Рассчитать 2</button>';
	$content .= '</div>';
	
	echo $content; 

}

// Add Shortcode
function calculator_shortcode( $atts ) {

	// Attributes
	$atts = shortcode_atts(
		array(
			'product_id' => '0',
		),
		$atts
	);
	
	$post_type = get_post_type( $atts[ 'product_id' ] );


	if ( $atts[ 'product_id' ] == '0' || $post_type != 'product' ) {
		
		$content = '<div>Введите атрибут шорткода - product_id [calculator product_id="..."]</div>';
		
	} else {
	
		global $content_width, $_wp_additional_image_sizes;
		$product_id = $atts[ 'product_id' ];
		$image_id = get_post_meta( $product_id, '_listing_image_id', true );
		$number_1 = get_post_meta( $product_id, 'calculator_number1', true ) ? get_post_meta( $product_id, 'calculator_number1', true ) : '0';
		$number_2 = get_post_meta( $product_id, 'calculator_number2', true ) ? get_post_meta( $product_id, 'calculator_number2', true ) : '0';
		$product = wc_get_product( $product_id );
		$product_price = $product->get_price() ? $product->get_price() : '0';
		
		$calculator_text = get_post_meta( $product_id, 'calculator_text', true );
		$old_content_width = $content_width;
		$content_width = 254;
		$content = '<div class="wc_calculator"><h5>' . __( 'Калькулятор', 'text-domain' ) . '</h5>';
		if ( $image_id && get_post( $image_id ) ) {
			if ( ! isset( $_wp_additional_image_sizes['post-thumbnail'] ) ) {
				$thumbnail_html = wp_get_attachment_image( $image_id, array( $content_width, $content_width ) );
			} else {
				$thumbnail_html = wp_get_attachment_image( $image_id, 'medium' );
			}
			if ( ! empty( $thumbnail_html ) ) {
				$content .= $thumbnail_html;
			}
			$content_width = $old_content_width;
		}
		
		if ( ! empty( get_post_meta($product_id, 'calculator_text', 1) ) ) {
			$content .= '<p>' . get_post_meta($product_id, 'calculator_text', 1) . '</p>';
		}
		$content .= '<input class="input_calc" data-number-1="' . $number_1 . '" data-number-2="' . $number_2 . '" data-price="' . $product_price . '" type="number" min="0" step="1" name="calculate1" value="" style="" />';
		$content .= '<p class="result_1"></p>';
		$content .= '<button type="submit" name="calculate_1" class="js_calculator js_calculator_1">Рассчитать 1</button></br></br>';
		$content .= '<input class="input_calc" data-text="' . $calculator_text . '" type="number" min="0" step="1" name="calculate2" value="" style="" />';
		$content .= '<p class="result_2"></p>'; 
		$content .= '<button type="submit" name="calculate_2" class="js_calculator js_calculator_2">Рассчитать 2</button>';	
		$content .= '</div>';
	}
	
	return $content;

}
add_shortcode( 'calculator', 'calculator_shortcode' );
