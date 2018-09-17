jQuery(document).ready(function () {
    
	jQuery('#side-menu a[href*="modal"]').click(function(){
		jQuery('#modal').modal();
	})
	
	jQuery("form .btn-primary").click(function(){
		var title = jQuery("#add-new-task #tasktitle").val();
		var freelancer = jQuery("#add-new-task #freelancer").val();
		if ( title == '' ){		
			jQuery("#add-new-task #tasktitle").css({"border-color": '#FF0000'});
			jQuery("#add-new-task #tasktitle").focus();			
			return false;		
		} 
		var data = {
			'action': 'send_modal_window',
			title:title,
			freelancer:freelancer,						
		};
		jQuery.post(send_modal_form.ajaxurl, data, function() {	
			jQuery("#add-new-task #tasktitle").removeAttr("style");
			alert('Success!');
			location.reload();
		});		
	});
	
	
});

