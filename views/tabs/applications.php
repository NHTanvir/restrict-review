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

// Start building the HTML table
?>
<table class="application-table" border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th style="width:75px">Job ID</th>
            <th>Name</th>
            <th>Tradesman Email</th>
            <th>Subject</th>
            <th>Field</th>
            <th>Sub Field</th>
            <th>Message</th>
            <th>Status</th> <!-- Status column header -->
        </tr>
    </thead>
    <tbody>
        <?php
        $status_options = ['pending', 'hired', 'rejected', 'complete'];
        foreach ( $results as $row ) {
            ?>
            <tr>
                <td><?php echo esc_html( $row->post_id ); ?></td>
                <td><?php echo esc_html( $row->name ); ?></td>
                <td><?php echo esc_html( $row->tradesman_email ); ?></td>
                <td><?php echo esc_html( $row->subject ); ?></td>
                <td><?php echo esc_html( $row->field ); ?></td>
                <td><?php echo esc_html( $row->sub_field ); ?></td>
                <td><?php echo esc_html( $row->message ); ?></td>
                <td>
                    <select name="job_status" data-job-id="<?php echo esc_attr( $row->post_id ); ?>" class="job-status-dropdown">
                        <?php foreach ( $status_options as $status ) {
                            $selected = ( $row->status == $status ) ? 'selected="selected"' : '';
                            ?>
                            <option value="<?php echo esc_attr( $status ); ?>" <?php echo $selected; ?>><?php echo esc_html( ucfirst( $status ) ); ?></option>
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
