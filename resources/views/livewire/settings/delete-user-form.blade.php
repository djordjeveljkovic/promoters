<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('profile.delete_account_section_heading') }}</flux:heading>
        <flux:subheading>{{ __('profile.delete_account_section_subheading') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('profile.delete_account_button_open_modal') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('profile.delete_account_modal_heading') }}</flux:heading>

                <flux:subheading>
                    {{ __('profile.delete_account_modal_warning') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('profile.delete_account_password_label')" type="password" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('profile.delete_account_cancel_button') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('profile.delete_account_confirm_button') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
