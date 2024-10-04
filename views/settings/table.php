<?php
use Codexpert\Plugin\Table;

global $wpdb;

$results = $wpdb->get_results("
    SELECT id, post_id, name, tradesman_email, subject, field, sub_field, message, created_at, status
    FROM {$wpdb->prefix}trade_job_submission
    ORDER BY created_at DESC
");

// Prepare the data structure based on the actual table data
$data = [];

foreach ($results as $row) {
    $data[] = [
        'id'                => $row->id,                     // Order ID
        'post_id'           => $row->post_id,                // Post ID
        'name'              => $row->name,                   // Name
        'tradesman_email'   => $row->tradesman_email,        // Tradesman Email
        'subject'           => $row->subject,                // Subject (products)
        'field'             => $row->field,                  // Field (order total or relevant info)
        'sub_field'         => $row->sub_field,              // Sub field (if applicable)
        'message'           => $row->message,                // Message (can represent order details)
        'created_at'       => $row->created_at,              // Created timestamp
        'status'            => $row->status,                 // Payment status or job status
    ];
}

// Configure the plugin settings
$config = [
    'per_page'      => 10,
    'columns'       => [
        'id'                => __( 'ID #', 'plugin-client' ),
        'post_id'          => __( 'Post ID', 'plugin-client' ),
        'name'             => __( 'Name', 'plugin-client' ),
        'tradesman_email'  => __( 'Tradesman Email', 'plugin-client' ),
        'subject'          => __( 'Subject', 'plugin-client' ),
        'field'            => __( 'field', 'plugin-client' ),
        'sub_field'        => __( 'Sub Field', 'plugin-client' ), 
        'message'          => __( 'Message', 'plugin-client' ),   
        'created_at'       => __( 'Time', 'plugin-client' ),
        'status'           => __( 'Status', 'plugin-client' ),
    ],
    'sortable'      => ['id', 'post_id', 'name', 'tradesman_email', 'subject', 'field', 'status', 'created_at'],
    'orderby'       => 'created_at',
    'order'         => 'desc',
    'data'          => $data,  // Populate with actual data
];


$table = new Table( $config );
echo '<form method="post">';
$table->prepare_items();
$table->search_box( 'Search', 'search' );
$table->display();
echo '</form>';