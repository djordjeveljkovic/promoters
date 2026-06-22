<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('profile.password_page_heading')" :subheading="__('profile.password_page_subheading')">
        <form wire:submit="updatePassword" class="space-y-5">
            <x-ds.field :label="__('profile.current_password_label')" name="current_password" :required="true">
                <input wire:model="current_password" type="password" required autocomplete="current-password" class="ds-input">
            </x-ds.field>
            <x-ds.field :label="__('profile.new_password_label')" name="password" :required="true">
                <input wire:model="password" type="password" required autocomplete="new-password" class="ds-input">
            </x-ds.field>
            <x-ds.field :label="__('profile.confirm_password_label')" name="password_confirmation" :required="true">
                <input wire:model="password_confirmation" type="password" required autocomplete="new-password" class="ds-input">
            </x-ds.field>

            <div class="flex items-center gap-3">
                <button type="submit" class="ds-btn ds-btn-primary">{{ __('profile.save_button') }}</button>
                <x-action-message class="text-sm text-emerald-600" on="password-updated">
                    {{ __('profile.saved_message') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
