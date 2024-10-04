let pc_modal = (show = true) => {
    if (show) {
        jQuery('#plugin-client-modal').show();
    } else {
        jQuery('#plugin-client-modal').hide();
    }
}
jQuery(function($){
    $(document).ready(function() {
        $(document).on('click', '.jet-form-builder__submit', function(e) {
            var formData = $(this).closest('form').serialize(); 
            formData += '&action=trade_job_submission'; 
            formData += '&_wpnonce=' + WPPRR._wpnonce; 

            $.ajax({
                type: 'POST',
                url: WPPRR.ajaxurl, 
                data: formData,
                success: function(response) {
                    if (response.success) {
                        console.log(response.data);
                    } else {
                        console.log(response.data);
                    }
                },
                error: function() {
                    console.log('An error occurred. Please try again.');
                }
            });
        });
    });
});
