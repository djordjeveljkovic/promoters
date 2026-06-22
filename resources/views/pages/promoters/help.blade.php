<x-layouts.app>
    <x-ds.page-header
        :title="__('Help')"
        :subtitle="__('How to sell tickets and what to do when something goes wrong.')"
    />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-ds.card :title="__('Support contact')">
            <x-slot:body>
                <p class="text-sm text-[color:var(--ds-text-muted)] mb-4">
                    {{ __('For any help, questions or technical support, contact us below.') }}
                </p>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-md bg-[color:var(--ds-accent-soft)] text-[color:var(--ds-accent-text)] flex items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs text-[color:var(--ds-text-muted)]">Phone</div>
                            <div class="font-medium text-[color:var(--ds-text)]">+381 63 7363 680</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-md bg-[color:var(--ds-accent-soft)] text-[color:var(--ds-accent-text)] flex items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zM22 6 12 13 2 6"/></svg>
                        </div>
                        <div>
                            <div class="text-xs text-[color:var(--ds-text-muted)]">Email</div>
                            <div class="font-medium text-[color:var(--ds-text)]">lazar.buster@gmail.com</div>
                        </div>
                    </div>
                </div>
            </x-slot:body>
        </x-ds.card>

        <x-ds.card :title="__('How to sell a ticket')">
            <x-slot:body>
                <ol class="space-y-3 text-sm text-[color:var(--ds-text)]">
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[color:var(--ds-accent-soft-2)] text-[color:var(--ds-accent-text)] text-xs font-semibold flex items-center justify-center">1</span>
                        <span>{{ __('Enter the customer email in the "Email" field.') }}</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[color:var(--ds-accent-soft-2)] text-[color:var(--ds-accent-text)] text-xs font-semibold flex items-center justify-center">2</span>
                        <span>{{ __('Pick a ticket type, choose quantity, and click "Add item".') }}</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[color:var(--ds-accent-soft-2)] text-[color:var(--ds-accent-text)] text-xs font-semibold flex items-center justify-center">3</span>
                        <span>{{ __('You can add up to 10 tickets in a single order.') }}</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[color:var(--ds-accent-soft-2)] text-[color:var(--ds-accent-text)] text-xs font-semibold flex items-center justify-center">4</span>
                        <span>{{ __('Click "Place order & send tickets" — the customer will receive them by email.') }}</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[color:var(--ds-accent-soft-2)] text-[color:var(--ds-accent-text)] text-xs font-semibold flex items-center justify-center">5</span>
                        <span>{{ __('Once the status moves from "processing" to "completed", your commission is recorded.') }}</span>
                    </li>
                </ol>
            </x-slot:body>
        </x-ds.card>
    </div>
</x-layouts.app>
