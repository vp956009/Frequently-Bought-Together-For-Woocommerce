jQuery( document ).ready(function() {

	var price = jQuery(".fbtfw_price_total").attr("data-total");
	var currency = jQuery(".formate").val();
	var total_items = jQuery(".product_check:checked").length;

	if(jQuery(".layout").val() == "layout1") {
    	jQuery(".product .single_add_to_cart_button").html("Add to cart ("+total_items+")");
    } else {
    	jQuery(".occp_add_cart_button").val("Add to cart ("+total_items+")");
    }


	jQuery(".product_check").change(function() {
		var total_items = jQuery(".product_check:checked").length;
		var pro_id = jQuery(this).val();


		if(jQuery(".layout").val() == "layout1") {
			jQuery(".product .single_add_to_cart_button").html("Add to cart ("+total_items+")");
		} else {
        	jQuery(".occp_add_cart_button").val("Add to cart ("+total_items+")");
        }

        var counter = 0;
        var priceForAll = 0;
    	jQuery(".product_check").each(function(index, value) {

    		loop_pro_id = jQuery(this).val();

    		imgPlus = jQuery(".product_check");

    		if(index > 0) {
    			loop_pro_img_id = jQuery(imgPlus[index - 1]).val();
    		} else {
    			loop_pro_img_id = 'false';
    		}


	        if(jQuery(this).is(":checked")) {
	            $price = jQuery(this).attr('price');
	            priceForAll = priceForAll + $price * 1;

	            jQuery(".fbtfw_product_images").find(".fbtfw_img[image_pro_id='"+loop_pro_id+"']").show();

	            if(loop_pro_img_id != 'false') {
	            	jQuery(".fbtfw_product_images").find(".fbtfw_img_plus[fbtfw_imgpls_id='"+loop_pro_img_id+"']").show();
	            }

	        } else {

	        	jQuery(".fbtfw_product_images").find(".fbtfw_img[image_pro_id='"+loop_pro_id+"']").hide();
          		
          		if(loop_pro_img_id != 'false') {
	        		jQuery(".fbtfw_product_images").find(".fbtfw_img_plus[fbtfw_imgpls_id='"+loop_pro_img_id+"']").hide();
	        	}
	        }
	        counter++;
	    });

    	jQuery(".fbtfw_price_total").html(currency + priceForAll.toFixed(2));

    });
});