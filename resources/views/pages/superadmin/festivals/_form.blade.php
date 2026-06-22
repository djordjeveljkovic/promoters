@csrf
@if (isset($festival) && $festival->exists)
    @method('PUT')
@endif

<div class="grid lg:grid-cols-2 gap-5">
    <div class="space-y-4">
        <x-ds.field :label="__('Name')" name="name" :required="true" :error="$errors->first('name')">
            <input type="text" name="name" value="{{ old('name', $festival->name ?? '') }}" class="ds-input" placeholder="REFEST, Lovefest…" required>
        </x-ds.field>

        <x-ds.field :label="__('Year')" name="year" :required="true" :error="$errors->first('year')">
            <input type="number" name="year" value="{{ old('year', $festival->year ?? date('Y')) }}" class="ds-input" min="2000" max="2100" required>
        </x-ds.field>

        <x-ds.field :label="__('Tagline')" name="tagline" :error="$errors->first('tagline')">
            <input type="text" name="tagline" maxlength="160" value="{{ old('tagline', $festival->tagline ?? '') }}" class="ds-input" placeholder="Where the wild things dance.">
        </x-ds.field>

        <x-ds.field :label="__('Description')" name="description" :error="$errors->first('description')">
            <textarea name="description" rows="4" maxlength="5000" class="ds-textarea">{{ old('description', $festival->description ?? '') }}</textarea>
        </x-ds.field>

        <x-ds.field :label="__('Location')" name="location" :error="$errors->first('location')">
            <input type="text" name="location" maxlength="160" value="{{ old('location', $festival->location ?? '') }}" class="ds-input">
        </x-ds.field>

        <div class="grid grid-cols-2 gap-3">
            <x-ds.field :label="__('Start date')" name="start_date" :error="$errors->first('start_date')">
                <input type="date" name="start_date" value="{{ old('start_date', optional($festival->start_date ?? null)->format('Y-m-d')) }}" class="ds-input">
            </x-ds.field>
            <x-ds.field :label="__('End date')" name="end_date" :error="$errors->first('end_date')">
                <input type="date" name="end_date" value="{{ old('end_date', optional($festival->end_date ?? null)->format('Y-m-d')) }}" class="ds-input">
            </x-ds.field>
        </div>
    </div>

    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <x-ds.field :label="__('Primary colour')" name="primary_color">
                <input type="color" name="primary_color" value="{{ old('primary_color', $festival->primary_color ?? '#ff2d92') }}" class="ds-input h-10 p-1 cursor-pointer">
            </x-ds.field>
            <x-ds.field :label="__('Secondary colour')" name="secondary_color">
                <input type="color" name="secondary_color" value="{{ old('secondary_color', $festival->secondary_color ?? '#5ce1ff') }}" class="ds-input h-10 p-1 cursor-pointer">
            </x-ds.field>
        </div>

        <x-ds.field :label="__('Status')" name="status" :required="true" :error="$errors->first('status')">
            <select name="status" class="ds-select" required>
                @foreach (['draft', 'active', 'archived'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $festival->status ?? 'draft') === $s)>{{ __(ucfirst($s)) }}</option>
                @endforeach
            </select>
        </x-ds.field>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="is_public" value="0">
            <input type="checkbox" name="is_public" value="1" class="ds-checkbox" @checked(old('is_public', $festival->is_public ?? true))>
            <span>{{ __('Visible to the public (landing page)') }}</span>
        </label>

        <x-ds.field :label="__('Logo')" name="logo" :error="$errors->first('logo')">
            @if (!empty($festival->logo_path))
                <img src="{{ asset($festival->logo_path) }}" class="mb-2 h-12 rounded" alt="">
            @endif
            <input type="file" name="logo" accept="image/*" class="block w-full text-sm text-[color:var(--ds-text)] border border-[color:var(--ds-border)] rounded-lg bg-[color:var(--ds-surface)]">
        </x-ds.field>

        <div class="text-xs text-[color:var(--ds-text-muted)]">
            {{ __('Slug') }}: <span class="font-mono">{{ $festival->slug ?? '—' }}</span>
        </div>
    </div>
</div>

<div class="mt-6 pt-4 border-t border-[color:var(--ds-divider)] flex items-center gap-2">
    <button class="ds-btn ds-btn-primary" type="submit">
        {{ isset($festival) && $festival->exists ? __('Save changes') : __('Create festival') }}
    </button>
    <a href="{{ route('superadmin.festivals.index') }}" class="ds-btn ds-btn-secondary" wire:navigate>{{ __('Cancel') }}</a>
</div>
