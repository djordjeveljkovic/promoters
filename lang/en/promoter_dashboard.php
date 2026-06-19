<?php // resources/lang/en/promoter_dashboard.php

return [
    'page_title' => 'My Dashboard',
    'main_heading' => 'My Promoter Dashboard',

    'financial_overview' => [
        'heading' => 'My Financial Overview',
        'total_earnings_commission' => 'My Total Earnings (Commission)',
        'all_time_label' => 'All time',
        'gross_sales_value' => 'Gross Value of Tickets Sold',
        'gross_sales_subtext' => 'All time, from successful orders',
        'amount_owed_to_organizers' => 'Amount I Owe to Organizers',
        'organizer_owes_credit_subtext' => '(Organizer owes you / Credit)',
        'amount_owed_calculation_subtext' => 'Calculated: Gross Sales - My Payments - My Commission',
        'amount_paid_to_organizers' => "Amount I've Paid to Organizers",
        'earnings_last_30_days' => 'My Earnings (Last 30 Days)',
    ],

    'general_performance' => [
        'heading' => 'My General Performance',
        'total_orders_all_time' => 'My Total Orders (All Time)',
        'tickets_sold_all_time' => 'My Tickets Sold (All Time)',
        'orders_last_30_days' => 'My Orders (Last 30 Days)',
        'tickets_sold_last_30_days' => 'My Tickets Sold (Last 30 Days)',
    ],

    'top_ticket_sales_by_type' => [
        'heading' => 'My Top Ticket Sales by Type',
        'no_data' => "You haven't sold any tickets yet, or no completed sales data is available.",
        'table_header_ticket_type' => 'Ticket Type',
        'table_header_quantity_sold' => 'Quantity Sold By You',
        'table_header_gross_revenue' => 'Gross Revenue Generated',
    ],

    'order_statuses' => [
        'heading' => 'My Order Statuses (Job Status)',
        'no_orders_found' => 'No orders found.',
        // If individual statuses (e.g., "Pending", "Completed") need translation,
        // add keys here or in a shared 'statuses.php' lang file, e.g.:
        // 'pending' => 'Pending',
        // 'completed' => 'Completed',
    ],

    'recent_orders' => [
        'heading' => 'My Recent Orders', // Assuming this section might be used
        'no_data' => 'You have no recent orders.',
        // Add table headers here if the recent orders table is fleshed out
    ],
];
