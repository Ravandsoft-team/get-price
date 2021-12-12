<?php
/**
 * Get Price
 *
 * Plugin Name: Get Price
 * Plugin URI:  https://ravandsoft.com
 * Description: Give me a link and i will find price that (don't forgot that link should belong to a product) ☺😉
 * Version:     1.0
 * Author:      S Morteza Yasrebi
 * Author URI:  https://ravandsoft.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

function smGetPrice(){
	wp_enqueue_style('sm-bootstrap', plugins_url('bootstrap.rtl.min.css' , __FILE__), false, false);
	wp_enqueue_script('sm-bootstrap', plugins_url('bootstrap.bundle.min.js' ,__FILE__), array('jquery'), true);
	
	if(isset($_POST['product-address']) && $_POST['product-address']){
		$url = $_POST['product-address'];
		$dom = new Dom;
		$dom->loadFromUrl('https://panel.eways.co/store/detail/4285/68532');
		$tag_price = $dom->find('.goods-price .price');
		$html = $tag_price->innerHtml;
		$price = str_replace(',', '', $html);
		print_r($price);
	}

	?>
	<form class="col-sm-5" action="" method="post">
		<div class="mb-3">
			<label for="exampleFormControlInput1" class="form-label fs-6">آدرس محصول مورد نظر :</label>
			<input type="text" class="form-control" id="exampleFormControlInput1" placeholder="وارد کنید" name="product-address">
		</div>
		<div class="mb-3">
			<label for="exampleFormControlInput2" class="form-label fs-6"></label>
			<input type="number" class="form-control" id="exampleFormControlInput2" placeholder="0%" name="product-percent">
		</div>
		<div class="mb-3">
			<button type="submit" class="btn btn-success">دریافت قیمت</button>
		</div>
	</form>
	<?php
}
add_shortcode('sm-get-price', 'smGetPrice');


// 1. Add custom field input @ Product Data > Variations > Single Variation
function bbloomer_add_custom_field_to_variations( $loop, $variation_data, $variation ) {
	woocommerce_wp_text_input(
		array(
			'id' => 'target-product[' . $loop . ']',
			'class' => 'short',
			'label' => 'آدرس محصول مورد نظر :',
			'value' => get_post_meta($variation->ID, 'target-product-address', true)
		)
	);
	woocommerce_wp_text_input(
		array(
			'id' => 'interest-rates[' . $loop . ']',
			'class' => 'short',
			'type' => 'number',
			'label' => 'درصد سود شما :',
			'value' => get_post_meta($variation->ID, 'interest-rates', true)
		)
	);
}
add_action( 'woocommerce_variation_options_pricing', 'bbloomer_add_custom_field_to_variations', 10, 3 );
 

// 2. Save custom field on product variation save 
function bbloomer_save_custom_field_variations( $variation_id, $i ) {
	$custom_field = $_POST['custom_field'][$i];
	if ( isset( $custom_field ) ) update_post_meta( $variation_id, '_sku', esc_attr( $custom_field ) );
}
add_action( 'woocommerce_save_product_variation', 'bbloomer_save_custom_field_variations', 10, 2 );
 
// 3. Store custom field value into variation data
function bbloomer_add_custom_field_variation_data( $variations ) {
	$variations['custom_field'] = '<div class="woocommerce_custom_field">Custom Field: <span>' . get_post_meta( $variations[ 'variation_id' ], 'custom_field', true ) . '</span></div>';
	return $variations;
}
add_filter( 'woocommerce_available_variation', 'bbloomer_add_custom_field_variation_data' );