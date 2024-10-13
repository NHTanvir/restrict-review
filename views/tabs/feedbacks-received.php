<?php
global $wpdb;

$current_user_id = get_current_user_id();

if ( $current_user_id == 0 ) {
    echo '<p>You must be logged in to view your received feedback.</p>';
    return;
}

// Get all post IDs where the current user is the owner (meta_key 'user_id')
$post_ids = $wpdb->get_col( $wpdb->prepare( "
    SELECT post_id 
    FROM {$wpdb->postmeta} 
    WHERE meta_key = 'user_id' 
    AND meta_value = %d
", $current_user_id ) );

if ( empty( $post_ids ) ) {
    echo '<p>No posts found where you are the owner.</p>';
    return;
}

// Now query wp_jet_reviews to get feedback on these posts
$table_name = $wpdb->prefix . 'jet_reviews';
$placeholders = implode(',', array_fill(0, count($post_ids), '%d')); // Prepare the placeholders for IN query

$query = "
    SELECT post_id,author, title, content, rating, date
    FROM $table_name
    WHERE post_id IN ($placeholders)
    ORDER BY date DESC
";

// Prepare the query with dynamic post IDs
$results = $wpdb->get_results( $wpdb->prepare( $query, ...$post_ids ) );

if ( empty( $results ) ) {
    echo '<p>No feedback received for your posts.</p>';
    return;
}

// Display the results in a table
?>
<table class="feedback-received-table" border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>author</th>
            <th>Title</th>
            <th>Content</th>
            <th>Rating</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ( $results as $row ) { ?>
            <tr>
                <td><?php echo esc_html( $row->author ); ?></td>
                <td><?php echo esc_html( $row->title ); ?></td>
                <td><?php echo esc_html( $row->content ); ?></td>
                <td><?php echo esc_html( $row->rating ); ?></td>
                <td><?php echo esc_html( $row->date ); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
