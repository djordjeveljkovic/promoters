<section class="w-full">
    {{-- Assuming partials.settings-heading contains its own translatable strings if needed --}}
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('profile.page_heading')" :subheading="__('profile.page_subheading')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('profile.name_label')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('profile.email_label')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('profile.verification.unverified_notice') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('profile.verification.resend_link') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('profile.verification.link_sent_message') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('profile.save_button') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('profile.saved_message') }}
                </x-action-message>
            </div>
        </form>

        {{-- Assuming livewire:settings.delete-user-form handles its own translations if any --}}
        <!-- <livewire:settings.delete-user-form /> -->
    </x-settings.layout>
</section>
