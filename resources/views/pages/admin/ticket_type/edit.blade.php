<x-layouts.app :title="__('ticket_types.edit_form.page_title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="max-w-2xl mx-auto w-full rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
            <div class="mb-6 flex items-center justify-between">
                {{-- Updated heading to include ticket type name for context --}}
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('ticket_types.edit_form.main_heading', ['name' => $ticketType->name]) }}</h1>
                <a href="{{ route('admin.ticket-types.index', $festival) }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">{!! __('ticket_types.edit_form.back_to_list_link') !!}</a>
            </div>

            {{-- Display any session errors from the controller --}}
            @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Corrected form action and method for update --}}
            <form method="POST" action="{{ route('admin.ticket-types.update', ['festival' => $festival->slug, 'id' => $ticketType->id]) }}" enctype="multipart/form-data" class="space-y-6" id="editTicketTypeForm">
                @csrf
                @method('PUT') {{-- Important for update routes --}}

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.edit_form.name_label') }} <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $ticketType->name) }}" {{-- Populate with existing data --}}
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                           placeholder="{{ __('ticket_types.edit_form.name_placeholder') }}"
                           required />
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Price --}}
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.edit_form.price_label') }} <span class="text-red-500">*</span></label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                        <input type="number"
                               name="price"
                               id="price"
                               value="{{ old('price', $ticketType->price) }}" {{-- Populate with existing data --}}
                               class="block w-full rounded-md border-gray-300 bg-gray-50 pl-7 pr-12 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                               placeholder="{{ __('ticket_types.edit_form.price_placeholder') }}"
                               step="0.01"
                               min="0"
                               required />
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">{{ __('ticket_types.edit_form.price_currency_suffix') }}</span>
                        </div>
                    </div>
                    @error('price')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Photo --}}
                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.edit_form.photo_label') }}</label>
                    @if($ticketType->photo_path)
                        <div class="my-2">
                            <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('ticket_types.edit_form.current_photo_label') }}</span>
                            <img src="{{ asset($ticketType->photo_path) }}" alt="Current photo for {{ $ticketType->name }}" class="mt-1 h-20 w-auto rounded-md object-cover">
                        </div>
                    @else
                         <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('ticket_types.edit_form.no_current_photo') }}</p>
                    @endif
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
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('ticket_types.edit_form.photo_help_text_edit') }}</p>
                    @error('photo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- QR Coordinates --}}
                @php
                    // Decode JSON from model, or use old input, or default for QR fields
                    $currentQrCoordinates = json_decode($ticketType->qr_coordinates, true) ?: ['x'=>0, 'y'=>0, 'size'=>100];
                    $oldQrJson = old('qr_coordinates', json_encode($currentQrCoordinates));
                    try {
                        $oldQrDecoded = json_decode($oldQrJson, true);
                    } catch (\Exception $e) {
                        $oldQrDecoded = $currentQrCoordinates;
                    }
                @endphp
                <fieldset class="rounded-md border border-gray-300 p-4 dark:border-gray-600">
                    <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 px-2">{{ __('ticket_types.edit_form.qr_fieldset_legend') }} <span class="text-red-500">*</span></legend>
                    <p class="mb-3 text-xs text-gray-500 dark:text-gray-400 px-2">{{ __('ticket_types.edit_form.qr_help_text') }}</p>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-3">
                        <div>
                            <label for="qr_coordinate_x" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.edit_form.qr_x_label') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="qr_coordinate_x" id="qr_coordinate_x" value="{{ old('qr_coordinate_x', $oldQrDecoded['x'] ?? ($currentQrCoordinates['x'] ?? 0)) }}" class="qr-input mt-1 block w-full rounded-md ..." placeholder="{{ __('ticket_types.edit_form.qr_x_placeholder') }}" min="0" required />
                            @error('qr_coordinate_x') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="qr_coordinate_y" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.edit_form.qr_y_label') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="qr_coordinate_y" id="qr_coordinate_y" value="{{ old('qr_coordinate_y', $oldQrDecoded['y'] ?? ($currentQrCoordinates['y'] ?? 0)) }}" class="qr-input mt-1 block w-full rounded-md ..." placeholder="{{ __('ticket_types.edit_form.qr_y_placeholder') }}" min="0" required />
                            @error('qr_coordinate_y') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="qr_coordinate_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.edit_form.qr_size_label') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="qr_coordinate_size" id="qr_coordinate_size" value="{{ old('qr_coordinate_size', $oldQrDecoded['size'] ?? ($currentQrCoordinates['size'] ?? 100)) }}" class="qr-input mt-1 block w-full rounded-md ..." placeholder="{{ __('ticket_types.edit_form.qr_size_placeholder') }}" min="10" required />
                            @error('qr_coordinate_size') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <input type="hidden" name="qr_coordinates" id="qr_coordinates_json" value="{{ old('qr_coordinates', $ticketType->qr_coordinates ?? '{"x":0,"y":0,"size":100}') }}">
                    @error('qr_coordinates') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                </fieldset>

                {{-- Ticket Commissions --}}
                <fieldset class="rounded-md border border-gray-300 p-4 dark:border-gray-600">
                    <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 px-2">{{ __('ticket_types.edit_form.commissions_fieldset_legend') }} <span class="text-red-500">*</span></legend>
                    <div id="commission-tiers-container" class="space-y-4">
                        @php
                            // Populate with existing commissions or old input
                            $commissionsToDisplay = old('commissions', $ticketType->commissions->map(function($c) {
                                return ['min_sold' => $c->min_sold, 'max_sold' => $c->max_sold, 'commission_amount' => $c->commission_amount, 'id' => $c->id]; // include id if you need to update existing ones
                            })->toArray());
                            if (empty($commissionsToDisplay)) {
                                $commissionsToDisplay = [['min_sold' => '', 'max_sold' => '', 'commission_amount' => '', 'id' => '']];
                            }
                        @endphp
                        @foreach($commissionsToDisplay as $index => $commission)
                        <div class="commission-tier-row grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-7 items-end border-b border-gray-200 dark:border-gray-700 pb-4 last:border-b-0 last:pb-0">
                            {{-- Hidden input for existing commission ID, if you plan to update instead of delete/recreate --}}
                            {{-- <input type="hidden" name="commissions[{{ $index }}][id]" value="{{ $commission['id'] ?? '' }}"> --}}
                            <div class="sm:col-span-2">
                                <label for="commissions_{{ $index }}_min_sold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.edit_form.commissions_min_sold_label') }} <span class="text-red-500">*</span></label>
                                <input type="number" name="commissions[{{ $index }}][min_sold]" id="commissions_{{ $index }}_min_sold" value="{{ $commission['min_sold'] ?? '' }}" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="{{ __('ticket_types.edit_form.commissions_min_sold_placeholder') }}" min="0" required>
                                @error("commissions.{$index}.min_sold") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="commissions_{{ $index }}_max_sold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.edit_form.commissions_max_sold_label') }}</label>
                                <input type="number" name="commissions[{{ $index }}][max_sold]" id="commissions_{{ $index }}_max_sold" value="{{ $commission['max_sold'] ?? '' }}" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="{{ __('ticket_types.edit_form.commissions_max_sold_placeholder') }}" min="0">
                                @error("commissions.{$index}.max_sold") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="commissions_{{ $index }}_commission_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('ticket_types.edit_form.commissions_amount_label') }}<span class="text-red-500">*</span></label>
                                <input type="number" name="commissions[{{ $index }}][commission_amount]" id="commissions_{{ $index }}_commission_amount" value="{{ $commission['commission_amount'] ?? '' }}" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="{{ __('ticket_types.edit_form.commissions_amount_placeholder') }}" step="0.01" min="0" required>
                                @error("commissions.{$index}.commission_amount") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-1">
                                {{-- Show remove button always for >1 tiers, or if it's not the very first predefined row and there's more than one --}}
                                @if($index > 0 || count($commissionsToDisplay) > 1)
                                <button type="button" class="remove-commission-tier-btn inline-flex items-center justify-center p-2 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 bg-red-100 hover:bg-red-200 dark:bg-red-700 dark:hover:bg-red-600 rounded-md w-full sm:w-auto">
                                    {{ __('ticket_types.edit_form.commissions_remove_button') }}
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
                            {{ __('ticket_types.edit_form.commissions_add_tier_button') }}
                        </button>
                    </div>
                     @error('commissions')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </fieldset>

                {{-- Submit and Cancel Buttons --}}
                <div class="flex items-center justify-end space-x-3 pt-4">
                    <a href="{{ route('admin.ticket-types.index', $festival) }}"
                       class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                        {{ __('ticket_types.edit_form.cancel_button') }}
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('ticket_types.edit_form.update_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
// Pass translated strings to JavaScript
const translatedCommissionStrings = { // Renamed for clarity as it's specific to commission section
    minSoldLabel: "{{ __('ticket_types.edit_form.commissions_min_sold_label') }}",
    minSoldPlaceholder: "{{ __('ticket_types.edit_form.commissions_min_sold_placeholder') }}",
    maxSoldLabel: "{{ __('ticket_types.edit_form.commissions_max_sold_label') }}",
    maxSoldPlaceholder: "{{ __('ticket_types.edit_form.commissions_max_sold_placeholder') }}",
    commissionAmountLabel: "{{ __('ticket_types.edit_form.commissions_amount_label') }}",
    commissionAmountPlaceholder: "{{ __('ticket_types.edit_form.commissions_amount_placeholder') }}",
    removeButtonText: "{{ __('ticket_types.edit_form.commissions_remove_button') }}"
};

document.addEventListener('DOMContentLoaded', function () {
    // QR Coordinates JSON updater
    const qrXInput = document.getElementById('qr_coordinate_x');
    const qrYInput = document.getElementById('qr_coordinate_y');
    const qrSizeInput = document.getElementById('qr_coordinate_size');
    const qrJsonInput = document.getElementById('qr_coordinates_json');

    function updateQrJson() {
        if (!qrXInput || !qrYInput || !qrSizeInput || !qrJsonInput) return;
        const qrData = {
            x: parseInt(qrXInput.value) || 0,
            y: parseInt(qrYInput.value) || 0,
            size: parseInt(qrSizeInput.value) || 100
        };
        qrJsonInput.value = JSON.stringify(qrData);
    }

    [qrXInput, qrYInput, qrSizeInput].forEach(input => {
        if (input) input.addEventListener('input', updateQrJson);
    });

    if (qrXInput && qrYInput && qrSizeInput && qrJsonInput) {
        // Parse existing JSON from hidden input to populate individual fields initially
        try {
            const existingJsonData = JSON.parse(qrJsonInput.value);
            if (existingJsonData && typeof existingJsonData === 'object') {
                // Only set if the input is currently empty and old('qr_coordinate_x') wasn't already used
                // The `value` attribute in HTML already handles old() data for individual fields if they were submitted.
                // This part ensures the individual fields reflect the initial JSON if `old()` for individual fields is empty.
                if (qrXInput.value === '' && (typeof existingJsonData.x !== 'undefined')) qrXInput.value = existingJsonData.x;
                if (qrYInput.value === '' && (typeof existingJsonData.y !== 'undefined')) qrYInput.value = existingJsonData.y;
                if (qrSizeInput.value === '' && (typeof existingJsonData.size !== 'undefined')) qrSizeInput.value = existingJsonData.size;
            }
        } catch (e) {
            console.warn('Could not parse initial QR JSON data for individual fields:', qrJsonInput.value);
        }
        updateQrJson(); // Initial call to sync JSON input if individual fields had values (or set to default)
    }


    // Dynamic Commission Tiers
    const container = document.getElementById('commission-tiers-container');
    const addButton = document.getElementById('add-commission-tier-btn');
    // Correctly get the count of initially rendered commission rows (from PHP/old data)
    // This should be the number of .commission-tier-row elements.
    let commissionIndex = container ? container.querySelectorAll('.commission-tier-row').length : 0;

    // Attach event listeners to initially rendered remove buttons (from PHP loop)
    if (container) {
        container.querySelectorAll('.remove-commission-tier-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.commission-tier-row').remove();
                 if (container.children.length === 0) { // If all tiers are removed
                     addCommissionTierHtml(0); // Add one back
                     commissionIndex = 1;      // Reset index
                 }
            });
        });
    }

    if (addButton && container) {
        addButton.addEventListener('click', function () {
            addCommissionTierHtml(commissionIndex);
            commissionIndex++;
        });

        // If no commission tiers were rendered by PHP (e.g., new ticket type or no old data)
        // and the $commissionsToDisplay was effectively empty for the loop.
        if (container.children.length === 0) {
            addCommissionTierHtml(0); // Add the first tier row
            commissionIndex = 1;      // Next new tier will be index 1
        }
    }


    function addCommissionTierHtml(index) {
        const tierHtml = `
            <div class="commission-tier-row grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-7 items-end border-b border-gray-200 dark:border-gray-700 pb-4 last:border-b-0 last:pb-0">
                <div class="sm:col-span-2">
                    <label for="commissions_${index}_min_sold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">${translatedCommissionStrings.minSoldLabel} <span class="text-red-500">*</span></label>
                    <input type="number" name="commissions[${index}][min_sold]" id="commissions_${index}_min_sold" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="${translatedCommissionStrings.minSoldPlaceholder}" min="0" required>
                </div>
                <div class="sm:col-span-2">
                    <label for="commissions_${index}_max_sold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">${translatedCommissionStrings.maxSoldLabel}</label>
                    <input type="number" name="commissions[${index}][max_sold]" id="commissions_${index}_max_sold" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="${translatedCommissionStrings.maxSoldPlaceholder}" min="0">
                </div>
                <div class="sm:col-span-2">
                    <label for="commissions_${index}_commission_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">${translatedCommissionStrings.commissionAmountLabel} <span class="text-red-500">*</span></label>
                    <input type="number" name="commissions[${index}][commission_amount]" id="commissions_${index}_commission_amount" class="mt-1 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:text-white dark:border-gray-600 rounded-md" placeholder="${translatedCommissionStrings.commissionAmountPlaceholder}" step="0.01" min="0" required>
                </div>
                <div class="sm:col-span-1">
                    <button type="button" class="remove-commission-tier-btn inline-flex items-center justify-center p-2 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 bg-red-100 hover:bg-red-200 dark:bg-red-700 dark:hover:bg-red-600 rounded-md w-full sm:w-auto">
                        ${translatedCommissionStrings.removeButtonText}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', tierHtml);
        const newRow = container.lastElementChild;
        const removeButton = newRow.querySelector('.remove-commission-tier-btn');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                newRow.remove();
                if (container.children.length === 0) { // If all tiers are removed by user
                     addCommissionTierHtml(0); // Add one back as a template
                     commissionIndex = 1;      // Reset index
                 }
            });
        }
    }
});
</script>
</x-layouts.app>
