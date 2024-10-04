let pc_modal = (show = true) => {
    if (show) {
        jQuery('#plugin-client-modal').show();
    } else {
        jQuery('#plugin-client-modal').hide();
    }
}
jQuery(function($){
    jQuery(document).ready(function($) {
        $(document).on('click', '.jet-form-builder__submit', function(e) {
            var formData = $(this).closest('form').serialize(); // Serialize the closest form
            $.ajax({
                type: 'POST',
                url: WPPRR.ajaxurl,
                data: formData + '&action=trade_job_submission',
                success: function(response) {
                    console.log( response );
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
});
