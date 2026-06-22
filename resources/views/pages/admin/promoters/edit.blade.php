<x-layouts.app :title="__('promoters.edit.page_title')">
    <x-ds.page-header
        :title="__('promoters.edit.main_heading')"
        :subtitle="$promoter->name . ' · ' . ($festival?->displayName() ?? '')"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('admin.promoters.index', $festival)" wire:navigate>
                ← {{ __('Back to list') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.promoters.update', ['festival' => $festival->slug, 'id' => $promoter->id]) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-ds.field :label="__('promoters.create_form.name_label')" name="name" :required="true" :error="$errors->first('name')">
                <input type="text" name="name" value="{{ old('name', $promoter->name) }}" class="ds-input" required>
            </x-ds.field>

            <x-ds.field :label="__('promoters.create_form.email_label')" name="email" :required="true" :error="$errors->first('email')">
                <input type="email" name="email" value="{{ old('email', $promoter->email) }}" class="ds-input" required>
            </x-ds.field>

            <x-ds.field :label="__('promoters.edit_form.password_label')" name="password" :hint="__('promoters.edit_form.password_help_text')" :error="$errors->first('password')">
                <input type="password" name="password" class="ds-input" minlength="8">
            </x-ds.field>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-[color:var(--ds-divider)]">
                <x-ds.button variant="secondary" :href="route('admin.promoters.index', $festival)" wire:navigate>
                    {{ __('promoters.edit_form.cancel_button') }}
                </x-ds.button>
                <x-ds.button variant="primary" type="submit">
                    {{ __('promoters.edit_form.update_button') }}
                </x-ds.button>
            </div>
        </form>
    </x-ds.card>
</x-layouts.app>
