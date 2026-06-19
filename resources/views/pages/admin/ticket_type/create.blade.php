<x-layouts.app :title="__('ticket_types.create_form.page_title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="max-w-2xl mx-auto w-full rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('ticket_types.create_form.main_heading') }}</h1>
                <a href="{{ route('admin.ticket-types.index', $festival) }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">{!! __('ticket_types.create_form.back_to_list_link') !!}</a>
            </div>

            {{-- Display any session errors from the controller --}}
            @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.ticket-types.store', $festival) }}" enctype="multipart/form-data" class="space-y-6" id="createTicketTypeForm">
                @csrf

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.create_form.name_label') }} <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                           placeholder="{{ __('ticket_types.create_form.name_placeholder') }}"
                           required />
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Price --}}
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.create_form.price_label') }} <span class="text-red-500">*</span></label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <input type="number"
                               name="price"
                               id="price"
                               value="{{ old('price') }}"
                               class="block w-full rounded-md border-gray-300 bg-gray-50 pl-7 pr-12 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                               placeholder="{{ __('ticket_types.create_form.price_placeholder') }}"
                               step="0.01"
                               min="0"
                               required />
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm" id="currency-usd">{{ __('ticket_types.create_form.price_currency_suffix') }}</span>
                        </div>
                    </div>
                    @error('price')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Photo --}}
                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.create_form.photo_label') }}</label>
                    <input type="file"
                           name="photo"
                           id="photo"
                           accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                           class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-l-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-indigo-50 file:text-indigo-700
                                  hover:file:bg-indigo-100 dark:file:bg-indigo-600 dark:file:text-indigo-50 dark:hover:file:bg-indigo-500" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('ticket_types.create_form.photo_help_text') }}</p>
                    @error('photo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- QR Coordinates --}}
                <fieldset class="rounded-md border border-gray-300 p-4 dark:border-gray-600">
                    <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 px-2">{{ __('ticket_types.create_form.qr_fieldset_legend') }} <span class="text-red-500">*</span></legend>
                    <p class="mb-3 text-xs text-gray-500 dark:text-gray-400 px-2">{{ __('ticket_types.create_form.qr_help_text') }}</p>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-3">
                        <div>
                            <label for="qr_coordinate_x" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.create_form.qr_x_label') }} <span class="text-red-500">*</span></label>
                            <input type="number"
                                   name="qr_coordinate_x"
                                   id="qr_coordinate_x"
                                   value="{{ old('qr_coordinate_x', old('qr_coordinates.x')) }}"
                                   class="qr-input mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                                   placeholder="{{ __('ticket_types.create_form.qr_x_placeholder') }}"
                                   min="0"
                                   required />
                            @error('qr_coordinate_x')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="qr_coordinate_y" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.create_form.qr_y_label') }} <span class="text-red-500">*</span></label>
                            <input type="number"
                                   name="qr_coordinate_y"
                                   id="qr_coordinate_y"
                                   value="{{ old('qr_coordinate_y', old('qr_coordinates.y')) }}"
                                   class="qr-input mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                                   placeholder="{{ __('ticket_types.create_form.qr_y_placeholder') }}"
                                   min="0"
                                   required />
                            @error('qr_coordinate_y')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="qr_coordinate_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.create_form.qr_size_label') }} <span class="text-red-500">*</span></label>
                            <input type="number"
                                   name="qr_coordinate_size"
                                   id="qr_coordinate_size"
                                   value="{{ old('qr_coordinate_size', old('qr_coordinates.size')) }}"
                                   class="qr-input mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                                   placeholder="{{ __('ticket_types.create_form.qr_size_placeholder') }}"
                                   min="10"
                                   required />
                            @error('qr_coordinate_size')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <input type="hidden" name="qr_coordinates" id="qr_coordinates_json" value="{{ old('qr_coordinates', '{"x":0,"y":0,"size":100}') }}">
                    @error('qr_coordinates')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </fieldset>

                {{-- Ticket Commissions --}}
                <fieldset class="rounded-md border border-gray-300 p-4 dark:border-gray-600">
                    <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 px-2">{{ __('ticket_types.create_form.commissions_fieldset_legend') }} <span class="text-red-500">*</span></legend>
                    <div id="commission-tiers-container" class="space-y-4">
                        @php
                            // Use a default structure for the first tier if old('commissions') is empty
                            $oldCommissions = old('commissions', [['min_sold' => '', 'max_sold' => '', 'commission_amount' => '']]);
                            if (empty($oldCommissions)) { // Ensure there's at least one row structure for the loop
                                $oldCommissions = [['min_sold' => '', 'max_sold' => '', 'commission_amount' => '']];
                            }
                        @endphp
                        @foreach($oldCommissions as $index => $commission)
                        <div class="commission-tier-row grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-7 items-end border-b border-gray-200 dark:border-gray-700 pb-4 last:border-b-0 last:pb-0">
                            <div class="sm:col-span-2">
                                <label for="commissions_{{ $index }}_min_sold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.create_form.commissions_min_sold_label') }} <span class="text-red-500">*</span></label>
                                <input type="number" name="commissions[{{ $index }}][min_sold]" id="commissions_{{ $index }}_min_sold" value="{{ $commission['min_sold'] ?? '' }}" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="{{ __('ticket_types.create_form.commissions_min_sold_placeholder') }}" min="0" required>
                                @error("commissions.{$index}.min_sold") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="commissions_{{ $index }}_max_sold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.create_form.commissions_max_sold_label') }}</label>
                                <input type="number" name="commissions[{{ $index }}][max_sold]" id="commissions_{{ $index }}_max_sold" value="{{ $commission['max_sold'] ?? '' }}" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="{{ __('ticket_types.create_form.commissions_max_sold_placeholder') }}" min="0">
                                @error("commissions.{$index}.max_sold") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="commissions_{{ $index }}_commission_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.create_form.commissions_amount_label') }}<span class="text-red-500">*</span></label>
                                <input type="number" name="commissions[{{ $index }}][commission_amount]" id="commissions_{{ $index }}_commission_amount" value="{{ $commission['commission_amount'] ?? '' }}" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="{{ __('ticket_types.create_form.commissions_amount_placeholder') }}" step="0.01" min="0" required>
                                @error("commissions.{$index}.commission_amount") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-1">
                                @if($index > 0 || count($oldCommissions) > 1) {{-- Show remove button if not the first row, or if more than one row exists from old() --}}
                                <button type="button" class="remove-commission-tier-btn inline-flex items-center justify-center p-2 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 bg-red-100 hover:bg-red-200 dark:bg-red-700 dark:hover:bg-red-600 rounded-md w-full sm:w-auto">
                                    {{ __('ticket_types.create_form.commissions_remove_button') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <button type="button" id="add-commission-tier-btn" class="inline-flex items-center rounded-md border border-dashed border-gray-400 dark:border-gray-500 bg-white dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            {{ __('ticket_types.create_form.commissions_add_tier_button') }}
                        </button>
                    </div>
                     @error('commissions') {{-- General error for the commissions array itself --}}
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </fieldset>


                {{-- Submit and Cancel Buttons --}}
                <div class="flex items-center justify-end space-x-3 pt-4">
                    <a href="{{ route('admin.ticket-types.index', $festival) }}"
                       class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                        {{ __('ticket_types.create_form.cancel_button') }}
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('ticket_types.create_form.create_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
// Pass translated strings to JavaScript
const translatedStrings = {
    minSoldLabel: "{{ __('ticket_types.create_form.commissions_min_sold_label') }}",
    minSoldPlaceholder: "{{ __('ticket_types.create_form.commissions_min_sold_placeholder') }}",
    maxSoldLabel: "{{ __('ticket_types.create_form.commissions_max_sold_label') }}",
    maxSoldPlaceholder: "{{ __('ticket_types.create_form.commissions_max_sold_placeholder') }}",
    commissionAmountLabel: "{{ __('ticket_types.create_form.commissions_amount_label') }}",
    commissionAmountPlaceholder: "{{ __('ticket_types.create_form.commissions_amount_placeholder') }}",
    removeButtonText: "{{ __('ticket_types.create_form.commissions_remove_button') }}"
};

document.addEventListener('DOMContentLoaded', function () {
    // --- QR Coordinates JSON updater ---
    const qrXInput = document.getElementById('qr_coordinate_x');
    const qrYInput = document.getElementById('qr_coordinate_y');
    const qrSizeInput = document.getElementById('qr_coordinate_size');
    const qrJsonInput = document.getElementById('qr_coordinates_json');

    function updateQrJson() {
        if (!qrXInput || !qrYInput || !qrSizeInput || !qrJsonInput) return;
        const qrData = {
            x: parseInt(qrXInput.value) || 0,
            y: parseInt(qrYInput.value) || 0,
            size: parseInt(qrSizeInput.value) || 100 // Default size if input is invalid
        };
        qrJsonInput.value = JSON.stringify(qrData);
    }

    [qrXInput, qrYInput, qrSizeInput].forEach(input => {
        if (input) {
            input.addEventListener('input', updateQrJson);
        }
    });

    // Initial population and sync for QR fields
    if (qrXInput && qrYInput && qrSizeInput && qrJsonInput) {
        // If individual fields have `old()` values, they are already set by Blade.
        // This part ensures the JSON input reflects the initial state of individual fields,
        // or populates individual fields from the JSON if they are empty and JSON has data (from $ticketType).
        let initialQrData = { x: 0, y: 0, size: 100 }; // Default structure
        try {
            const existingJson = JSON.parse(qrJsonInput.value); // Value from old('qr_coordinates', $ticketType->qr_coordinates)
            if (existingJson && typeof existingJson === 'object') {
                initialQrData = { ...initialQrData, ...existingJson };
            }
        } catch (e) {
            console.warn('Could not parse initial QR JSON data for individual fields:', qrJsonInput.value);
        }

        // Populate individual fields if they are empty and corresponding old('qr_coordinate_x/y/z') was not set
        if (qrXInput.value === '' && (typeof initialQrData.x !== 'undefined')) qrXInput.value = initialQrData.x;
        if (qrYInput.value === '' && (typeof initialQrData.y !== 'undefined')) qrYInput.value = initialQrData.y;
        if (qrSizeInput.value === '' && (typeof initialQrData.size !== 'undefined')) qrSizeInput.value = initialQrData.size;

        updateQrJson(); // Sync the hidden JSON input based on the (potentially updated) individual fields
    }

    // --- Dynamic Commission Tiers ---
    const commissionContainer = document.getElementById('commission-tiers-container');
    const addTierButton = document.getElementById('add-commission-tier-btn');

    // Determine the starting index for new tiers by counting existing rows rendered by PHP
    let commissionTierIndex = commissionContainer ? commissionContainer.querySelectorAll('.commission-tier-row').length : 0;

    // Function to attach event listener to a remove button
    function addRemoveListener(button) {
        button.addEventListener('click', function() {
            this.closest('.commission-tier-row').remove();
            // Optional: if you want to always have at least one tier row
            if (commissionContainer && commissionContainer.children.length === 0) {
                addCommissionTierHtml(0); // Add a new one if all are removed
                commissionTierIndex = 1;    // Reset index for the next "add" operation
            }
        });
    }

    // Attach listeners to remove buttons of initially rendered tiers (from old() or $ticketType->commissions)
    if (commissionContainer) {
        commissionContainer.querySelectorAll('.remove-commission-tier-btn').forEach(addRemoveListener);
    }

    // Add the first tier row via JS if the PHP loop didn't render any
    // (e.g., new ticket type and old('commissions') was also empty)
    // The Blade @php logic for $commissionsToDisplay should always ensure at least one empty shell array,
    // so the foreach loop should run at least once. This JS check is a safeguard.
    if (commissionContainer && addTierButton && commissionContainer.children.length === 0) {
        addCommissionTierHtml(0);
        commissionTierIndex = 1;
    }

    if (addTierButton && commissionContainer) {
        addTierButton.addEventListener('click', function () {
            addCommissionTierHtml(commissionTierIndex);
            commissionTierIndex++;
        });
    }

    function addCommissionTierHtml(index) {
        // Ensure translatedStrings is defined; if not, use fallbacks (should be defined from Blade)
        const ts = typeof translatedStrings !== 'undefined' ? translatedStrings : {
            minSoldLabel: "Min Sold",
            minSoldPlaceholder: "e.g., 1",
            maxSoldLabel: "Max Sold",
            maxSoldPlaceholder: "e.g., 10 (empty for no limit)",
            commissionAmountLabel: "Commission Amount",
            commissionAmountPlaceholder: "e.g., 1.50",
            removeButtonText: "Remove"
        };

        const tierHtml = `
            <div class="commission-tier-row grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-7 items-end border-b border-gray-200 dark:border-gray-700 pb-4 last:border-b-0 last:pb-0">
                <div class="sm:col-span-2">
                    <label for="commissions_${index}_min_sold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">${ts.minSoldLabel} <span class="text-red-500">*</span></label>
                    <input type="number" name="commissions[${index}][min_sold]" id="commissions_${index}_min_sold" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="${ts.minSoldPlaceholder}" min="0" required>
                </div>
                <div class="sm:col-span-2">
                    <label for="commissions_${index}_max_sold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">${ts.maxSoldLabel}</label>
                    <input type="number" name="commissions[${index}][max_sold]" id="commissions_${index}_max_sold" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="${ts.maxSoldPlaceholder}" min="0">
                </div>
                <div class="sm:col-span-2">
                    <label for="commissions_${index}_commission_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">${ts.commissionAmountLabel} <span class="text-red-500">*</span></label>
                    <input type="number" name="commissions[${index}][commission_amount]" id="commissions_${index}_commission_amount" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="${ts.commissionAmountPlaceholder}" step="0.01" min="0" required>
                </div>
                <div class="sm:col-span-1">
                    <button type="button" class="remove-commission-tier-btn inline-flex items-center justify-center p-2 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 bg-red-100 hover:bg-red-200 dark:bg-red-700 dark:hover:bg-red-600 rounded-md w-full sm:w-auto">
                        ${ts.removeButtonText}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
        `;
        if (commissionContainer) {
            commissionContainer.insertAdjacentHTML('beforeend', tierHtml);
            const newRow = commissionContainer.lastElementChild;
            const removeButton = newRow.querySelector('.remove-commission-tier-btn');
            if (removeButton) {
                addRemoveListener(removeButton); // Attach listener to the new remove button
            }
        }
    }
});
</script></x-layouts.app>
