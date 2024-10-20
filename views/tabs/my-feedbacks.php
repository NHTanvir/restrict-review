<?php
global $wpdb;

$current_user_id = get_current_user_id();

if ( $current_user_id == 0 ) {
    echo '<p>You must be logged in to view your reviews.</p>';
    return;
}

$table_name = $wpdb->prefix . 'jet_reviews'; 

$query = "
    SELECT post_id, title, content, rating, date
    FROM $table_name
    WHERE author = %d
    ORDER BY date DESC
";

$results = $wpdb->get_results( $wpdb->prepare( $query, $current_user_id ) );

if ( empty( $results ) ) {
    echo '<p>No reviews found.</p>';
    return;
}

// Display the results in a table
?>
<div style="overflow-x:auto;">
    <table class="feedback-table" border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Title</th>
                <th>Content</th>
                <th>Rating</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $results as $row ) { ?>
                <tr>
                    <td><?php echo esc_html( get_post_meta( $row->post_id, 'user_id', true ) ); ?></td>
                    <td><?php echo esc_html( $row->title ); ?></td>
                    <td><?php echo esc_html( $row->content ); ?></td>
                    <td>
                        <div class="jet-reviews-stars jet-reviews-stars--adjuster restrict-reviews-stars">
                            <?php 
                            $full_stars = floor($row->rating / 20);
                            $empty_stars = 5 - $full_stars;
                            
                            for ($i = 0; $i < $full_stars; $i++) {
                                echo '
                                <div class="jet-reviews-star">
                                    <svg viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg" class="e-font-icon-svg e-fas-star" style="width: 10px; height: 10px; fill: #FFC400;">
                                        <path d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path>
                                    </svg>
                                </div>';
                            }

                            // Output empty stars
                            for ($i = 0; $i < $empty_stars; $i++) {
                                echo '
                                <div class="jet-reviews-star">
                                    <svg viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg" class="e-font-icon-svg e-fas-star" style="width: 10px; height: 10px; fill: #FFC400;">
                                        <path d="M528.1 171.5L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6zM388.6 312.3l23.7 138.4L288 385.4l-124.3 65.3 23.7-138.4-100.6-98 139-20.2 62.2-126 62.2 126 139 20.2-100.6 98z"></path>
                                    </svg>
                                </div>';
                            }
                            ?>
                        </div>
                    </td>
                    <td><?php echo esc_html( $row->date ); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
