<x-layouts.app :title="__('promoters.create_form.page_title')">
    <x-ds.page-header
        :title="__('promoters.create_form.main_heading')"
        :subtitle="$festival?->displayName() ? __('Festival') . ' · ' . $festival->displayName() : null"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('admin.promoters.index', $festival)" wire:navigate>← {{ __('Back to list') }}</x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.promoters.store', $festival) }}" class="space-y-5">
            @csrf

            <x-ds.field :label="__('promoters.create_form.name_label')" name="name" :required="true" :error="$errors->first('name')">
                <input type="text" name="name" value="{{ old('name', '') }}" class="ds-input" placeholder="{{ __('promoters.create_form.name_placeholder') }}" required>
            </x-ds.field>

            <x-ds.field :label="__('promoters.create_form.email_label')" name="email" :required="true" :error="$errors->first('email')">
                <input type="email" name="email" value="{{ old('email', '') }}" class="ds-input" placeholder="you@example.com" required>
            </x-ds.field>

            <x-ds.field :label="__('promoters.create_form.password_label')" name="password" :hint="__('promoters.create_form.password_help_text_create')" :error="$errors->first('password')">
                <input type="password" name="password" class="ds-input" placeholder="{{ __('promoters.create_form.password_placeholder_create') }}" required>
            </x-ds.field>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-[color:var(--ds-divider)]">
                <x-ds.button variant="secondary" :href="route('admin.promoters.index', $festival)" wire:navigate>{{ __('promoters.create_form.cancel_button') }}</x-ds.button>
                <x-ds.button variant="primary" type="submit">{{ __('promoters.create_form.create_button') }}</x-ds.button>
            </div>
        </form>
    </x-ds.card>
</x-layouts.app>
