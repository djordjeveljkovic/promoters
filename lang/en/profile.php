<?php // resources/lang/en/profile.php

return [
    // Existing keys from profile & update password sections...
    'page_heading' => 'Profile',
    'page_subheading' => 'Update your name and email address',
    'name_label' => 'Name',
    'email_label' => 'Email',
    'verification' => [
        'unverified_notice' => 'Your email address is unverified.',
        'resend_link' => 'Click here to re-send the verification email.',
        'link_sent_message' => 'A new verification link has been sent to your email address.',
    ],
    'password_page_heading' => 'Update password',
    'password_page_subheading' => 'Ensure your account is using a long, random password to stay secure.',
    'current_password_label' => 'Current password',
    'new_password_label' => 'New password',
    'confirm_password_label' => 'Confirm Password',

    // New keys for Delete Account section
    'delete_account_section_heading' => 'Delete account',
    'delete_account_section_subheading' => 'Permanently delete your account and all of its associated data.',
    'delete_account_button_open_modal' => 'Delete account',
    'delete_account_modal_heading' => 'Are you sure you want to delete your account?',
    'delete_account_modal_warning' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
    'delete_account_password_label' => 'Password', // Password for confirmation
    'delete_account_cancel_button' => 'Cancel',
    'delete_account_confirm_button' => 'Delete account', // Final confirmation button

    // Common buttons and messages (reused)
    'save_button' => 'Save',
    'saved_message' => 'Saved.',
];
