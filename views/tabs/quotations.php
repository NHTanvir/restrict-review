<?php
global $wpdb;

$current_user_id = get_current_user_id();

if ( 0 === $current_user_id ) {
	echo '<p>You must be logged in to view your quotations.</p>';
	return;
}

$year_filter   = isset( $_GET['year'] ) ? intval( $_GET['year'] ) : '';
$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

$years = $wpdb->get_col(
	$wpdb->prepare(
		"
		SELECT DISTINCT YEAR(created_at) 
		FROM {$wpdb->prefix}trade_job_submission 
		WHERE author_id = %d
		",
		$current_user_id
	)
);

$where_clauses = [ 'author_id = %d' ];
$params        = [ $current_user_id ];

if ( $year_filter ) {
	$where_clauses[] = 'YEAR(created_at) = %d';
	$params[]        = $year_filter;
}

if ( $status_filter ) {
	$where_clauses[] = 'status = %s';
	$params[]        = $status_filter;
}

$query   = "
	SELECT *
	FROM {$wpdb->prefix}trade_job_submission
	WHERE " . implode( ' AND ', $where_clauses ) . '
	ORDER BY created_at DESC
';
$query   = $wpdb->prepare( $query, $params );
$results = $wpdb->get_results( $query );

$wpdb->query(
	$wpdb->prepare(
		"
		UPDATE {$wpdb->prefix}trade_job_submission
		SET viewed = %d
		WHERE author_id = %d
		",
		1,
		$current_user_id
	)
);
?>

<form method="get" class="filter-form">
	<label for="year">Filter by Year:</label>
	<select name="year" id="year">
		<option value=""><?php esc_html_e( 'Select Year', 'textdomain' ); ?></option>
		<?php foreach ( $years as $year ) : ?>
			<option value="<?php echo esc_attr( $year ); ?>" <?php selected( $year_filter, $year ); ?>>
				<?php echo esc_html( $year ); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<label for="status">Filter by Status:</label>
	<select name="status" id="status">
		<option value=""><?php esc_html_e( 'Select Status', 'textdomain' ); ?></option>
		<option value="hired" <?php selected( $status_filter, 'hired' ); ?>><?php esc_html_e( 'Hired', 'textdomain' ); ?></option>
		<option value="hiring" <?php selected( $status_filter, 'hiring' ); ?>><?php esc_html_e( 'Hiring', 'textdomain' ); ?></option>
		<option value="complete" <?php selected( $status_filter, 'complete' ); ?>><?php esc_html_e( 'Complete', 'textdomain' ); ?></option>
	</select>

	<input type="submit" value="<?php esc_attr_e( 'Filter', 'textdomain' ); ?>">
</form>

<?php
if ( empty( $results ) ) {
	echo '<p>No quotations found.</p>';
	return;
}

foreach ( $results as $row ) {
	$job_url     = get_permalink( $row->post_id );
	$title       = get_the_title( $row->post_id );
	$title       = str_replace( 'Private: ', '', $title );
	$user_id     = esc_attr( $row->user_id );
	$query       = $wpdb->prepare(
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
	$post_id     = $wpdb->get_var( $query );
	$user_url    = $post_id ? get_permalink( $post_id ) : '';
	$post_status = $row->post_id ? get_post_status( $row->post_id ) : '';
	?>
	<table class="application-table" border="1" cellpadding="10" cellspacing="0" style="margin-bottom: 20px;">
		<tbody>
			<tr>
				<td>Job Title</td>
				<td>
					<?php if ( 'private' === $post_status ) : ?>
						<?php echo esc_html( $title ); ?>
					<?php else : ?>
						<a href="<?php echo esc_url( $job_url ); ?>" target="_blank">
							<?php echo esc_html( $title ); ?>
						</a>
					<?php endif; ?>
				</td>
			</tr>
			<tr data-review-id="<?php echo esc_attr( $row->post_id ); ?>" data-submission-id="<?php echo esc_attr( $row->id ); ?>">
				<td>Tradesperson Name</td>
				<td>
					<?php if ( $user_url ) : ?>
						<a class="user-link" href="<?php echo esc_url( $user_url ); ?>">
							<?php echo esc_html( $row->name ); ?>
						</a>
					<?php else : ?>
						<?php echo esc_html( $row->name ); ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>Tradesperson Email</td>
				<td><?php echo esc_html( $row->tradesman_email ); ?></td>
			</tr>
			<tr>
				<td>Message</td>
				<td><?php echo esc_html( $row->message ); ?></td>
			</tr>
			<tr>
				<td>Status</td>
				<td>
					<select 
						name="job_status" 
						data-job-id="<?php echo esc_attr( $row->id ); ?>"
						class="job-status-dropdown <?php echo esc_attr( $row->status ); ?>"
					>
						<?php
						$status_options = [ 'hiring', 'hired', 'complete', 'closed' ];
						foreach ( $status_options as $status ) :
							$selected = ( $row->status === $status ) ? 'selected="selected"' : '';
							?>
							<option value="<?php echo esc_attr( $status ); ?>" <?php echo $selected; ?>>
								<?php echo esc_html( ucfirst( $status ) ); ?>
							</option>
						<?php endforeach; ?>
					</select><br>
					<button 
						class="update-status-btn" 
						data-job-id="<?php echo esc_attr( $row->id ); ?>" 
					>
						<?php esc_html_e( 'Update', 'textdomain' ); ?>
					</button>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}
?>

<script>
	var ajax_nonce = "<?php echo esc_js( wp_create_nonce( 'update_job_status_nonce' ) ); ?>";
</script>
