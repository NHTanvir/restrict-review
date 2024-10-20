<?php
global $wpdb;

$current_user_id = get_current_user_id();

if ( $current_user_id == 0 ) {
    echo '<p>You must be logged in to view your quotations.</p>';
    return;
}

$table_name = $wpdb->prefix . 'trade_job_submission';
$query = $wpdb->prepare("
    SELECT *
    FROM $table_name
    WHERE user_id = %d
    ORDER BY created_at DESC
", $current_user_id);

$results = $wpdb->get_results($query);


if ( empty( $results ) ) {
    echo '<p>No applications found.</p>';
    return;
}
?>
<div style="overflow-x:auto;">
    <table class="application-table" border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Job Title</th>
                <th>Author Name</th>
                <th>Subject</th>
                <th>Field</th>
                <th>Sub Field</th>
                <th>Message</th>
                <th>Status</th> 
            </tr>
        </thead>
        <tbody>
            <?php
            $status_options = ['pending', 'hired', 'rejected', 'complete'];
            foreach ( $results as $row ) {
                $job_url = get_permalink( $row->post_id );
                $title = get_the_title( $row->post_id );

                global $wpdb;

                $author_id = esc_attr( $row->author_id );

                $query = $wpdb->prepare(
                    "
                    SELECT posts.ID 
                    FROM {$wpdb->posts} AS posts
                    INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id 
                    WHERE posts.post_type = 'users' 
                    AND postmeta.meta_key = 'user_id' 
                    AND postmeta.meta_value = %s
                    LIMIT 1
                    ",
                    $author_id
                );

                $post_id = $wpdb->get_var( $query );

                $post_url = $post_id ? get_permalink( $post_id ) : '';

                ?>
                <tr>
                    <td>
                        <a href="<?php echo esc_url( $job_url ); ?>" target="_blank">
                            <?php echo esc_html( $title ); ?>
                        </a>
                    </td>
                    <td>
                        <?php if ( $post_url ) : ?>
                            <a href="<?php echo esc_url( $post_url ); ?>">
                                <?php echo esc_html( get_post_meta( $post_id, 'user_name', true ) ); ?>
                            </a>
                        <?php else : ?>
                            <?php echo esc_html( $row->name );?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html( $row->subject ); ?></td>
                    <td><?php echo esc_html( $row->field ); ?></td>
                    <td><?php echo esc_html( $row->sub_field ); ?></td>
                    <td><?php echo esc_html( $row->message ); ?></td>
                    <td style="text-align: center;" class="job-status <?php echo $row->status; ?>">
                            <?php echo $row->status; ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    var ajax_nonce = "<?php echo wp_create_nonce( 'update_job_status_nonce' ); ?>";
</script>
