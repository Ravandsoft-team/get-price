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
	// wp_enqueue_style('sm-bootstrap', plugins_url('bootstrap.rtl.min.css' , __FILE__), false, false);
	// wp_enqueue_script('sm-bootstrap', plugins_url('bootstrap.bundle.min.js' ,__FILE__), array('jquery'), true);
	$start = microtime(true);
	global $wpdb;
	$post_ids = $wpdb->get_results("SELECT `ID` FROM {$wpdb->posts} WHERE `post_type` IN ('product','product_variation')");
	
	$post_get = [];
	foreach ($post_ids as $post_id) {
		$url = get_post_meta($post_id->ID,'target-url', true);
		$percent = get_post_meta($post_id->ID,'interest-rates', true);
		$updater = get_post_meta($post_id->ID,'price-updater', true);

		if($url && $percent && $updater != 'yes'){
			array_push($post_get, $post_id->ID);
			$dom = new Dom;
			$dom->loadFromUrl($url);
			$tag_price = $dom->find('.goods-price .price');
			$html = $tag_price->innerHtml;
			$price = (int)str_replace(',', '', $html);
			$toman = $price * 0.1;

			$reg_price = $toman + ($toman * ($percent / 100)); 
			$reg_price = floor($reg_price/1000)*1000;
			update_post_meta($post_id->ID, '_regular_price' , $reg_price);
		}
	}

	$time_elapsed_secs = microtime(true) - $start;
	return [
		'the_posts' => $post_get,
		'time_elapsed' , $time_elapsed_secs
	];
}
add_action('init', 'smGetPrice');
// smGetPrice();
