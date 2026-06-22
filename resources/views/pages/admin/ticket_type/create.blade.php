<x-layouts.app :title="__('ticket_types.create_form.page_title')">

    <x-ds.page-header
        :title="__('ticket_types.create_form.main_heading')"
        :subtitle="$festival?->displayName() ? __('Festival') . ' · ' . $festival->displayName() : null"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('admin.ticket-types.index', $festival)" wire:navigate>
                ← {{ __('ticket_types.create_form.back_to_list_link') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    @if (session('error'))
        <x-ds.alert variant="danger" class="mb-4">{{ session('error') }}</x-ds.alert>
    @endif

    <x-ds.card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.ticket-types.store', $festival) }}" enctype="multipart/form-data" class="space-y-5" id="createTicketTypeForm">
            @csrf

            <x-ds.field :label="__('ticket_types.create_form.name_label')" name="name" :required="true" :error="$errors->first('name')">
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="ds-input" placeholder="{{ __('ticket_types.create_form.name_placeholder') }}" required>
            </x-ds.field>

            <x-ds.field :label="__('ticket_types.create_form.price_label')" name="price" :required="true" :error="$errors->first('price')">
                <div class="relative">
                    <input type="number" name="price" id="price" value="{{ old('price') }}" class="ds-input pr-12" placeholder="{{ __('ticket_types.create_form.price_placeholder') }}" step="0.01" min="0" required>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-[color:var(--ds-text-muted)] pointer-events-none">
                        {{ __('ticket_types.create_form.price_currency_suffix') }}
                    </div>
                </div>
            </x-ds.field>

            <x-ds.field :label="__('ticket_types.create_form.photo_label')" name="photo" :hint="__('ticket_types.create_form.photo_help_text')" :error="$errors->first('photo')">
                <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" class="block w-full text-sm text-[color:var(--ds-text)] border border-[color:var(--ds-border)] rounded-lg bg-[color:var(--ds-surface)] file:mr-3 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:text-sm file:font-medium file:bg-[color:var(--ds-accent-soft)] file:text-[color:var(--ds-accent-text)] hover:file:bg-[color:var(--ds-accent-soft-2)]">
            </x-ds.field>

            <fieldset class="rounded-lg border border-[color:var(--ds-border)] p-4 space-y-3">
                <legend class="px-2 text-sm font-medium text-[color:var(--ds-text)]">
                    {{ __('ticket_types.create_form.qr_fieldset_legend') }} <span class="text-rose-500">*</span>
                </legend>
                <p class="text-xs text-[color:var(--ds-text-muted)]">{{ __('ticket_types.create_form.qr_help_text') }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <x-ds.field :label="__('ticket_types.create_form.qr_x_label')" name="qr_coordinate_x" :required="true" :error="$errors->first('qr_coordinate_x')">
                        <input type="number" name="qr_coordinate_x" id="qr_coordinate_x" value="{{ old('qr_coordinate_x', old('qr_coordinates.x')) }}" class="ds-input qr-input" min="0" required>
                    </x-ds.field>
                    <x-ds.field :label="__('ticket_types.create_form.qr_y_label')" name="qr_coordinate_y" :required="true" :error="$errors->first('qr_coordinate_y')">
                        <input type="number" name="qr_coordinate_y" id="qr_coordinate_y" value="{{ old('qr_coordinate_y', old('qr_coordinates.y')) }}" class="ds-input qr-input" min="0" required>
                    </x-ds.field>
                    <x-ds.field :label="__('ticket_types.create_form.qr_size_label')" name="qr_coordinate_size" :required="true" :error="$errors->first('qr_coordinate_size')">
                        <input type="number" name="qr_coordinate_size" id="qr_coordinate_size" value="{{ old('qr_coordinate_size', old('qr_coordinates.size')) }}" class="ds-input qr-input" min="10" required>
                    </x-ds.field>
                </div>
                <input type="hidden" name="qr_coordinates" id="qr_coordinates_json" value='{{ old("qr_coordinates", "{\"x\":0,\"y\":0,\"size\":100}") }}'>
                @error('qr_coordinates') <p class="ds-error">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="rounded-lg border border-[color:var(--ds-border)] p-4 space-y-3">
                <legend class="px-2 text-sm font-medium text-[color:var(--ds-text)]">
                    {{ __('ticket_types.create_form.commissions_fieldset_legend') }} <span class="text-rose-500">*</span>
                </legend>
                <div id="commission-tiers-container" class="space-y-3">
                    @php
                        $oldCommissions = old('commissions', [['min_sold' => '', 'max_sold' => '', 'commission_amount' => '']]);
                        if (empty($oldCommissions)) $oldCommissions = [['min_sold' => '', 'max_sold' => '', 'commission_amount' => '']];
                    @endphp
                    @foreach($oldCommissions as $index => $commission)
                        <div class="commission-tier-row grid grid-cols-1 sm:grid-cols-7 gap-3 items-end pb-3 border-b border-[color:var(--ds-divider)] last:border-b-0 last:pb-0">
                            <div class="sm:col-span-2">
                                <x-ds.field :label="__('ticket_types.create_form.commissions_min_sold_label')" :name="'commissions_'.$index.'_min_sold'" :required="true" :error="$errors->first('commissions.'.$index.'.min_sold')">
                                    <input type="number" name="commissions[{{ $index }}][min_sold]" value="{{ $commission['min_sold'] ?? '' }}" class="ds-input" min="0" required>
                                </x-ds.field>
                            </div>
                            <div class="sm:col-span-2">
                                <x-ds.field :label="__('ticket_types.create_form.commissions_max_sold_label')" :name="'commissions_'.$index.'_max_sold'" :error="$errors->first('commissions.'.$index.'.max_sold')">
                                    <input type="number" name="commissions[{{ $index }}][max_sold]" value="{{ $commission['max_sold'] ?? '' }}" class="ds-input" min="0">
                                </x-ds.field>
                            </div>
                            <div class="sm:col-span-2">
                                <x-ds.field :label="__('ticket_types.create_form.commissions_amount_label')" :name="'commissions_'.$index.'_commission_amount'" :required="true" :error="$errors->first('commissions.'.$index.'.commission_amount')">
                                    <input type="number" name="commissions[{{ $index }}][commission_amount]" value="{{ $commission['commission_amount'] ?? '' }}" class="ds-input" step="0.01" min="0" required>
                                </x-ds.field>
                            </div>
                            <div class="sm:col-span-1">
                                @if($index > 0 || count($oldCommissions) > 1)
                                    <button type="button" class="remove-commission-tier-btn ds-btn ds-btn-danger-ghost ds-btn-icon w-full" title="{{ __('ticket_types.create_form.commissions_remove_button') }}">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-commission-tier-btn" class="ds-btn ds-btn-secondary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    {{ __('ticket_types.create_form.commissions_add_tier_button') }}
                </button>
                @error('commissions') <p class="ds-error">{{ $message }}</p> @enderror
            </fieldset>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-[color:var(--ds-divider)]">
                <x-ds.button variant="secondary" :href="route('admin.ticket-types.index', $festival)" wire:navigate>
                    {{ __('ticket_types.create_form.cancel_button') }}
                </x-ds.button>
                <x-ds.button variant="primary" type="submit">
                    {{ __('ticket_types.create_form.create_button') }}
                </x-ds.button>
            </div>
        </form>
    </x-ds.card>

    @include('pages.admin.ticket_type._script')
</x-layouts.app>
