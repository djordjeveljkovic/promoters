<?php // resources/lang/en/admin_dashboard.php

return [
    'page_title' => 'Admin Dashboard',
    'main_heading' => 'Admin Analytics Dashboard',

    'overall_performance' => [
        'heading' => 'Overall Performance',
        'total_revenue_all_time' => 'Total Revenue (All Time)',
        'total_orders_all_time' => 'Total Orders (All Time)',
        'tickets_sold_completed_all_time' => 'Tickets Sold (Completed Orders)',
        'revenue_last_30_days' => 'Revenue (Last 30 Days)',
    ],

    'top_ticket_types' => [
        'heading' => 'Top Ticket Types (By Quantity Sold)',
        'no_data' => 'No ticket sales data available.',
        'table_header_type_name' => 'Type Name',
        'table_header_quantity_sold' => 'Quantity Sold',
        'table_header_est_revenue' => 'Est. Revenue',
    ],

    'user_ticket_stats' => [
        'heading' => 'User & Ticket Stats',
        // Assuming $role variable will be 'admin', 'promoter', 'buyer' etc.
        // You can translate role names themselves if they are not just slugs
        // e.g., 'role_admin' => 'Administrators', 'role_promoter' => 'Promoters'
        // For now, we'll handle the suffix.
        'role_count_suffix' => 's:', // To make "Admin" -> "Admins:"
        'active_tickets' => 'Active Tickets:',
        'inactive_tickets' => 'Inactive Tickets:',
    ],

    'order_statuses' => [
        'heading' => 'Order Statuses',
        // Status names (e.g., "pending", "completed") are often slugs from the database.
        // ucfirst() is used in Blade. If you need to translate "Pending", "Completed" itself,
        // you'd need keys like 'status_pending' => 'Pending', 'status_completed' => 'Completed'.
        // For now, only the heading is explicitly translated here.
    ],

    'top_promoter_performance' => [
        'heading' => 'Top Promoter Performance (Completed Orders)',
        'no_data' => 'No promoter sales data available.',
        'table_header_promoter' => 'Promoter',
        'table_header_email' => 'Email',
        'table_header_orders_generated' => 'Orders Generated',
        'table_header_revenue_generated' => 'Revenue Generated',
    ],

    'recent_orders' => [
        'heading' => 'Recent Orders',
        'no_data' => 'No recent orders.',
        'table_header_order_id' => 'Order ID',
        'table_header_customer_email' => 'Customer Email',
        'table_header_promoter' => 'Promoter',
        'table_header_items' => 'Items',
        'table_header_total' => 'Total',
        'table_header_status' => 'Status',
        'table_header_date' => 'Date',
    ],
];
