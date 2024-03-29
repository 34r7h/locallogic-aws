<?php
//If Direct Access Kill the Script
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

/*
* Return Coupon Categories for Class
* @version 1.90
*/
function cctor_return_coupon_categories($coupon_id) {
 	
	$coupon_terms = get_the_terms( $coupon_id, 'cctor_coupon_category' );
	
	if ( $coupon_terms && ! is_wp_error( $coupon_terms ) ) { 

		$cctor_coupon_category_terms = array();

		foreach ( $coupon_terms as $coupon_term ) {
			$cctor_coupon_category_terms[] = $coupon_term->slug;
		}
						
		$coupon_cat_class = join( " ", $cctor_coupon_category_terms );
		
		return $coupon_cat_class;
	}
}
/*
* Coupon Creator Outer Wrap
* @version 1.90
*/
function cctor_return_outer_coupon_wrap($coupon_id, $coupon_align) { 
	
	
	if(cctor_return_image_url($coupon_id)) {
		$coupon_img_class = 'cctor-image';
	} else {
		$coupon_img_class = '';
	}
	
	$coupon_cat_class = cctor_return_coupon_categories($coupon_id);
	
	$outer_coupon_wrap = array();
	$outer_coupon_wrap['start_wrap'] = '<!--start coupon container here -->
		<div id="coupon_creator_'. esc_attr($coupon_id).'" class="type-cctor_coupon cctor_coupon_container '.esc_attr($coupon_cat_class).' '.esc_attr($coupon_align).' '.esc_attr($coupon_img_class).'">';
	
	$outer_coupon_wrap['end_wrap'] = '</div> <!--end #cctor_coupon_container -->';
							
	return $outer_coupon_wrap;
}			
/*
* Coupon Creator Inner Wrap
* @version 1.90
*/
function cctor_return_inner_coupon_wrap($coupon_id) { 

	$coupon_inner_content_wrap = array();
	
	$endlink = '';
	
	//Build Click to Print Link For Coupon - First Check if Option to Hide is Checked
	if (cctor_options('cctor_hide_print_link') == 0) {
		
		if (cctor_options('cctor_nofollow_print_link') == 1) {
			$nofollow = "rel='nofollow'";
		} 
			
		$coupon_inner_content_wrap['start_wrap'] = '<a class="cctor_wrap_link" '.$nofollow.' href="'. esc_url(get_permalink($coupon_id)).'" onclick="window.open(this.href);return false;">
			<div class="cctor_coupon">
			<div class="cctor_coupon_content" style="border-color:'. esc_attr(get_post_meta($coupon_id, 'cctor_bordercolor', true)).'">';
	
		$endlink = '</a>';

	 } else {
			$coupon_inner_content_wrap['start_wrap'] = '<div class="cctor_coupon">
			<div class="cctor_coupon_content" style="border-color:'. esc_attr(get_post_meta($coupon_id, 'cctor_bordercolor', true)).'">';
	
	 }

	$coupon_inner_content_wrap['end_wrap'] = '</div> <!--end .cctor_coupon_content -->
												</div> <!--end .cctor_coupon -->'. $endlink;
	 
	return $coupon_inner_content_wrap;
}	

/*
* Coupon Creator Print Outer Wrap
* @version 1.90
*/
function cctor_return_print_outer_coupon_wrap($coupon_id) { 

	if(cctor_return_image_url($coupon_id)) {
		$coupon_img_class = ' cctor-image ';
	} else {
		$coupon_img_class = '';
	}
	$coupon_cat_class = cctor_return_coupon_categories($coupon_id);
	
	$outer_coupon_wrap = array();
	$outer_coupon_wrap['start_wrap'] = '<!--start coupon container -->
		<div id="coupon_creator_'. esc_attr($coupon_id).'" class="cctor_coupon_container '.esc_attr($coupon_cat_class).' '.esc_attr($coupon_img_class).'">';
	
	$outer_coupon_wrap['end_wrap'] = '</div> <!--end #cctor_coupon_container -->';
							
	return $outer_coupon_wrap;
}	

/*
*  Coupon Creator Print Inner Wrap
*  @version 1.90
*/
function cctor_return_print_inner_coupon_wrap($coupon_id) { 
	
	$coupon_inner_content_wrap = array();
	$coupon_inner_content_wrap['start_wrap'] = '<div class="cctor_coupon">
	<div class="cctor_coupon_content" style="border-color:'. esc_attr(get_post_meta($coupon_id, 'cctor_bordercolor', true)).'">';
	
	$coupon_inner_content_wrap['end_wrap'] = '</div> <!--end .cctor_coupon_content -->
							</div> <!--end .cctor_coupon -->';
							
	return $coupon_inner_content_wrap;
}	


