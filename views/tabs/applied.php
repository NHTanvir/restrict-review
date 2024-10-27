<?php
global $wpdb;

$current_user_id = get_current_user_id();

if ($current_user_id == 0) {
    echo '<p>You must be logged in to view your quotations.</p>';
    return;
}

$year_filter = isset($_GET['year']) ? intval($_GET['year']) : '';
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Fetch distinct years for the filter
$years = $wpdb->get_col($wpdb->prepare("
    SELECT DISTINCT YEAR(created_at) 
    FROM {$wpdb->prefix}trade_job_submission 
    WHERE user_id = %d
", $current_user_id));

$where_clauses = ["user_id = %d"];
$params = [$current_user_id];

if ($year_filter) {
    $where_clauses[] = "YEAR(created_at) = %d";
    $params[] = $year_filter;
}

// Filter by status if provided
if ($status_filter) {
    $where_clauses[] = "status = %s";
    $params[] = $status_filter;
}

// Create the final SQL query
$query = "
    SELECT *
    FROM {$wpdb->prefix}trade_job_submission
    WHERE " . implode(' AND ', $where_clauses) . "
    ORDER BY created_at DESC
";

// Count the total results
$total_results = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*)
    FROM {$wpdb->prefix}trade_job_submission
    WHERE " . implode(' AND ', $where_clauses), $params)); // Use prepare with params

// Prepare and execute the final query
$query = $wpdb->prepare($query, $params);
$results = $wpdb->get_results($query);


?>

<!-- Filters -->
<form method="get" class="filter-form">
    <label for="year">Filter by Year:</label>
    <select name="year" id="year">
        <option value="">Select Year</option>
        <?php foreach ($years as $year): ?>
            <option value="<?php echo esc_attr($year); ?>" <?php selected($year_filter, $year); ?>>
                <?php echo esc_html($year); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="status">Filter by Status:</label>
    <select name="status" id="status">
        <option value="">Select Status</option>
        <option value="hired" <?php selected($status_filter, 'hired'); ?>>Hired</option>
        <option value="hiring" <?php selected($status_filter, 'hiring'); ?>>Hiring</option>
        <option value="complete" <?php selected($status_filter, 'complete'); ?>>Complete</option>
    </select>

    <input type="submit" value="Filter" class="filter-btn">
</form>

<?php
if (empty($results)) {
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
                <th>Author Email</th>
                <th>Message</th>
                <th>Status</th> 
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($results as $row) {
                $job_url = get_permalink($row->post_id);
                $title = get_the_title($row->post_id);
                $author_id = esc_attr($row->author_id);
                $author_info = get_userdata($author_id);
                $author_email = $author_info->user_email;
                $author_name = $author_info->user_nicename;

                $query = $wpdb->prepare("
                    SELECT posts.ID 
                    FROM {$wpdb->posts} AS posts
                    INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id 
                    WHERE posts.post_type = 'users' 
                    AND postmeta.meta_key = 'user_id' 
                    AND postmeta.meta_value = %s
                    LIMIT 1
                ", $author_id);

                $post_id = $wpdb->get_var($query);
                $post_url = $post_id ? get_permalink($post_id) : '';

                ?>
                <tr data-review-id="<?php echo esc_attr($row->post_id); ?>">
                    <td>
                        <a href="<?php echo esc_url($job_url); ?>" target="_blank">
                            <?php echo esc_html($title); ?>
                        </a>
                    </td>
                    <td>
                        <?php if ($post_url): ?>
                            <a href="<?php echo esc_url($post_url); ?>">
                                <?php echo esc_html($author_name); ?>
                            </a>
                        <?php else: ?>
                            <?php echo esc_html($author_name); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($author_email); ?></td>
                    <td><?php echo esc_html($row->message); ?></td>
                    <td style="text-align: center;" class="job-status <?php echo esc_attr($row->status); ?>">
                        <?php echo esc_html($row->status); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    var ajax_nonce = "<?php echo wp_create_nonce('update_job_status_nonce'); ?>";
</script>
