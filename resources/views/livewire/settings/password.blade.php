<section class="w-full">
    {{-- Assuming this partial contains its own translations or is static --}}
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('profile.password_page_heading')" :subheading="__('profile.password_page_subheading')">
        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('profile.current_password_label')"
                type="password"
                required
                autocomplete="current-password"
            />
            <flux:input
                wire:model="password"
                :label="__('profile.new_password_label')"
                type="password"
                required
                autocomplete="new-password"
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('profile.confirm_password_label')"
                type="password"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    {{-- Reusing the general save button key --}}
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('profile.save_button') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{-- Reusing the general saved message key --}}
                    {{ __('profile.saved_message') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
