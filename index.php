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

add_action('rest_api_init', 
    function () {
        register_rest_route('smprice', '/get', array(
            'methods' => 'GET',
            'callback' => 'smGetPrice',
			'permission_callback' => '__return_true'
        ) );
    }
);

// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

function smGetPrice(){
	// wp_enqueue_style('sm-bootstrap', plugins_url('bootstrap.rtl.min.css' , __FILE__), false, false);
	// wp_enqueue_script('sm-bootstrap', plugins_url('bootstrap.bundle.min.js' ,__FILE__), array('jquery'), true);
	$start = microtime(true);
	global $wpdb;
	$post_ids = $wpdb->get_results("SELECT `ID` FROM {$wpdb->posts} WHERE `post_type` IN ('product','product_variation')");
	// return $post_ids;
	$post_get = [];
	foreach ($post_ids as $post_id) {
		$url = get_post_meta($post_id->ID,'target-url', true);
		$percent = get_post_meta($post_id->ID,'interest-rates', true);
		$updater = get_post_meta($post_id->ID,'price-updater', true);
		
		if(empty($updater) || !$updater || count($updater) != 3 )$updater = 'no';
	
		if($url && $percent && $updater != 'yes'){
			array_push($post_get, $post_id->ID);
			$dom = new Dom;
			$dom->loadFromUrl($url);

			$product_availability = $dom->find('[property="product:availability"]');
			$p_a_content = $product_availability->getAttribute('content');

			$tag_price = $dom->find('.goods-price .price');
			$html = $tag_price->innerHtml;
			$price = (int)str_replace(',', '', $html);
			$toman = $price * 0.1;

			$reg_price = $toman + ($toman * ($percent / 100)); 
			$reg_price = floor($reg_price/1000)*1000;

			$stock_status = ($p_a_content == 'in stock') ? 'instock' : 'outofstock';
			if($stock_status  == 'outofstock'){
				update_post_meta($post_id->ID, '_stock', 0);
				update_post_meta($post_id->ID, '_regular_price' , 0);
			}else{
				update_post_meta($post_id->ID, '_stock', 1);
				update_post_meta($post_id->ID, '_regular_price' , $reg_price);
			}
			wp_set_post_terms($post_id->ID, $stock_status, 'product_visibility', true );
			update_post_meta($post_id->ID, '_stock_status', $stock_status);
		}
	}

	$time_elapsed_secs = microtime(true) - $start;
	$content = 'time_elapsed : ' . date("H:i:s",$time_elapsed_secs) .PHP_EOL;
	file_put_contents('log.txt',$content , FILE_APPEND);
	// return [
	// 	'the_posts' => $post_get,
	// 	'time_elapsed' , $time_elapsed_secs
	// ];
}
// add_action('init', 'smGetPrice');
// add_shortcode('smGetPrice', 'smGetPrice');


function getasdlk(){
	// $query = array(
	// 	'post_type' => 'product_variation',
	// 	'posts_per_page' => -1,
	// 	'meta_query' => array(
	// 		array(
	// 			'key' => '_price',
	// 			'value' => '0'
	// 		),
	// 	)
	// );
	// $wp_query = query_posts( $query );

	// foreach ($wp_query as $wp_quer) {
	// 	print_r($wp_quer->ID);
	// 	update_post_meta($wp_quer->ID , '_stock_status' , 'outofstock');
	// }

	global $wpdb;
	// $post_ids = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` IN ('product_variation','product')");	
	// $post_ids = $wpdb->get_results("SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` LIKE 'product'");	
	foreach ($post_ids as $post_id) {
		// $handle=new WC_Product_Variable($post_id->ID);
		// $variations = $handle->get_children();
		// if(isset($variations) && !empty($variations)){
			
		// 	$prod = [];
		// 	foreach ($variations as $variation) {
		// 		$status = get_post_meta($variation, '_stock_status', true);
		// 		$prod[$post_id->ID] = [$status];
		// 	}
		// }
		// echo '<br>';
		// if(($prod[$post_id->ID])[0] == 'instock'){
		// 	update_post_meta($post_id->ID, '_stock_status', 'instock');
		// }
		
		// $status = get_post_meta($post_id->ID, '_stock_status', true);
		// echo '<br>';
		// print_r($status);
		// if($status == 'instock'){
		// 	update_post_meta($post_id->ID, 'sm-price-stock', 1);
		// }else{
		// 	update_post_meta($post_id->ID, 'sm-price-stock', 0);
		// 	update_post_meta($post_id->ID, '_price', 0);
		// 	update_post_meta($post_id->ID, '_stock_status', 'outofstock');
		// }
	}
}
// add_shortcode('getasdlk' , 'getasdlk');
