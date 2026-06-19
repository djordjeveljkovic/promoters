<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Password extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        Log::info("updatePassword method started for user: " . Auth::id()); // Temporary
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', PasswordRule::defaults(), 'confirmed'],
            ]);

            Log::info("Validation passed for user: " . Auth::id()); // Temporary

            $updateSuccess = Auth::user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            if ($updateSuccess) {
                Session::flash('success', __('alert.password_update_success'));
                Log::info('Session after flash in updatePassword: ', session()->all());
                Log::info(sprintf("User password updated. User ID: %s, Email: %s", Auth::id(), Auth::user()->email));
                $this->reset('current_password', 'password', 'password_confirmation');
                $this->dispatch('password-updated');
                $this->dispatch('alert-shown');
            } else {
                Log::error("User password update failed for User ID: " . Auth::id() . " after validation.");
                Session::flash('error', __('alert.password_update_failed'));
                $this->dispatch('alert-shown');
            }
        } catch (ValidationException $e) {
            Log::warning("Validation failed for user: " . Auth::id() . ". Errors: " . json_encode($e->errors())); // Temporary
            Session::flash('error', __('alert.validation_failed_check_fields'));
            $this->reset('current_password', 'password', 'password_confirmation');
            $this->dispatch('alert-shown');
            throw $e;
        } catch (\Exception $e) {
            Log::error("Unexpected error during password update for User ID: " . Auth::id() . ". Error: " . $e->getMessage());
            Session::flash('error', __('alert.password_update_failed'));
            $this->dispatch('alert-shown');
        }
    }
}
