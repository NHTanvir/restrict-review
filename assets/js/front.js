let pc_modal = ( show = true ) => {
	if(show) {
		jQuery('#plugin-client-modal').show();
	}
	else {
		jQuery('#plugin-client-modal').hide();
	}
}

jQuery(function($){
	$('.jet-form-builder__submit').on('click', function(e) {
		console.log(  'fewfwee' );

        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: WPPRR.ajax_url,
			data: formData + '&action=trade_job_submission', 
            success: function(response) {
                if (response.success) {
                    alert(response.data); // Display success message
                } else {
                    alert(response.data); // Display error message
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
})