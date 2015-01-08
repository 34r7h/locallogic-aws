if(typeof $ == 'undefined'){
	var $ = jQuery;
}
(function($) {
    $(document).ready(function () { 
	
	   "use strict";       
		
		$(document).on("click", "#xoouserultra-register-btn", function(e) {
			
			e.preventDefault();
			
			var validation = true;
			
			
			$( ".required" ).each(function( index ) {				
				
				
				var field_txt = jQuery(this).attr("value");
				var field_id = jQuery(this).attr("id");
				if(field_txt=="")
				{
					validation = false;
					
					alert("field empy" + field_id);
					
					jQuery("#uultra-val-message-"+field_id).slideDown();
				
				}else{
					
					jQuery("#uultra-val-message-"+field_id).slideUp();
					
				
				}
			
			});
			
			if(validation)
			{
				alert("validated")
				
				$( "#xoouserultra-registration-form" ).submit();
			
			}
			
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
			 

				
        });
		
       
    }); //END READY
})(jQuery);

function hidde_noti (div_d)
{
		jQuery("#"+div_d).slideUp();		
		
}

