jQuery(document).ready(function(){
	jQuery('#occp_select_serach_box').select2({
		data:occp_selected_product_array,
  		ajax: {
    			url: ajaxurl,
    			dataType: 'json',
    			delay: 200,
    			allowClear: true,
    			data: function (params) {
    				
      				return {
        				q: params.term,
        				except: jQuery(this).attr("except"),
        				action: 'occp_search_product_ajax'

      				};
      				
    			},
    			processResults: function( data ) {
					var options = [];
					if ( data ) {
	 					jQuery.each( data, function( index, text ) { 
							options.push( { id: text[0], text: text[1], 'price': text[2]} );
						});
	 				}
					return {
						results: options
					};
				},
				cache: true
		},
		minimumInputLength: 3 
	});
    
	jQuery('#occp_select_serach_box').val(occp_selected_product_ids).trigger('change');;
	jQuery("#occp_select_serach_box").on('change', function (e) { 
    
        var htmla = jQuery("#occp_select_serach_box").select2("data");
        jQuery("#sortable").html("");
        jQuery.each( htmla, function( key, value ) {
        	if(value.discount_type == "percentage"){ 
        		var per = "selected"; 
        	}else{
        		var perp = "selected"; 
        	}
		  	jQuery("#sortable").append('<li class="ui-state-default" id="'+value.id+'"><span class="occp-draggble-icon"></span><span class="product-attributes-drop">'+value.text+' ('+ value.price +') </span><div class="occp_qty_box"><input type="hidden" name="occp_drag_ids[]" value="'+value.id+'"><input type="number" name="occp_off_per['+value.id+']" value="'+value.discount+'"><select name="occp_discount_type['+value.id+']"><option value="fixed" '+perp+'>Fixed</option><option value="percentage" '+per+'>Percentage</option></select></div></li>');
		});
    }); 

    jQuery( function() {
	    jQuery( "#sortable" ).sortable();
	    jQuery( "#sortable" ).disableSelection();
	} );

    jQuery('input[name="rdlayout"]').change(function(){

        var value = jQuery( 'input[name="rdlayout"]:checked' ).val();
        if(value == "layout3"){
            jQuery('.width_div').css('display','block');
            
        }else{
            jQuery('.width_div').css('display','none');
        
        }
    });



    var value = jQuery( 'input[name="rdlayout"]:checked' ).val();
    if(value == "layout3"){
        jQuery('.width_div').css('display','block');
        
    }else{
        jQuery('.width_div').css('display','none');
    
    }
});