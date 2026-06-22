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

            {{-- P-070: public profile --}}
            <fieldset class="rounded-lg border border-[color:var(--ds-border)] p-4 space-y-3">
                <legend class="px-2 text-sm font-medium text-[color:var(--ds-text)]">
                    {{ __('promoters.edit_form.public_profile') }}
                </legend>
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $promoter->is_public)) class="ds-checkbox mt-0.5">
                    <span>
                        <span class="text-sm font-medium text-[color:var(--ds-text)]">{{ __('promoters.edit_form.make_profile_public') }}</span>
                        <span class="block text-xs text-[color:var(--ds-text-muted)]">{{ __('promoters.edit_form.public_help_text') }}</span>
                    </span>
                </label>
                <x-ds.field :label="__('promoters.edit_form.bio_label')" name="bio" :hint="__('promoters.edit_form.bio_help_text')" :error="$errors->first('bio')">
                    <textarea name="bio" rows="3" maxlength="500" class="ds-textarea" placeholder="{{ __('promoters.edit_form.bio_placeholder') }}">{{ old('bio', $promoter->bio) }}</textarea>
                </x-ds.field>
            </fieldset>

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
