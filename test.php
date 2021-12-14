
// 1. Add custom field input @ Product Data > Variations > Single Variation
function sm_add_custom_field_to_variations( $loop, $variation_data, $variation ) {
	woocommerce_wp_text_input(
		array(
			'id' => 'target-product[' . $loop . ']',
			'class' => 'short',
			'type'  => 'url',
			'label' => 'آدرس محصول مورد نظر :',
			'custom_attributes' => array( 'required' => 'required' ),
			'value' => get_post_meta($variation->ID, 'target-product-address', true)
		)
	);
	woocommerce_wp_text_input(
		array(
			'id' => 'interest-rates[' . $loop . ']',
			'class' => 'short',
			'type' => 'number',
			'label' => 'درصد سود شما :',
			'custom_attributes' => array( 'required' => 'required' ),
			'value' => get_post_meta($variation->ID, 'interest-rates', true)
		)
	);
}
add_action( 'woocommerce_variation_options_pricing', 'sm_add_custom_field_to_variations', 10, 3 );
 

// 2. Save custom field on product variation save 
function sm_save_custom_field_variations( $variation_id, $i ) {
	$target_product = $_POST['target-product'][$i];
	$interest_rates = $_POST['interest-rates'][$i];
	if (isset( $target_product ))
		update_post_meta( $variation_id, 'target-product-address', esc_attr( $target_product ) );
	if(isset($interest_rates))
		update_post_meta( $variation_id, 'interest-rates', esc_attr( $interest_rates ) );
}
add_action( 'woocommerce_save_product_variation', 'sm_save_custom_field_variations', 10, 2 );
 
// 3. Store custom field value into variation data
function sm_add_custom_field_variation_data( $variations ) {
	$variations['target-product'] = '<div class="woocommerce_custom_field">Custom Field: <span>' . get_post_meta( $variations[ 'variation_id' ], 'target-product-address', true ) . '</span></div>';
	$variations['interest-rates'] = '<div class="woocommerce_custom_field">Custom Field: <span>' . get_post_meta( $variations[ 'variation_id' ], 'interest-rates', true ) . '</span></div>';
	return $variations;
}
add_filter( 'woocommerce_available_variation', 'sm_add_custom_field_variation_data' );