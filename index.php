<?php
/**
 * Get Price
 *
 * Plugin Name: Get Price
 * Plugin URI:  https://ravandsoft.com
 * Description: Give me a link and i will find price that (don't forgot that link should belong to a product) 😉
 * Version:     1.0
 * Author:      S Morteza Yasrebi
 * Author URI:  https://ravandsoft.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

add_action('rest_api_init', 
    function () {
        register_rest_route('smprice', '/get/(?P<offset>\d+)/(?P<limit>\d+)/', array(
            'methods' => 'GET',
            'callback' => 'smGetPrice',
			'permission_callback' => '__return_true'
        ) );
    }
);
add_action('rest_api_init', 
    function () {
        register_rest_route('smprice', '/product-status', array(
            'methods' => 'GET',
            'callback' => 'smproductStatus',
			'permission_callback' => '__return_true'
        ) );
    }
);

// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

function smGetPrice($data){
	// wp_enqueue_style('sm-bootstrap', plugins_url('bootstrap.rtl.min.css' , __FILE__), false, false);
	// wp_enqueue_script('sm-bootstrap', plugins_url('bootstrap.bundle.min.js' ,__FILE__), array('jquery'), true);
	$start = microtime(true);
	global $wpdb;

	// $offset = $_GET['offset'];
	// $limit = $_GET['limit'];
	$offset = $data['offset'];
	$limit = $data['limit'];

	$posts = $wpdb->get_results("SELECT `ID` FROM {$wpdb->posts} WHERE `post_type` IN ('product','product_variation') LIMIT $offset,$limit");
	// $posts = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` LIKE 'target-url'");
	// return $posts;
	$post_get = [];
	foreach ($posts as $post) {
		$post_id = $post->ID;
		$url = get_post_meta($post_id, 'target-url', true);
		$percent = get_post_meta($post_id, 'interest-rates', true);
		$updater = get_post_meta($post_id, 'price-updater', true);
		
		if(empty($updater) || !$updater || count($updater) != 3 )$updater = 'no';
	
		if(isset($url) && !empty($url) && isset($percent) && !empty($percent) && $updater != 'yes'){
			$dom = new Dom;
			$dom->loadFromUrl($url);

			$product_availability = $dom->find('[property="product:availability"]');
			$p_a_content = ($product_availability) ? $product_availability->getAttribute('content') : false;

			$tag_price = $dom->find('.goods-price .price');
			$html = $tag_price->innerHtml;
			$price = (int)str_replace(',', '', $html);
			$toman = $price * 0.1;

			$reg_price = ($toman * (($percent / 100) + 1)); 
			$reg_price = floor($reg_price/1000)*1000;

			$stock_status = ($p_a_content == 'in stock') ? 'instock' : 'outofstock';

			// $variation = wc_get_product($post_id);
			// $product = wc_get_product( $variation->get_parent_id() );
			if($stock_status  == 'outofstock'){
				// update_post_meta($product->get_id(), '_stock', 0);
				update_post_meta($post_id, '_stock', 0);
				update_post_meta($post_id, '_regular_price' , 0);		
			}else{
				update_post_meta($post_id, '_stock', 1);
				update_post_meta($post_id, '_regular_price' , $reg_price);
			}
			// update_post_meta($product->get_id(), '_stock_status', $stock_status);
			// wp_set_post_terms($product->get_id(), $stock_status, 'product_visibility', true );
			update_post_meta($post_id, '_stock_status', $stock_status);
			wp_set_post_terms($post_id, $stock_status, 'product_visibility', true ); 
			array_push($post_get ,$post_id);
		}
	}

	$time_elapsed_secs = microtime(true) - $start;
	$content = 'date : ' . date("Y:m:d H:i:s") . ' | time_elapsed : ' . date("H:i:s",$time_elapsed_secs) . ' | posts :' . json_encode($post_get) . PHP_EOL;
	file_put_contents('log.txt',$content , FILE_APPEND);
}
// add_action('init', 'smGetPrice');
add_shortcode('smGetPrice', 'smGetPrice');

function smproductStatus(){
	global $wpdb;
	$posts = $wpdb->get_results("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` LIKE 'target-url'");
	$products = [];
	foreach ($posts as $post) {
		$post_id = $post->post_id;
		$variation = wc_get_product($post_id);
		$product = wc_get_product( $variation->get_parent_id() );
		// echo '<br>';
		// echo $post_id;
		// echo $stock_status;
		// echo $product->get_id();
		// echo '<br>';
		if($product)
		$products[$product->get_id()][] = $post_id;
		// $the_id = ($product) ? $product->get_id() : $post_id;
		// if($stock_status == 'instock'){
		// 	update_post_meta($the_id, '_stock_status', 'instock');
		// 	wp_set_post_terms($the_id, 'instock', 'product_visibility', true );
		// }else{
		// 	update_post_meta($the_id, '_stock', 0);
		// 	update_post_meta($the_id, '_stock_status', 'outofstock');
		// }
	}
	$products2 = [];
	foreach($products as $key => $theid){
		for($i=0; $i< count($theid); $i++){
			$stock_status = get_post_meta($theid[$i], '_stock_status', true);
			$products2[$key][] = $stock_status;
		}
	}
	// echo '<pre>';
	// print_r($products2);
	// echo '</pre>';
	// $products3 = [];
	foreach($products2 as $key => $prod){
		if(in_array("instock" , $prod)){
			// $products3[$key] = 'instock';
			update_post_meta($key, '_stock_status', 'instock');
			wp_set_post_terms($key, 'instock', 'product_visibility', true );
		}else{
			// $products3[$key] = 'outofstock';
			update_post_meta($key, '_stock', 0);
			update_post_meta($key, '_stock_status', 'outofstock');
		}
	}
	// echo '<pre>';
	// print_r($products3);
	// echo '</pre>';
}