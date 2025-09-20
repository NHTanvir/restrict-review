<?php
global $wpdb;

$current_user_id = get_current_user_id();

if ( 0 === $current_user_id ) {
	echo '<p>You must be logged in to view your received feedback.</p>';
	return;
}

$notification_table = $wpdb->prefix . 'trade_notifications';
$wpdb->update(
	$notification_table,
	array( 'viewed' => 1 ),
	array(
		'user_id' => $current_user_id,
		'type'    => 'review',
	)
);

$post_ids = $wpdb->get_col(
	$wpdb->prepare(
		"
		SELECT post_id 
		FROM {$wpdb->postmeta} 
		WHERE meta_key = 'user_id' 
		AND meta_value = %d
		",
		$current_user_id
	)
);

if ( empty( $post_ids ) ) {
	echo '<p>No posts found where you are the owner.</p>';
	return;
}

$table_name   = $wpdb->prefix . 'jet_reviews';
$placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );

$query   = "
	SELECT *
	FROM $table_name
	WHERE post_id IN ($placeholders)
	ORDER BY date DESC
";
$results = $wpdb->get_results( $wpdb->prepare( $query, ...$post_ids ) );

if ( empty( $results ) ) {
	echo '<p>No feedback received for your posts.</p>';
	return;
}

foreach ( $results as $row ) {
	$review_id     = $row->id;
	$user_id       = $row->author;
	$user          = get_user_by( 'ID', $user_id );
	$username      = $user ? $user->user_login : '';
	$user_url      = get_permalink( $row->post_id );
	$submission_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->prefix}jet_review_meta WHERE review_id = %d AND meta_key = 'submission_id'",
			$review_id
		)
	);

	$job_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->prefix}jet_review_meta WHERE review_id = %d AND meta_key = 'job_id'",
			$review_id
		)
	);

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

	$post_id  = $wpdb->get_var( $query );
	$user_url = $post_id ? get_permalink( $post_id ) : '';
	?>
	<table class="feedback-received-table" border="1" cellpadding="10" cellspacing="0">
		<tbody>
			<tr data-review-id="<?php echo esc_attr( $job_id ); ?>" data-submission-id="<?php echo esc_attr( $submission_id ); ?>">
				<td>Author</td>
				<td>
					<?php if ( $user_url ) : ?>
						<a class="user-link" href="<?php echo esc_url( $user_url ); ?>" target="_blank">
							<?php echo esc_html( $username ); ?>
						</a>
					<?php else : ?>
						<?php echo esc_html( $username ); ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>Title</td>
				<td><?php echo esc_html( $row->title ); ?></td>
			</tr>
			<tr>
				<td>Content</td>
				<td><?php echo esc_html( $row->content ); ?></td>
			</tr>
			<tr>
				<td>Rating</td>
				<td>
					<div class="jet-reviews-stars jet-reviews-stars--adjuster restrict-reviews-stars">
						<?php
						$full_stars  = floor( $row->rating / 20 );
						$empty_stars = 5 - $full_stars;

						for ( $i = 0; $i < $full_stars; $i++ ) {
							echo '
							<div class="jet-reviews-star">
								<svg viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg" class="e-font-icon-svg e-fas-star" style="width: 10px; height: 10px; fill: #FFC400;">
									<path d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path>
								</svg>
							</div>';
						}

						for ( $i = 0; $i < $empty_stars; $i++ ) {
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
			</tr>
			<tr>
				<td>Date</td>
				<td><?php echo esc_html( $row->date ); ?></td>
			</tr>
		</tbody>
	</table>
	<?php
}
?>
