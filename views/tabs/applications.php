<?php
global $wpdb;

$current_user_id = get_current_user_id();

if ( $current_user_id == 0 ) {
    echo '<p>You must be logged in to view your job submissions.</p>';
    return;
}

$table_name = $wpdb->prefix . 'trade_job_submission';
$query = "
    SELECT submissions.*
    FROM $table_name as submissions
    INNER JOIN {$wpdb->posts} as posts
    ON submissions.post_id = posts.ID
    WHERE posts.post_author = %d
    ORDER BY submissions.created_at DESC
";

$results = $wpdb->get_results( $wpdb->prepare( $query, $current_user_id ) );

if ( empty( $results ) ) {
    echo '<p>No Applications found.</p>';
    return;
}
?>
<table class="application-table" border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>Job Title</th>
            <th>Name</th>
            <th>Tradesman Email</th>
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

            // Assuming $row->user_id contains the user ID you want to match against the post meta.
            $user_id = esc_attr( $row->user_id );

            // Query to get the post ID for the specific user
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
                $user_id
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
                            <?php echo esc_html( $row->name ); ?>
                        </a>
                    <?php else : ?>
                        <?php echo esc_html( $row->name );?>
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html( $row->tradesman_email ); ?></td>
                <td><?php echo esc_html( $row->subject ); ?></td>
                <td><?php echo esc_html( $row->field ); ?></td>
                <td><?php echo esc_html( $row->sub_field ); ?></td>
                <td><?php echo esc_html( $row->message ); ?></td>
                <td style="text-align: center;">
                    <select name="job_status" data-job-id="<?php echo esc_attr( $row->post_id ); ?>" class="job-status-dropdown <?php echo esc_attr( $row->status ); ?>">
                        <?php 
                        $status_options = ['hiring', 'hired', 'complete'];
                        foreach ( $status_options as $status ) {
                            $selected = ( $row->status == $status ) ? 'selected="selected"' : '';
                            ?>
                            <option value="<?php echo esc_attr( $status ); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html( ucfirst( $status ) ); ?>
                            </option>
                        <?php } ?>
                    </select><br>
                    <button class="update-status-btn" data-job-id="<?php echo esc_attr( $row->post_id ); ?>">Update</button>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<script>
    var ajax_nonce = "<?php echo wp_create_nonce( 'update_job_status_nonce' ); ?>";
</script>
