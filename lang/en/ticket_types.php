<?php // resources/lang/en/admin_ticket_types.php

return [
    'page_title'        => 'Manage Ticket Types',
    'main_heading'      => 'Ticket Types',
    'create_button'     => 'Create Ticket Type',

    'table' => [
        'header_name'           => 'Name',
        'header_price'          => 'Price',
        'header_photo'          => 'Photo',
        'header_actions'        => 'Actions',

        'no_photo'              => 'No photo',
        'action_edit'           => 'Edit',
        'action_delete'         => 'Delete',
        'delete_confirm_message'=> 'Are you sure you want to delete this ticket type? This action cannot be undone.',

        'no_data_message'       => 'No ticket types found.',
    ],
    // New keys for Create New Ticket Type page
    'create_form' => [
        'page_title'        => 'Create New Ticket Type',
        'main_heading'      => 'Create New Ticket Type',
        'back_to_list_link' => '&larr; Back to List',

        'name_label'        => 'Name',
        'name_placeholder'  => 'e.g., General Admission, VIP Pass',

        'price_label'       => 'Price',
        'price_currency_suffix' => 'RSD', // As you specified you use RSD
        'price_placeholder' => '0.00',

        'photo_label'       => 'Ticket Image/Photo',
        'photo_help_text'   => 'Optional. Max file size: 2MB. Allowed types: JPG, PNG, WEBP, GIF, SVG.',

        'qr_fieldset_legend'    => 'QR Code Coordinates (on Ticket Image)',
        'qr_help_text'          => 'Define X, Y coordinates (in pixels from top-left) and size (in pixels) for placing the QR code.',
        'qr_x_label'            => 'X Coordinate',
        'qr_x_placeholder'      => 'e.g., 120',
        'qr_y_label'            => 'Y Coordinate',
        'qr_y_placeholder'      => 'e.g., 210',
        'qr_size_label'         => 'QR Size (px)',
        'qr_size_placeholder'   => 'e.g., 100',

        'commissions_fieldset_legend'   => 'Ticket Commission Tiers',
        'commissions_min_sold_label'    => 'Min Sold',
        'commissions_min_sold_placeholder'=> 'e.g., 1',
        'commissions_max_sold_label'    => 'Max Sold',
        'commissions_max_sold_placeholder'=> 'e.g., 10 (empty for no limit)',
        'commissions_amount_label'      => 'Commission Amount', // "Zarada"
        'commissions_amount_placeholder'=> 'e.g., 1.50',
        'commissions_remove_button'     => 'Remove',
        'commissions_add_tier_button'   => 'Add Commission Tier',

        'cancel_button'     => 'Cancel',
        'create_button'     => 'Create Ticket Type', // Submit button for this form
    ],
    'currency_symbol'   => 'RSD', // Currency symbol
    'form_shared' => [ // Shared labels and placeholders for create/edit forms
        'name_label' => 'Name',
        'name_placeholder' => 'e.g., General Admission, VIP Pass',
        'price_label' => 'Price',
        'price_placeholder' => '0.00',
        'photo_label' => 'Ticket Image/Photo',
        'photo_help_text' => 'Optional. Max file size: 2MB. Allowed types: JPG, PNG, WEBP, GIF, SVG.',
        'qr_fieldset_legend' => 'QR Code Coordinates (on Ticket Image)',
        'qr_help_text' => 'Define X, Y coordinates (in pixels from top-left) and size (in pixels) for placing the QR code.',
        'qr_x_label' => 'X Coordinate',
        'qr_x_placeholder' => 'e.g., 120',
        'qr_y_label' => 'Y Coordinate',
        'qr_y_placeholder' => 'e.g., 210',
        'qr_size_label' => 'QR Size (px)',
        'qr_size_placeholder' => 'e.g., 100',
        'commissions_fieldset_legend' => 'Ticket Commission Tiers',
        'commissions_min_sold_label' => 'Min Sold',
        'commissions_min_sold_placeholder'=> 'e.g., 1',
        'commissions_max_sold_label' => 'Max Sold',
        'commissions_max_sold_placeholder'=> 'e.g., 10 (empty for no limit)',
        'commissions_amount_label' => 'Commission Amount',
        'commissions_amount_placeholder'=> 'e.g., 1.50',
        'commissions_remove_button' => 'Remove',
        'commissions_add_tier_button' => 'Add Commission Tier',
        'cancel_button' => 'Cancel',
    ],

    'create_form' => [
        'page_title' => 'Create New Ticket Type',
        'main_heading' => 'Create New Ticket Type',
        'back_to_list_link' => '&larr; Back to List',
        'create_button' => 'Create Ticket Type',
        // Specific help texts or placeholders for create if different can go here
    ],

    // Keys for Edit Ticket Type page
    'edit_form' => [
        'page_title'        => 'Edit Ticket Type',
        'main_heading'      => 'Edit Ticket Type: :name', // :name will be replaced
        'back_to_list_link' => '&larr; Back to List',

        'name_label'        => 'Name',
        'name_placeholder'  => 'e.g., General Admission, VIP Pass',

        'price_label'       => 'Price',
        'price_currency_suffix' => 'RSD',
        'price_placeholder' => '0.00',

        'photo_label'       => 'Ticket Image/Photo',
        'current_photo_label'=> 'Current Image:',
        'no_current_photo'  => 'No current image uploaded.',
        'photo_help_text_edit' => 'Optional. Upload a new image to replace the current one. Max file size: 2MB. Allowed types: JPG, PNG, WEBP, GIF, SVG.',

        'qr_fieldset_legend'    => 'QR Code Coordinates (on Ticket Image)',
        'qr_help_text'          => 'Define X, Y coordinates (in pixels from top-left) and size (in pixels) for placing the QR code.',
        'qr_x_label'            => 'X Coordinate',
        'qr_x_placeholder'      => 'e.g., 120',
        'qr_y_label'            => 'Y Coordinate',
        'qr_y_placeholder'      => 'e.g., 210',
        'qr_size_label'         => 'QR Size (px)',
        'qr_size_placeholder'   => 'e.g., 100',

        'commissions_fieldset_legend'   => 'Ticket Commission Tiers',
        'commissions_min_sold_label'    => 'Min Sold',
        'commissions_min_sold_placeholder'=> 'e.g., 1',
        'commissions_max_sold_label'    => 'Max Sold',
        'commissions_max_sold_placeholder'=> 'e.g., 10 (empty for no limit)',
        'commissions_amount_label'      => 'Commission Amount',
        'commissions_amount_placeholder'=> 'e.g., 1.50',
        'commissions_remove_button'     => 'Remove',
        'commissions_add_tier_button'   => 'Add Commission Tier',

        'cancel_button'     => 'Cancel',
        'update_button'     => 'Update Ticket Type',
    ],
];
