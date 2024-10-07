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
                <td><?php echo esc_html( $row->rating ); ?></td>
                <td><?php echo esc_html( $row->date ); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
