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

$notification_table 	= $wpdb->prefix . 'trade_notifications';
$wpdb->update(
    $notification_table,  // Table name
    ['viewed' => 1],      // Data to update
    ['user_id' => $current_user_id, 'type' => 'review']  // Conditions to match user_id and type
);

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
        <?php foreach ( $results as $row ) {

            global $wpdb;
            $user_id    = $row->author;
            $user       = get_user_by('ID', $user_id);
            $username   = $user->user_login;
            $user_url   = get_permalink( $row->post_id );
            

            $query = $wpdb->prepare("
                SELECT posts.ID 
                FROM {$wpdb->posts} AS posts
                INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id 
                WHERE posts.post_type = 'users' 
                AND postmeta.meta_key = 'user_id' 
                AND postmeta.meta_value = %s
                LIMIT 1
            ", $user_id);

            $post_id = $wpdb->get_var($query);
            $user_url = $post_id ? get_permalink($post_id) : '';
            ?>
            <tr>
                <td>
                    <?php if ($user_url): ?>
                        <a href="<?php echo esc_url($user_url); ?>" target="_blank">
                            <?php echo esc_html($username); ?>
                        </a>
                    <?php else: ?>
                        <?php echo esc_html($username); ?>
                    <?php endif; ?>
                </td>
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
