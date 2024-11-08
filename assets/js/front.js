let pc_modal = (show = true) => {
    if (show) {
        jQuery("#plugin-client-modal").show();
    } else {
        jQuery("#plugin-client-modal").hide();
    }
};
jQuery(function ($) {
    $(document).ready(function () {
        var unviewedCount = WPPRR.unviewedCount;
        var unviewedHiresComplete = WPPRR.unviewedHiresComplete;
        var unviewedFeedback = WPPRR.unviewedFeedback;
        var unreviewedJobs = WPPRR.unreviewedJobs;

        var menuItemLink = $( '.jet-profile-menu__item-link:contains("Quotations")');
        var ApplicationsmenuItemLink = $( '.jet-profile-menu__item-link:contains("Applications")' );
        var FeedbackReceivedMenuItemLink = $('.jet-profile-menu__item-link:contains("Feedback Received")');



        if (unviewedCount > 0) {
            menuItemLink.css("position", "relative");
            menuItemLink.attr("data-count", unviewedCount);
        } else {
            menuItemLink.addClass("hide-unviewed-count");
        }

        if (unviewedHiresComplete > 0) {
            ApplicationsmenuItemLink.css("position", "relative");
            ApplicationsmenuItemLink.attr("data-count", unviewedHiresComplete);
        } else {
            ApplicationsmenuItemLink.addClass("hide-unviewed-count");
        }

        if (unviewedFeedback > 0) {
            FeedbackReceivedMenuItemLink.css("position", "relative");
            FeedbackReceivedMenuItemLink.attr("data-count", unviewedFeedback);
        } else {
            FeedbackReceivedMenuItemLink.addClass("hide-unviewed-count");
        }


        $(document).on("click", ".jet-form-builder__submit", function (e) {
            var formData = $(this).closest("form").serialize();
            formData += "&action=trade_job_submission";
            formData += "&_wpnonce=" + WPPRR._wpnonce;

            $.ajax({
                type: "POST",
                url: WPPRR.ajaxurl,
                data: formData,
                success: function (response) {
                    if (response.success) {
                        console.log(response.data);
                    } else {
                        console.log(response.data);
                    }
                },
                error: function () {
                    console.log("An error occurred. Please try again.");
                },
            });
        });
        $(".update-status-btn").on("click", function (e) {
            e.preventDefault();

            var button = $(this);
            var jobId = button.data("job-id");
            var status = $('select[data-job-id="' + jobId + '"]').val();
            var select = button.siblings("select");

            $.ajax({
                url: WPPRR.ajaxurl,
                method: "POST",
                data: {
                    action: "update_job_status",
                    job_id: jobId,
                    job_status: status,
                    _wpnonce: WPPRR._wpnonce,
                },
                success: function (response) {
                    if (response.success) {
                        if (response.data.status) {
                            select
                                .removeClass()
                                .addClass("job-status-dropdown");
                            select.addClass(response.data.status);
                        }
                        alert("Job status updated successfully!");
                    } else {
                        alert("Failed to update job status.");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX error:", status, error);
                    alert("An error occurred while updating the job status.");
                },
            });
        });

        if (WPPRR.unreviewedJobs && WPPRR.unreviewedJobs.length) {

            var ReviewMenu;

            if (WPPRR.is_client == 1) {
                ReviewMenu = $('.jet-profile-menu__item-link:contains("Quotations")');
            } else {
                ReviewMenu = $('.jet-profile-menu__item-link:contains("Applications")');
            }
        
            if (WPPRR.unreviewedJobs.length) {
                ReviewMenu.css("position", "relative");
                ReviewMenu.attr("data-count", WPPRR.unreviewedJobs.length);
                ReviewMenu.removeClass("hide-unviewed-count");
            } else {
                ReviewMenu.addClass("hide-unviewed-count");
            }

            
            WPPRR.unreviewedJobs.forEach(function (jobId) {
                var $tr = $('tr[data-review-id="' + jobId + '"]');
                if ($tr.length) {
                    var $secondTd = $tr.find("td").eq(1);
                    var $message = $(
                        '<span class="review-message">Click username to give Review</span>'
                    ).css({
                        position: "absolute",
                        left: "100px",
                        top: "-15px",
                        zIndex: "10",
                        backgroundColor: "#ff0000",
                        color: "white",
                        padding: "2px 5px",
                        borderRadius: "5px",
                    });

                    $secondTd.prepend($message);
                }
            });
        }
    });
});
