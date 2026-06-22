<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('profile.page_heading')" :subheading="__('profile.page_subheading')">
        <form wire:submit="updateProfileInformation" class="space-y-5 w-full">
            <x-ds.field :label="__('profile.name_label')" name="name" :required="true">
                <input wire:model="name" type="text" required autofocus autocomplete="name" class="ds-input">
            </x-ds.field>

            <x-ds.field :label="__('profile.email_label')" name="email" :required="true">
                <input wire:model="email" type="email" required autocomplete="email" class="ds-input">
            </x-ds.field>

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                <x-ds.alert variant="warning">
                    {{ __('profile.verification.unverified_notice') }}
                    <a href="#" wire:click.prevent="resendVerificationNotification" class="font-medium underline ml-1">
                        {{ __('profile.verification.resend_link') }}
                    </a>
                </x-ds.alert>
                @if (session('status') === 'verification-link-sent')
                    <x-ds.alert variant="success">
                        {{ __('profile.verification.link_sent_message') }}
                    </x-ds.alert>
                @endif
            @endif

            <div class="flex items-center gap-3">
                <button type="submit" class="ds-btn ds-btn-primary">{{ __('profile.save_button') }}</button>
                <x-action-message class="text-sm text-emerald-600" on="profile-updated">
                    {{ __('profile.saved_message') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
