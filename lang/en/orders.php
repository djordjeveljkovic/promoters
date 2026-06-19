<?php // resources/lang/en/promoter_orders.php

return [
    'page_title' => 'My Ticket Orders',
    'main_heading' => 'My Placed Ticket Orders',
    'create_new_order_button' => 'Create New Order',

    'table' => [
        'header_order_id' => 'Order ID',
        'header_customer_email' => 'Customer Email',
        'header_order_date' => 'Order Date',
        'header_items' => 'Items',
        'header_total_price' => 'Total Price',
        'header_commission_earned' => 'Commission Earned',
        'header_job_status' => 'Job Status',
        'header_actions' => 'Actions',

        'commission_not_calculated' => 'Not Calculated',
        'status_error_tooltip_prefix' => 'Click to view error details:',
        'actions_retry_images_button' => 'Retry Images',
        'actions_retry_images_tooltip_prefix' => 'Retry generating images/QR codes. Failure:',
        'actions_retry_email_button' => 'Retry Email',
        'actions_retry_email_tooltip_prefix' => 'Retry sending email. Failure:',
        'actions_resend_email_button' => 'Resend Email',
        'actions_resend_email_tooltip' => 'Resend confirmation email.',
        'job_failure_reason_label' => 'Job Failure Reason:',
        'no_orders_message' => "You haven't placed any orders yet.",
    ],

    'create_page_title' => 'Create New Ticket Order',
    'create_main_heading' => 'Create New Ticket Order',
    'create_back_to_orders_link' => '&larr; Back to Orders',
    'create_customer_email_label' => 'Customer Email', // The asterisk is part of the HTML structure

    'create_order_items_heading' => 'Order Items',
    'create_ticket_type_label' => 'Ticket Type',
    'create_select_ticket_type_option' => 'Select a ticket type...',
    'create_quantity_label' => 'Quantity',
    'create_add_item_button' => 'Add Item',

    'create_items_table_header_ticket' => 'Ticket',
    'create_items_table_header_quantity' => 'Quantity',
    'create_items_table_header_unit_price' => 'Unit Price',
    'create_items_table_header_subtotal' => 'Subtotal',
    'create_items_table_header_remove' => 'Remove',
    'create_no_items_message' => 'No items added yet.',

    'create_total_label' => 'Total',
    'create_cancel_button' => 'Cancel',
    'create_submit_button' => 'Place Order & Send Tickets',
    // Translatable job statuses
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
