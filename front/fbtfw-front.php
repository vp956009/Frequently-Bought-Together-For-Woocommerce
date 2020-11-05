<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('fbtfw_front')) {

    class fbtfw_front {

        protected static $fbtfw_instance;

        public static function fbtfw_instance() {
            if (!isset(self::$fbtfw_instance)) {
                self::$fbtfw_instance = new self();
                self::$fbtfw_instance->init();
            }
            return self::$fbtfw_instance;
        }

        function init() {
            add_action( 'wp_head', array($this, 'fbtfw_get_page_id') );
            add_filter( 'woocommerce_add_cart_item_data', array( $this, 'fbtfw_add_cart_item_data' ), 10, 3 );
            add_action( 'woocommerce_add_to_cart', array( $this, 'fbtfw_add_to_cart' ), 10, 6 );
            add_action( 'template_redirect', array($this, 'fbtfw_iconic_add_to_cart') );
            add_action( 'woocommerce_before_calculate_totals', array($this, 'fbtfw_custom_price_to_cart_item') , 99 );
            add_shortcode( 'Woo_Frequently_added', array($this,'fbtfw_woo_combo'));
            add_filter( 'woocommerce_cart_item_name', array( $this, 'fbtfw_bought_together_item_name' ), 10, 2 );
            add_filter( 'woocommerce_order_item_name', array( $this, 'fbtfw_bought_together_item_name' ), 10, 2 );
            add_action( 'woocommerce_cart_item_removed', array( $this, 'fbtfw_cart_item_removed' ), 10, 2 );
        }


        function fbtfw_get_page_id() {
        	if ( is_product() ) {
	            $page_security = get_queried_object();
	            if($page_security->post_type == "product") {
	            	$product_id = $page_security->ID;
	            	$layout     = get_post_meta($product_id , 'occp_layout', true );
	                if( $layout == "layout1" ) {
	                    $product = wc_get_product($product_id);
	                    if($product->is_type( 'variable' )) {
	                        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'fbtfw_layout1' ) );
	                    }else{
	                        add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'fbtfw_layout1' ) );
	                    }     
	                }else if( $layout == "layout2" ) {
	                    add_filter( 'woocommerce_after_single_product_summary', array($this, 'fbtfw_layout2'), 5);
	                } 
	            }
	        }
        }


        function fbtfw_layout1 () {
            $product = get_post_meta( get_the_ID(), 'occp_select2', true );
            $occp_discunt = get_post_meta( get_the_ID(), 'occp_off_per', true );
            $occp_discunt_type = get_post_meta( get_the_ID(), 'occp_discount_type', true );
            if(empty($product)) {
              return;
            }
            $main_product = wc_get_product( get_the_ID() );
            array_unshift($product, get_the_ID());
            $count  = 0;
            $badge ='';
            $product_details = '';
            $total= 0;
            $images = '';
            foreach ($product as $productId) {
                $product = wc_get_product( $productId );

                $current_product_link =  $product->get_permalink();
                $current_product_image = $product->get_image();
                $current_product_title = $product->get_title();
                $current_product_price = $product->get_price();
				$current_product_id = $product->get_id();
                $current_product_is_variation   = $product->is_type( 'variation' );

                $current_product_discount='';
                $current_product_discount_type='';
                if(!empty($occp_discunt[$current_product_id])) {
                    $current_product_discount = $occp_discunt[$current_product_id];
                }
                if(!empty($occp_discunt_type[$current_product_id])) {
                    $current_product_discount_type = $occp_discunt_type[$current_product_id];
                }
                

                $current_product_exact_price = $this->fbtfw_get_price($current_product_price, $current_product_discount, $current_product_discount_type);
                if($count == 0) {
                    $current_product_exact_prices = 0;
           		} else {
                    $current_product_exact_prices = $current_product_exact_price;
                }
           		
                $dis_type = get_post_meta( get_the_ID(), 'occp_discount_type' );
                $dis_amt = get_post_meta( get_the_ID(), 'occp_off_per' );

                if(!empty($dis_amt[0][$current_product_id])) {
                    if(get_option('woocommerce_currency_pos') == 'left' || get_option('woocommerce_currency_pos') == 'left_space'){
                        if($dis_type[0][$current_product_id] == "percentage") {
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.$dis_amt[0][$current_product_id].' %</span></div>';
                        }else if($dis_type[0][$current_product_id] == "fixed") {
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.get_woocommerce_currency_symbol().$dis_amt[0][$current_product_id].'</span></div>';
                        }
                    }else{
                        if($dis_type[0][$current_product_id] == "percentage") {
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.$dis_amt[0][$current_product_id].' %</span></div>';
                        }else if($dis_type[0][$current_product_id] == "fixed") {
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.$dis_amt[0][$current_product_id].get_woocommerce_currency_symbol(). '</span></div>';
                        }
                    }
                }

                $images .= '<td class="fbtfw_img sss" image_pro_id="'.$current_product_id.'"><div class="fbtfw_img_div"><a href="' . $current_product_link . '">' . $current_product_image . '</a>'.$badge.'</div></td>';
                ob_start();
                ?>
                <div class="fbtfw_each_item <?php if($count == 0) { echo 'fbtfw_each_curprod'; } ?>">
                    <div class="fbtfw_product_check">
                        <input type="checkbox" name="proID[]" id="proID_<?php echo $count ?>" class="product_check" value="<?php echo $current_product_id; ?>" price="<?php echo $current_product_exact_price ; ?>" checked <?php if($count == 0){ echo "disabled"; } ?>/>
                    </div>

                    <div class="fbtfw_product_image">
                        <?php echo $product->get_image(); ?>
                    </div>

                    <div class="fbtfw_product_title">
                        <span>
                            <?php
                            if($count == 0) {
                                echo 'This item: '.$current_product_title;
                            } else {
                                echo '<a href="'.$current_product_link.'">'.$current_product_title.'</a>';
                            }
                            ?>
                        </span>
                        <?php
                            if( $current_product_is_variation ) {
                                $attributes = $product->get_variation_attributes();
                                $variations = array();

                                foreach( $attributes as $key => $attribute ) {
                                    $variations[] = $attribute;
                                }

                                if( ! empty( $variations ) )
                                echo '<span class="product-attributes"> &ndash; ' . implode( ', ', $variations ) . '</span>';
                            }
                        ?>
                    </div>

                    <div class="fbtfw_product_price">
                    	<?php 
                    		if(!empty($product->get_price())) { 
                                $price = wc_price($product->get_price()); 
                            }else { 
                                $price = wc_price(0);
                            }
                            echo '<span class="fbtfw_price_old">' . $price . '</span><span class="fbtfw_price_new">('. wc_price($current_product_exact_price) .')</span>';
                        ?>
                    </div>
                </div>
                <?php
                $product_details .= ob_get_clean();
                // increment total
                $total += floatval( $current_product_exact_prices );
                $count++;
            }
            ?>

            <input type="hidden" name="formate" value="<?php echo get_woocommerce_currency_symbol(); ?>" class="formate">
            <input type="hidden" name="layout" value="layout1" class="layout">
            <div class="fbtfw_main layout1">
            	<h3><?php echo get_post_meta( get_the_ID(), 'occp_head_txt', true ); ?></h3>
                <div class="fbtfw_div">
                    <?php echo $product_details; ?>
                </div>
                <div class="fbtfw_cart_div">
                    <div class="fbtfw_price">
                        <span class="fbtfw_price_label">
                            <?php echo "Additional Amount : "; ?>
                        </span>
                        &nbsp;
                        <span class="fbtfw_price_total" data-total="<?php echo $total ?>">
                            <?php echo wc_price( $total ); ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php   
        }


        function fbtfw_layout2() {
            $product = get_post_meta( get_the_ID(), 'occp_select2', true );

            $occp_count = count($product);
            $occp_badge = 'true';

            if(wp_is_mobile()) {
                if($occp_count > 2) {
                    $occp_badge = 'false';
                }
            }

            $occp_discunt = get_post_meta( get_the_ID(), 'occp_off_per', true );
            $occp_discunt_type = get_post_meta( get_the_ID(), 'occp_discount_type', true );

            if(empty($product)) {
              	return;
            }

            $main_product = wc_get_product( get_the_ID() );
            if( $main_product->has_child() ) {
              	$product_variable = new WC_Product_Variable( get_the_ID() );
              	$variations = $product_variable->get_available_variations();
              	$vari = 0;
              	foreach ($variations as $variation) {
                	$vari++;
                	if($vari == 1){
                  		if (in_array($variation['variation_id'], $product)){ 
                    
                  		}else{
                    		array_unshift($product,$variation['variation_id']);
                  		}
                	}
              	}
            } else {
              	array_unshift($product, get_the_ID());
            }
            $count = 0;
            $badge = '';
            $product_details = '';
            $total= 0;
            $images = '';
            foreach ($product as $productId) {
                $product = wc_get_product( $productId );
                $current_product_link = $product->get_permalink();
                $current_product_image = $product->get_image();
                $current_product_title = $product->get_title();
                $current_product_price = $product->get_price();
                $current_product_id = $product->get_id();
                $current_product_is_variation   = $product->is_type( 'variation' );
                $current_product_discount='';
                $current_product_discount_type='';
                if(!empty($occp_discunt[$current_product_id])) {
                    $current_product_discount = $occp_discunt[$current_product_id];
                }
                if(!empty($occp_discunt_type[$current_product_id])) {
                    $current_product_discount_type = $occp_discunt_type[$current_product_id];
                }
                
				$current_product_exact_price = $this->fbtfw_get_price($current_product_price, $current_product_discount, $current_product_discount_type);

                $dis_type = get_post_meta( get_the_ID(), 'occp_discount_type' );
                $dis_amt = get_post_meta( get_the_ID(), 'occp_off_per' );
				                
                if(!empty($dis_amt[0][$current_product_id])) {
                    if(get_option('woocommerce_currency_pos') == 'left' || get_option('woocommerce_currency_pos') == 'left_space'){
                        if($dis_type[0][$current_product_id] == "percentage") {
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.$dis_amt[0][$current_product_id].' %</span></div>';
                        }else if($dis_type[0][$current_product_id] == "fixed") {
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.get_woocommerce_currency_symbol().$dis_amt[0][$current_product_id].'</span></div>';
                        }
                    }else{
                        if($dis_type[0][$current_product_id] == "percentage") {
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.$dis_amt[0][$current_product_id].' %</span></div>';
                        }else if($dis_type[0][$current_product_id] == "fixed") {
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.$dis_amt[0][$current_product_id].get_woocommerce_currency_symbol(). '</span></div>';
                        }
                    }
                }

                if($occp_badge == 'true') {
                    $badge = $badge;
                } else {
                    $badge = '';
                }

                $images .= '<td class="fbtfw_img sss" image_pro_id="'.$current_product_id.'"><div class="fbtfw_img_div"><a href="' . $current_product_link . '">' . $current_product_image . '</a>'.$badge.'</div></td>';


                if( $count < $occp_count ) {
                	$isimplfirst = '';
                	if($count == 0) {
                		$isimplfirst = 'fbtfw_img_plus_first';
                	}
                    $images .= '<td class="fbtfw_img_plus '.$isimplfirst.'" fbtfw_imgpls_id="'.$current_product_id.'">+</td>';
                }
               
                ob_start();
                ?>
                <li class="fbtfw_each_item">
                    <input type="checkbox" name="proID[]" id="proID_<?php echo $count ?>" class="product_check" value="<?php echo $current_product_id; ?>" price="<?php echo $current_product_exact_price; ?>" checked <?php if($count == 0) { echo 'disabled'; } ?> />
                    <span class="fbtfw_product_title">
                        <?php
                        if($count == 0) {
                            echo 'This item: '.$current_product_title;
                        } else {
                            echo '<a href="'.$current_product_link.'">'.$current_product_title.'</a>';
                        }
                        ?>
                    </span>
                    <?php
                        if( $current_product_is_variation ) {
                            $attributes = $product->get_variation_attributes();
                            $variations = array();

                            foreach( $attributes as $key => $attribute ) {
                                $variations[] = $attribute;
                            }

                            if( ! empty( $variations ) )
                            echo '<span class="product-attributes"> &ndash; ' . implode( ', ', $variations ) . '</span>';
                        }
                       
                        if(!empty($product->get_price())) { 
                            $price = wc_price($product->get_price()); 
                        }else { 
                            $price = wc_price(0);
                        }
                        echo ' &ndash; <span class="fbtfw_price_old">' . $price . '</span><span class="fbtfw_price_new">('. wc_price($current_product_exact_price) .')</span>';
                    ?>
                </li>
                <?php
                $product_details .= ob_get_clean();
                // increment total
                $total += floatval( $current_product_exact_price );
                $badge = '';
                $count++;
            }
            ?>
        	<input type="hidden" name="formate" value="<?php echo get_woocommerce_currency_symbol(); ?>" class="formate">
            <input type="hidden" name="layout" value="layout2" class="layout">
            <div class="fbtfw_main layout2">
	        	<form class="fbtfw_product_form" method="post" action="">
	            	<h3><?php echo get_post_meta( get_the_ID(), 'occp_head_txt', true ); ?></h3>
	                <table class="fbtfw_product_images">
	                    <tbody>
	                        <tr>
	                            <?php echo $images; ?>
	                            
	                        </tr>
	                    </tbody>
	                </table>
	                <div class="fbtfw_cart_div" style="margin-bottom: 10px;">
	                    <div class="fbtfw_price">
	                        <span class="fbtfw_price_label">
	                            <?php echo "Price for all : "; 
	                            ?>
	                        </span>
	                        &nbsp;
	                        <span class="fbtfw_price_total" data-total="<?php echo $total ?>">
	                            <?php echo wc_price( $total ); ?>
	                        </span>
	                    </div>
	                    <input type="submit" class="occp_add_cart_button button" value="<?php echo "Add To Cart"; ?>" name="occp_add_to_cart">
	                </div> 

	                <ul class="fbtfw_ul">
	                    <?php echo $product_details; ?>
	                </ul>        
	            </form>
	        </div>
            <?php
        }


        function fbtfw_get_price($price, $discount, $discount_type) {
        	if(empty($price)){
        		$price = 0;
        	}else{
        		if(empty($discount)) {
        			$price = $price;
        		}else{
        			if($discount_type == "percentage") {
	        			$price = $price - ( $price * $discount / 100 );
	        		} else {
	        			$price = $price - $discount;
	        		}
        		}
        	}
        	return $price;
		}


        function fbtfw_recursive_sanitize_text_field($array) {
         
            foreach ( $array as $key => &$value ) {
                if ( is_array( $value ) ) {
                    $value = $this->fbtfw_recursive_sanitize_text_field($value);
                }else{
                    $value = sanitize_text_field( $value );
                }
            }
            return $array;
        }


        function fbtfw_add_cart_item_data( $cart_item_data, $product_id ) {
            if(isset($_POST['proID']) && !empty($_POST['proID'])) {
                $fbtfw_combo_ids =  $this->fbtfw_recursive_sanitize_text_field($_POST['proID']);
            }
            
            if( empty( $fbtfw_combo_ids ) ) {
                return;
            }
            
            if ( ! empty( $fbtfw_combo_ids ) ) {
                $cart_item_data['combo_ids'] = $fbtfw_combo_ids;
            }

            return $cart_item_data;
        }


        /*add to cart for layout1*/
        function fbtfw_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
        	
            if ( isset( $cart_item_data['combo_ids'] ) && ( $cart_item_data['combo_ids'] !== '' ) ) {

                $fbtfwitems = $cart_item_data['combo_ids'];

                remove_action( 'woocommerce_add_to_cart', array( $this, 'fbtfw_add_to_cart' ), 10, 6 );

                foreach ($fbtfwitems as $keya => $valuea) {
                    $occp_product = wc_get_product( $valuea );
                    if ( $occp_product && $occp_product->is_in_stock() && $occp_product->is_purchasable() ) {
                        $cart_item_keya = WC()->cart->add_to_cart( $valuea, 1, 0, array(), array("fbtfw_parent_id" => $product_id, "fbtfw_parent_key" => $cart_item_key) );
                        
                        if ( $cart_item_keya ) {
                            WC()->cart->cart_contents[ $cart_item_key ]['fbtfw_child_keys'][] = $cart_item_keya;
                        }

                    }
                }
            }
        }


        /*add to cart for layout2 and shortcode*/
        function fbtfw_iconic_add_to_cart() {
            global $woocommerce;
            $occp_main_id = get_the_ID();

            if(isset($_REQUEST['occp_add_to_cart'])) {

                $product_cust = $this->fbtfw_recursive_sanitize_text_field( $_POST['proID'] );
                $linked_prods = $this->fbtfw_recursive_sanitize_text_field( $_POST['proID'] );
				if( empty( $product_cust ) ) {
                    return;
                }
                
                array_unshift($product_cust, $occp_main_id);

                remove_action( 'woocommerce_add_to_cart', array( $this, 'fbtfw_add_to_cart' ), 10, 6 );
                    
                $fbtfw_parent_key = '';
                $fbtfw_child_keys = array();

                foreach( $product_cust as $id ) {
                    $occp_product = wc_get_product( $id );

                    if($id == $occp_main_id) {
                    	$custom_data = array(
		                    				"fbtfw_ids" => $linked_prods,
		                    			);
                    } else {
                    	$custom_data = array(
		                    				"fbtfw_parent_id" => $occp_main_id
		                    			);
                    }

                    if ( $occp_product && $occp_product->is_in_stock() && $occp_product->is_purchasable() ) {
                        if($id == $occp_main_id ) {
                            $fbtfw_parent_key = WC()->cart->add_to_cart( $id, 1, 0, array(), $custom_data );
                        } else {
                            $custom_data['fbtfw_parent_key'] = $fbtfw_parent_key;
                            $fbtfw_child_keys[] = WC()->cart->add_to_cart( $id, 1, 0, array(), $custom_data );
                        }
                    }
                }
                
                if ( !empty($fbtfw_child_keys) ) {
                    $woocommerce->cart->cart_contents[$fbtfw_parent_key]['fbtfw_child_keys'] = $fbtfw_child_keys;
                    $woocommerce->cart->set_session();
                }

                $cart_url = $woocommerce->cart->get_cart_url();
                wp_redirect( $cart_url );
                exit;
            }
        }


        /*set price discount wise*/
        function fbtfw_custom_price_to_cart_item( $cart_object ) {
            if( !WC()->session->__isset( "reload_checkout" )) {
                foreach ( $cart_object->get_cart() as $key => $value ) {
            
                    if( isset( $value["fbtfw_parent_id"] ) ) {
                        
                        $product_id = $value['data']->get_id();
                        $ID = $value["fbtfw_parent_id"];
                        $product = get_post_meta( $ID, 'occp_select2', true );
                        $fbtfw_discunt = get_post_meta( $ID, 'occp_off_per', true );
                        $fbtfw_discunt_type = get_post_meta( $ID, 'occp_discount_type', true );
                        
                        $product = wc_get_product( $product_id );
                        $price = $product->get_price();
                        
                        if(!empty($fbtfw_discunt[$product_id])){
                        	$fbtfw_discount = $fbtfw_discunt[$product_id];
                        }
                        
                        if(!empty($fbtfw_discunt_type[$product_id])){
                        	$fbtfw_discount_type = $fbtfw_discunt_type[$product_id];
                        }
                        
                        if(isset($fbtfw_discount) && isset($fbtfw_discount_type)) {

                        	$fbtfw_exact_price = $this->fbtfw_get_price($price, $fbtfw_discount, $fbtfw_discount_type);
                            
                        	$value['data']->set_price( $fbtfw_exact_price );
                        }
                    }

                } 
            }   
        }

        function fbtfw_bought_together_item_name( $item_name, $item ) {
            if ( isset( $item['fbtfw_parent_id'] ) && ! empty( $item['fbtfw_parent_id'] ) ) {

                $occp_btassociated_txt = get_post_meta( $item['fbtfw_parent_id'], 'occp_btassociated_txt', true );

                if($occp_btassociated_txt != '') {
                    $fbtfw_btogether_text = esc_html__( $occp_btassociated_txt, 'fbtfw' );
                } else {
                    $fbtfw_btogether_text = esc_html__( '(bought together %s)', 'fbtfw' );
                }

                if ( strpos( $item_name, '</a>' ) !== false ) {
                    $name = sprintf( $fbtfw_btogether_text, '<a href="' . get_permalink( $item['fbtfw_parent_id'] ) . '">' . get_the_title( $item['fbtfw_parent_id'] ) . '</a>' );
                } else {
                    $name = sprintf( $fbtfw_btogether_text, get_the_title( $item['fbtfw_parent_id'] ) );
                }

                $item_name .= ' <span class="fbtfw_parent_name">' . apply_filters( 'fbtfw_parent_name', $name, $item ) . '</span>';
            }

            return $item_name;
        }


        function fbtfw_cart_item_removed( $cart_item_key, $cart ) {
            if ( isset( $cart->removed_cart_contents[ $cart_item_key ]['fbtfw_child_keys'] ) ) {
                $keys = $cart->removed_cart_contents[ $cart_item_key ]['fbtfw_child_keys'];

                foreach ( $keys as $key ) {
                    unset( $cart->cart_contents[ $key ] );
                }
            }
        }


        /*design for shortcode*/
        function fbtfw_woo_combo($atts, $content = null) {
            $page_security = get_queried_object();
            if(get_post_meta( $page_security->ID, 'occp_layout', true ) == "none"){
                $product = get_post_meta( get_the_ID(), 'occp_select2', true );
            $occp_discunt = get_post_meta( get_the_ID(), 'occp_off_per', true );

            $occp_discunt_type = get_post_meta( get_the_ID(), 'occp_discount_type', true );

            if(empty($product)){
              return;
            }

            $main_product = wc_get_product( get_the_ID() );
            if( $main_product->has_child() ){
              	$product_variable = new WC_Product_Variable( get_the_ID() );
              	$variations = $product_variable->get_available_variations();
              	$vari = 0;
              	foreach ($variations as $variation) {
                	$vari++;
                	if($vari == 1){
                  		if (in_array($variation['variation_id'], $product)){ 
                    
                  		}else{
                    		array_unshift($product,$variation['variation_id']);
                  		}
                	}
              	}
            }else{
              	array_unshift($product, get_the_ID());
            }
            $count           = 0;
            $badge           = "";
            $images          = "";
            $product_details = "";
            $total = 0;
            foreach ($product as $productId) {
                $product = wc_get_product( $productId );
                $current_product_link = $product->get_permalink();
                $current_product_image = $product->get_image();
                $current_product_title = $product->get_title();
                $current_product_price = $product->get_price();
                $current_product_id = $product->get_id();
                $current_product_is_variation   = $product->is_type( 'variation' );

                $current_product_discount='';
                $current_product_discount_type='';
                if(!empty($occp_discunt[$current_product_id])){
                    $current_product_discount = $occp_discunt[$current_product_id];
                }
                if(!empty($occp_discunt_type[$current_product_id])){
                    $current_product_discount_type = $occp_discunt_type[$current_product_id];
                }
                
               
                $current_product_exact_price = $this->fbtfw_get_price($current_product_price, $current_product_discount, $current_product_discount_type);
               
               
                $dis_type = get_post_meta( get_the_ID(), 'occp_discount_type' );
                $dis_amt = get_post_meta( get_the_ID(), 'occp_off_per' );


                if(!empty($dis_amt[0][$current_product_id])) {
                    if(get_option('woocommerce_currency_pos') == 'left' || get_option('woocommerce_currency_pos') == 'left_space'){
                        if($dis_type[0][$current_product_id] == "percentage") {
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.$dis_amt[0][$current_product_id].' %</span></div>';
                        }else if($dis_type[0][$current_product_id] == "fixed"){
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.get_woocommerce_currency_symbol().$dis_amt[0][$current_product_id].'</span></div>';
                        }
                    }else{
                        if($dis_type[0][$current_product_id] == "percentage") {
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.$dis_amt[0][$current_product_id].' %</span></div>';
                        }else if($dis_type[0][$current_product_id] == "fixed"){
                            $badge = '<div class="fbtfw_badge"><span><p>off</p>- '.$dis_amt[0][$current_product_id].get_woocommerce_currency_symbol(). '</span></div>';
                        }
                    }
                }
                $images .= '<td class="fbtfw_img sss" image_pro_id="'.$current_product_id.'"><div class="fbtfw_img_div"><a href="' . $current_product_link . '">' . $current_product_image . '</a>'.$badge.'</div></td>';
                
                ob_start();
                ?>
                <li class="fbtfw_each_item">
                    <input type="checkbox" name="proID[]" id="proID_<?php echo $count ?>" class="product_check" value="<?php echo $current_product_id; ?>" price="<?php echo $current_product_exact_price; ?>" checked/>
                    <span class="fbtfw_product_title">
                        <a href="<?php echo $current_product_link; ?>"><?php echo $current_product_title; ?></a>
                    </span>
                    <?php
                        if( $current_product_is_variation ) {
                            $attributes = $product->get_variation_attributes();
                            $variations = array();

                            foreach( $attributes as $key => $attribute ) {
                                $variations[] = $attribute;
                            }

                            if( ! empty( $variations ) )
                            echo '<span class="product-attributes"> &ndash; ' . implode( ', ', $variations ) . '</span>';
                        }
                        // echo product price
                        if(!empty($product->get_price())) { 
                            $price = wc_price($product->get_price()); 
                        }else { 
                            $price = wc_price(0);
                        }
                        echo ' &ndash; <span class="fbtfw_price_old">' . $price . '</span><span class="fbtfw_price_new">('. wc_price($current_product_exact_price) .')</span>';
                    ?>
                </li>
                <?php
                $product_details .= ob_get_clean();
                // increment total
                $total += floatval( $current_product_exact_price );
                $count++;
            }
            ?>
            <input type="hidden" name="formate" value="<?php echo get_woocommerce_currency_symbol(); ?>" class="formate">
            <input type="hidden" name="layout" value="layout2" class="layout">
            <div class="fbtfw_main layout2">
	            <form class="fbtfw_product_form" method="post" action="">
	                <h3><?php echo get_post_meta( get_the_ID(), 'occp_head_txt', true ); ?></h3>
	                <table class="fbtfw_product_images">
	                    <tbody>
	                        <tr>
	                            <?php echo $images; ?>
	                        </tr>
	                    </tbody>
	                </table>
	                <ul class="fbtfw_ul">
	                    <?php echo $product_details; ?>
	                </ul>
	                <div class="fbtfw_cart_div" style="margin-bottom: 10px;">
	                    <div class="fbtfw_price">
	                        <span class="fbtfw_price_label">
	                            <?php echo "Additional Amount : "; ?>
	                        </span>
	                        &nbsp;
	                        <span class="fbtfw_price_total" data-total="<?php echo $total ?>">
	                            <?php echo wc_price( $total ); ?>
	                        </span>
	                    </div>
	                    <input type="submit" class="occp_add_cart_button button" value="<?php echo "Add To Cart"; ?>" name="occp_add_to_cart">
	                </div>
	            </form>
	        </div>
            <?php
            }
        }
    }

    fbtfw_front::fbtfw_instance();
}