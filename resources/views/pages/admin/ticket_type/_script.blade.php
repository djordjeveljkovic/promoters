{{-- Shared JS for the ticket-type form: live QR JSON + commission tier
     add/remove buttons. Used by both create.blade.php and edit.blade.php. --}}
<script>
    const translatedStrings = {
        minSoldLabel: @json(__('ticket_types.create_form.commissions_min_sold_label')),
        minSoldPlaceholder: @json(__('ticket_types.create_form.commissions_min_sold_placeholder')),
        maxSoldLabel: @json(__('ticket_types.create_form.commissions_max_sold_label')),
        maxSoldPlaceholder: @json(__('ticket_types.create_form.commissions_max_sold_placeholder')),
        commissionAmountLabel: @json(__('ticket_types.create_form.commissions_amount_label')),
        commissionAmountPlaceholder: @json(__('ticket_types.create_form.commissions_amount_placeholder')),
        removeButtonText: @json(__('ticket_types.create_form.commissions_remove_button')),
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
                size: parseInt(qrSizeInput.value) || 100,
            };
            qrJsonInput.value = JSON.stringify(qrData);
        }
        [qrXInput, qrYInput, qrSizeInput].forEach(input => input?.addEventListener('input', updateQrJson));

        let initialQrData = { x: 0, y: 0, size: 100 };
        try {
            const existingJson = JSON.parse(qrJsonInput.value);
            if (existingJson && typeof existingJson === 'object') initialQrData = { ...initialQrData, ...existingJson };
        } catch (_) {}
        if (qrXInput?.value === '' && typeof initialQrData.x !== 'undefined') qrXInput.value = initialQrData.x;
        if (qrYInput?.value === '' && typeof initialQrData.y !== 'undefined') qrYInput.value = initialQrData.y;
        if (qrSizeInput?.value === '' && typeof initialQrData.size !== 'undefined') qrSizeInput.value = initialQrData.size;
        updateQrJson();

        // --- Commission Tiers ---
        const container = document.getElementById('commission-tiers-container');
        const addBtn = document.getElementById('add-commission-tier-btn');
        let tierIndex = container ? container.querySelectorAll('.commission-tier-row').length : 0;

        function addRemoveListener(button) {
            button.addEventListener('click', function () {
                this.closest('.commission-tier-row').remove();
                if (container && container.children.length === 0) {
                    addTierHtml(0); tierIndex = 1;
                }
            });
        }
        container?.querySelectorAll('.remove-commission-tier-btn').forEach(addRemoveListener);

        if (container && addBtn) {
            addBtn.addEventListener('click', function () {
                addTierHtml(tierIndex); tierIndex++;
            });
        }

        function addTierHtml(index) {
            const ts = translatedStrings;
            const html = `
                <div class="commission-tier-row grid grid-cols-1 sm:grid-cols-7 gap-3 items-end pb-3 border-b border-[color:var(--ds-divider)] last:border-b-0 last:pb-0">
                    <div class="sm:col-span-2">
                        <label class="ds-label">${ts.minSoldLabel} <span class="text-rose-500">*</span></label>
                        <input type="number" name="commissions[${index}][min_sold]" class="ds-input" placeholder="${ts.minSoldPlaceholder}" min="0" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="ds-label">${ts.maxSoldLabel}</label>
                        <input type="number" name="commissions[${index}][max_sold]" class="ds-input" placeholder="${ts.maxSoldPlaceholder}" min="0">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="ds-label">${ts.commissionAmountLabel} <span class="text-rose-500">*</span></label>
                        <input type="number" name="commissions[${index}][commission_amount]" class="ds-input" placeholder="${ts.commissionAmountPlaceholder}" step="0.01" min="0" required>
                    </div>
                    <div class="sm:col-span-1">
                        <button type="button" class="remove-commission-tier-btn ds-btn ds-btn-danger-ghost ds-btn-icon w-full" title="${ts.removeButtonText}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        </button>
                    </div>
                </div>`;
            if (container) {
                container.insertAdjacentHTML('beforeend', html);
                const newRow = container.lastElementChild;
                const removeButton = newRow.querySelector('.remove-commission-tier-btn');
                if (removeButton) addRemoveListener(removeButton);
            }
        }
    });
</script>
