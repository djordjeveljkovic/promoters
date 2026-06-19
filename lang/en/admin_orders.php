<?php // resources/lang/en/admin_orders.php

return [
    'page_title' => 'Admin Ticket Orders',
    'main_heading' => 'Sold Tickets',
    'create_order_button' => 'Create Order',

    'filters' => [
        'all_job_statuses_option' => 'All Job Statuses',
        'search_placeholder' => 'Search ID, Email, Promoter...',
        'search_button' => 'Search',
        'clear_button' => 'Clear',
    ],

    'table' => [
        'header_id' => 'ID',
        'header_customer' => 'Customer',
        'header_promoter' => 'Promoter',
        'header_date' => 'Date',
        'header_items' => 'Items',
        'header_total' => 'Total',
        'header_paid' => 'Paid',
        'header_commission' => 'Commission',
        'header_job_status' => 'Job Status',
        'header_actions' => 'Actions',

        'promoter_not_available' => 'N/A',
        'commission_not_calculated' => '---',
        'status_tooltip_failure_prefix' => 'Click to view failure:',
        'action_view' => 'View',
        'action_generate_images' => 'Generate Images',
        'action_generate_images_tooltip_failure_prefix' => 'Retry Image Generation. Failure:',
        'action_send_mail' => 'Send Mail',
        'action_send_mail_tooltip_base' => 'Resend/Retry Email.',
        'action_send_mail_tooltip_additional_failure_prefix' => 'Failure:', // To be appended in Blade
        'job_failure_reason_label' => 'Job Failure Reason:',
        'no_orders_header' => 'No orders found',
        'no_orders_message' => 'No orders match your current criteria or none have been placed yet.',
    ],

    'statuses' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'failed' => 'Failed',
        'blocked' => 'Blocked',
        'completed' => 'Completed',
        'sent' => 'Sent',
        'unknown' => 'N/A', // Fallback for undefined status
    ],
];
