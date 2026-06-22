@csrf
@if (isset($festival) && $festival->exists)
    @method('PUT')
@endif

<div class="grid lg:grid-cols-2 gap-5">
    <div class="space-y-4">
        <x-ds.field :label="__('festivals.name_label')" name="name" :required="true" :error="$errors->first('name')">
            <input type="text" name="name" value="{{ old('name', $festival->name ?? '') }}" class="ds-input" placeholder="REFEST, Lovefest…" required>
        </x-ds.field>

        <x-ds.field :label="__('festivals.year_label')" name="year" :required="true" :error="$errors->first('year')">
            <input type="number" name="year" value="{{ old('year', $festival->year ?? date('Y')) }}" class="ds-input" min="2000" max="2100" required>
        </x-ds.field>

        <x-ds.field :label="__('festivals.tagline_label')" name="tagline" :error="$errors->first('tagline')">
            <input type="text" name="tagline" maxlength="160" value="{{ old('tagline', $festival->tagline ?? '') }}" class="ds-input" placeholder="Where the wild things dance.">
        </x-ds.field>

        <x-ds.field :label="__('festivals.description_label')" name="description" :error="$errors->first('description')">
            <textarea name="description" rows="4" maxlength="5000" class="ds-textarea">{{ old('description', $festival->description ?? '') }}</textarea>
        </x-ds.field>

        <x-ds.field :label="__('festivals.location_label')" name="location" :error="$errors->first('location')">
            <input type="text" name="location" maxlength="160" value="{{ old('location', $festival->location ?? '') }}" class="ds-input">
        </x-ds.field>

        <div class="grid grid-cols-2 gap-3">
            <x-ds.field :label="__('festivals.start_date_label')" name="start_date" :error="$errors->first('start_date')">
                <input type="date" name="start_date" value="{{ old('start_date', optional($festival->start_date ?? null)->format('Y-m-d')) }}" class="ds-input">
            </x-ds.field>
            <x-ds.field :label="__('festivals.end_date_label')" name="end_date" :error="$errors->first('end_date')">
                <input type="date" name="end_date" value="{{ old('end_date', optional($festival->end_date ?? null)->format('Y-m-d')) }}" class="ds-input">
            </x-ds.field>
        </div>
    </div>

    <div class="space-y-4">
        <div class="text-[11px] uppercase tracking-wider font-semibold text-[color:var(--ds-text-muted)]">{{ __('festivals.theme_heading') }}</div>
        <div class="grid grid-cols-2 gap-3">
            <x-ds.field :label="__('festivals.primary_colour')" name="primary_color" :hint="__('festivals.primary_colour_hint')">
                <div class="flex items-stretch gap-2">
                    <input type="color" id="primaryColor" name="primary_color" value="{{ old('primary_color', $festival->primary_color ?? '#4f46e5') }}" class="h-10 w-12 rounded-md border border-[color:var(--ds-border)] cursor-pointer p-0.5 bg-[color:var(--ds-surface)]">
                    <input type="text" id="primaryColorHex" value="{{ old('primary_color', $festival->primary_color ?? '#4f46e5') }}" maxlength="7" pattern="^#[0-9a-fA-F]{6}$" class="ds-input flex-1 font-mono" aria-label="{{ __('festivals.primary_hex') }}">
                </div>
            </x-ds.field>
            <x-ds.field :label="__('festivals.secondary_colour')" name="secondary_color" :hint="__('festivals.secondary_colour_hint')">
                <div class="flex items-stretch gap-2">
                    <input type="color" id="secondaryColor" name="secondary_color" value="{{ old('secondary_color', $festival->secondary_color ?? '#818cf8') }}" class="h-10 w-12 rounded-md border border-[color:var(--ds-border)] cursor-pointer p-0.5 bg-[color:var(--ds-surface)]">
                    <input type="text" id="secondaryColorHex" value="{{ old('secondary_color', $festival->secondary_color ?? '#818cf8') }}" maxlength="7" pattern="^#[0-9a-fA-F]{6}$" class="ds-input flex-1 font-mono" aria-label="{{ __('festivals.secondary_hex') }}">
                </div>
            </x-ds.field>
        </div>

        {{-- Live preview: the brand mark + a few sample components
             re-themed with the picked colours, so the admin can see the
             effect immediately. --}}
        <div class="rounded-lg border border-[color:var(--ds-border)] overflow-hidden" id="themePreviewWrapper">
            <div class="px-3 py-2 text-[11px] uppercase tracking-wider font-semibold text-[color:var(--ds-text-muted)] border-b border-[color:var(--ds-divider)] bg-[color:var(--ds-bg-subtle)]">
                {{ __('festivals.live_preview') }}
            </div>
            <div id="themePreview" class="p-4 space-y-3" style="--festival-primary: {{ old('primary_color', $festival->primary_color ?? '#4f46e5') }}; --festival-secondary: {{ old('secondary_color', $festival->secondary_color ?? '#818cf8') }};">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-md flex items-center justify-center font-semibold text-white" style="background: linear-gradient(135deg, var(--festival-primary) 0%, var(--festival-secondary) 100%);">{{ mb_substr(old('name', $festival->name ?? 'F'), 0, 1) }}</div>
                    <div class="text-sm font-semibold">{{ old('name', $festival->name ?? __('festivals.sample_festival_name')) }}</div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="ds-btn ds-btn-primary">{{ __('festivals.sample_primary_btn') }}</button>
                    <button type="button" class="ds-btn ds-btn-secondary">{{ __('festivals.sample_secondary_btn') }}</button>
                    <span class="ds-badge ds-badge-accent">{{ __('festivals.sample_badge') }}</span>
                    <a href="#" class="text-sm font-medium" style="color: var(--festival-primary);">{{ __('festivals.sample_link') }} →</a>
                </div>
            </div>
        </div>

        {{-- One-tap palettes --}}
        <div>
            <div class="text-[11px] uppercase tracking-wider font-semibold text-[color:var(--ds-text-muted)] mb-2">{{ __('festivals.quick_palettes') }}</div>
            <div class="grid grid-cols-6 gap-2" id="paletteGrid">
                @php
                    $palettes = [
                        ['#ef4444', '#fca5a5', __('festivals.palettes.red')],
                        ['#f59e0b', '#fcd34d', __('festivals.palettes.amber')],
                        ['#10b981', '#6ee7b7', __('festivals.palettes.green')],
                        ['#0ea5e9', '#7dd3fc', __('festivals.palettes.sky')],
                        ['#6366f1', '#a5b4fc', __('festivals.palettes.indigo')],
                        ['#ec4899', '#f9a8d4', __('festivals.palettes.pink')],
                        ['#8b5cf6', '#c4b5fd', __('festivals.palettes.violet')],
                        ['#14b8a6', '#5eead4', __('festivals.palettes.teal')],
                        ['#f43f5e', '#fda4af', __('festivals.palettes.rose')],
                        ['#84cc16', '#bef264', __('festivals.palettes.lime')],
                        ['#0f172a', '#475569', __('festivals.palettes.slate')],
                        ['#dc2626', '#fbbf24', __('festivals.palettes.refest')],
                    ];
                @endphp
                @foreach ($palettes as $p)
                    <button type="button" class="palette-swatch group relative h-10 rounded-md border border-[color:var(--ds-border)] overflow-hidden" data-primary="{{ $p[0] }}" data-secondary="{{ $p[1] }}" title="{{ $p[2] }}">
                        <span class="absolute inset-0" style="background: linear-gradient(135deg, {{ $p[0] }} 0%, {{ $p[1] }} 100%);"></span>
                        <span class="absolute inset-x-0 bottom-0 text-[9px] font-semibold text-white px-1 py-0.5 text-center opacity-0 group-hover:opacity-100 transition-opacity" style="background: rgba(0,0,0,0.4);">{{ $p[2] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <x-ds.field :label="__('festivals.status_label')" name="status" :required="true" :error="$errors->first('status')">
            <select name="status" class="ds-select" required>
                @foreach (['draft', 'active', 'archived'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $festival->status ?? 'draft') === $s)>{{ __(ucfirst($s)) }}</option>
                @endforeach
            </select>
        </x-ds.field>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="is_public" value="0">
            <input type="checkbox" name="is_public" value="1" class="ds-checkbox" @checked(old('is_public', $festival->is_public ?? true))>
            <span>{{ __('festivals.is_public_label') }}</span>
        </label>

        <x-ds.field :label="__('festivals.logo_label')" name="logo" :error="$errors->first('logo')">
            @if (!empty($festival->logo_path))
                <img src="{{ asset($festival->logo_path) }}" class="mb-2 h-12 rounded" alt="">
            @endif
            <input type="file" name="logo" accept="image/*" class="block w-full text-sm text-[color:var(--ds-text)] border border-[color:var(--ds-border)] rounded-lg bg-[color:var(--ds-surface)]">
        </x-ds.field>

        <div class="text-xs text-[color:var(--ds-text-muted)]">
            {{ __('festivals.slug_label') }}: <span class="font-mono">{{ $festival->slug ?? '—' }}</span>
        </div>
    </div>
</div>

<div class="mt-6 pt-4 border-t border-[color:var(--ds-divider)] flex items-center gap-2">
    <button class="ds-btn ds-btn-primary" type="submit">
        {{ isset($festival) && $festival->exists ? __('festivals.save_changes') : __('festivals.create') }}
    </button>
    <a href="{{ route('superadmin.festivals.index') }}" class="ds-btn ds-btn-secondary" wire:navigate>{{ __('festivals.cancel') }}</a>
</div>

<script>
    (function () {
        const picker    = document.getElementById('primaryColor');
        const hexIn     = document.getElementById('primaryColorHex');
        const secPicker = document.getElementById('secondaryColor');
        const secHexIn  = document.getElementById('secondaryColorHex');
        const preview   = document.getElementById('themePreview');
        const palette   = document.getElementById('paletteGrid');
        if (!picker || !preview) return;

        const isHex = (v) => /^#[0-9a-fA-F]{6}$/.test(v);

        const apply = () => {
            const p = picker.value;
            const s = secPicker.value;
            preview.style.setProperty('--festival-primary', p);
            preview.style.setProperty('--festival-secondary', s);
            hexIn.value   = p;
            secHexIn.value = s;
        };

        // Native picker -> preview
        picker.addEventListener('input', apply);
        secPicker.addEventListener('input', apply);

        // Hex text -> native picker (must be valid 6-digit hex)
        hexIn.addEventListener('input', () => {
            const v = hexIn.value.trim();
            if (isHex(v)) { picker.value = v; apply(); }
        });
        secHexIn.addEventListener('input', () => {
            const v = secHexIn.value.trim();
            if (isHex(v)) { secPicker.value = v; apply(); }
        });

        // One-tap palettes
        if (palette) {
            palette.querySelectorAll('.palette-swatch').forEach((btn) => {
                btn.addEventListener('click', () => {
                    picker.value    = btn.dataset.primary;
                    secPicker.value = btn.dataset.secondary;
                    apply();
                });
            });
        }
    })();
</script>
