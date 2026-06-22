<?php // resources/lang/en/alert.php

return [
    // Ticket Type Alerts
    'ticket_type_created_success' => 'Ticket Type created successfully!',
    'ticket_type_create_failed'   => 'Failed to create Ticket Type. Please try again. Error: :error',

    'ticket_type_updated_success' => 'Ticket Type updated successfully!',
    'ticket_type_update_failed'   => 'Failed to update Ticket Type. Please try again. Error: :error',

    'ticket_type_deleted_success' => 'Ticket Type deleted successfully!',
    'ticket_type_delete_failed'   => 'Failed to delete Ticket Type. Error: :error',

    'failed_to_create_directory' => 'Failed to create directory: :error',
    'failed_to_move_photo'       => 'Failed to move uploaded photo: :error',
    'update_failed_create_directory' => 'Update failed: Could not create directory: :error',
    'update_failed_move_photo'       => 'Update failed: Could not move new photo: :error',

    'order_created_success' => 'Order created successfully! Processing initiated for order .',
    'order_created_failure' => 'Failed to create order due to an internal error: :message',
    'image_generation_requeued' => 'Image generation for order has been re-queued.',
    'image_generation_cannot_rerun' => 'Image generation for order cannot be rerun from its current state (:status).',
    'email_requeued_success' => 'Email for order has been re-queued for sending.',
    'email_cannot_resent' => 'Email for order cannot be re-sent from its current state (:status).',

    'payment_amount_updated' => 'Payment amount updated.',
    'ticket_codes_not_found' => 'None of the selected ticket codes were found for this order.',
    'no_tickets_to_process' => 'No tickets available to process for this order.',
    'no_qr_codes_found' => 'No QR code images were found for the specified tickets.',
    'zip_creation_failed' => 'Could not create the ZIP file. Please check server permissions or logs.',

    'promoter_updated_success' => 'Promoter updated successfully!',
    'auth_required' => 'Authentication required.',
    'ticket_type_created_success' => 'Ticket Type created successfully!',
    'ticket_type_create_failed' => 'Failed to create Ticket Type. Please try again. Error: :message',
    'ticket_type_updated_success' => 'Ticket Type updated successfully!',
    'ticket_type_update_failed' => 'Failed to update Ticket Type. Error: :message',
    'ticket_type_deleted_success' => 'Ticket Type deleted successfully!',
    'ticket_type_delete_failed' => 'Failed to delete Ticket Type. Error: :message',

    'password_update_success' => 'Password updated successfully!',
    'password_update_failed' => 'Failed to update password. Please try again.',
    'validation_failed_check_fields' => 'Validation failed. Please check the input fields for errors.',

    /* ---- Festival management ---- */
    'festival_created'              => 'Festival created successfully.',
    'festival_updated'              => 'Festival updated successfully.',
    'festival_deleted'              => 'Festival deleted.',
    'festival_cannot_delete_active' => 'Only draft festivals can be deleted. Archive it first.',
    'festival_archived'             => 'Festival archived.',
    'festival_already_archived'     => 'Festival is already archived.',
    'festival_restored'             => 'Festival restored.',
    'festival_not_archived'         => 'Festival is not archived.',
    'festival_made_public'          => 'Festival is now visible on the public landing page.',
    'festival_made_private'         => 'Festival is now hidden from the public landing page.',
    'bulk_resend_queued'            => 'Queued :count email(s) for re-sending.',
    'assignment_added'              => 'User assigned to the festival.',
    'assignment_removed'            => 'User removed from the festival.',

    /* ---- Promoter manager / sub-promoter commission ---- */
    'sub_promoter_created'                  => 'Sub-promoter :name created. You can now set their commission rates.',
    'commissions_saved'                    => 'Commission overrides saved.',
    'sub_commissions_saved'                 => 'Sub-promoter commission rates saved.',
    'sub_commission_cannot_exceed_manager'  => 'Sub-promoter commission cannot exceed your own commission of :manager RSD for this ticket type.',
    'promoter_promoted_to_manager'          => ':name is now a promoter manager. They can create their own sub-promoters.',
    'promoter_demoted'                       => ':name was demoted to a regular promoter.',

    /* ---- User management ---- */
    'user_created'           => 'User created successfully.',
    'user_updated'           => 'User updated successfully.',
    'user_deleted'           => 'User deleted.',
    'user_cannot_delete_self'=> 'You cannot delete your own account.',
    'user_cannot_delete_admin' => 'Admin and superadmin users cannot be deleted from here.',

    /* ---- Promoter CRUD ---- */
    'promoter_created'       => 'Promoter created and assigned to the festival.',
    'promoter_deleted'       => 'Promoter removed from the festival.',

    /* ---- Authorization ---- */
    'no_festival_access'     => 'You do not have access to this festival.',
    'role_unauthorized'      => 'You are not authorized to perform this action.',
    'no_festival_in_scope'   => 'This action requires a festival to be selected. Please navigate via a festival page.',
];
