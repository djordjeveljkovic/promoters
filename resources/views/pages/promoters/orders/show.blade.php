<x-layouts.app :title="__('Order') . ' #' . $order->order_number">
    @php
        $festival = $order->festival;
        $festivalParam = $festival ? ['festival' => $festival->slug] : [];
    @endphp

    <x-ds.page-header
        :title="__('Order #:number', ['number' => $order->order_number])"
        :subtitle="$festival?->displayName()"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('promoter.orders.index', $festivalParam)" wire:navigate>
                ← {{ __('All orders') }}
            </x-ds.button>
            <x-ds.button variant="secondary" :href="route('promoter.orders.create', $festivalParam)" wire:navigate>
                + {{ __('New order') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    @if (session('success'))
        <x-ds.alert variant="success" class="mb-4">{{ session('success') }}</x-ds.alert>
    @endif
    @if (session('error'))
        <x-ds.alert variant="danger" class="mb-4">{{ session('error') }}</x-ds.alert>
    @endif

    <div class="grid lg:grid-cols-3 gap-5">
        {{-- Main column ------------------------------------------------------ --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Status banner --}}
            <x-ds.card>
                <x-slot:body>
                    <div class="flex flex-wrap items-center gap-3">
                        @php
                            $jobStatusColors = [
                                'processing' => 'warning',
                                'completed'  => 'success',
                                'failed'     => 'danger',
                                'pending'    => 'neutral',
                                'sent'       => 'info',
                            ];
                            $paymentVariant = $order->paid > 0 ? 'success' : 'warning';
                        @endphp
                        <x-ds.badge :variant="$jobStatusColors[$order->job_status] ?? 'neutral'" dot>
                            {{ __(ucfirst($order->job_status ?? 'pending')) }}
                        </x-ds.badge>
                        <x-ds.badge :variant="$paymentVariant" dot>
                            {{ $order->paid > 0 ? __('Paid') : __('Unpaid') }}
                        </x-ds.badge>
                        <span class="text-sm text-[color:var(--ds-text-muted)]">
                            {{ __('Placed :when', ['when' => $order->created_at->setTimezone('Europe/Belgrade')->format('d.m.Y H:i')]) }}
                        </span>
                        <div class="ml-auto flex items-center gap-2">
                            <form method="POST" action="{{ route('promoter.orders.rerun-image-generation', $festivalParam + ['order' => $order->id]) }}">
                                @csrf
                                <x-ds.button variant="ghost" size="sm" type="submit" title="{{ __('Re-run image generation') }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><polyline points="21 3 21 8 16 8"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><polyline points="3 21 3 16 8 16"/></svg>
                                    {{ __('Re-run images') }}
                                </x-ds.button>
                            </form>
                            <form method="POST" action="{{ route('promoter.orders.rerun-email-sending', $festivalParam + ['order' => $order->id]) }}">
                                @csrf
                                <x-ds.button variant="primary" size="sm" type="submit" title="{{ __('Send tickets email again') }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><polyline points="22 6 12 13 2 6"/></svg>
                                    {{ __('Resend email') }}
                                </x-ds.button>
                            </form>
                        </div>
                    </div>
                </x-slot:body>
            </x-ds.card>

            {{-- Items --}}
            <x-ds.card :title="__('Items')">
                <x-slot:body :padded="false">
                    <x-ds.table>
                        <x-slot:head>
                            <tr>
                                <th>{{ __('Ticket type') }}</th>
                                <th class="text-right">{{ __('Unit price') }}</th>
                                <th class="text-right">{{ __('Quantity') }}</th>
                                <th class="text-right">{{ __('Subtotal') }}</th>
                            </tr>
                        </x-slot:head>
                        @forelse ($order->items as $item)
                            <tr>
                                <td>
                                    <div class="row-title">{{ $item->ticketType?->name ?? '—' }}</div>
                                    <div class="row-meta">{{ $item->ticketType?->description ?? '' }}</div>
                                </td>
                                <td class="text-right num">{{ number_format((float) ($item->price_at_order ?? $item->ticketType?->price ?? 0), 0, ',', '.') }} {{ __('RSD') }}</td>
                                <td class="text-right num">{{ $item->quantity }}</td>
                                <td class="text-right num font-semibold">{{ number_format((float) ($item->quantity * ($item->price_at_order ?? $item->ticketType?->price ?? 0)), 0, ',', '.') }} {{ __('RSD') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-ds.empty-state
                                        :title="__('No items on this order')"
                                        :message="__('The order has no line items.')"
                                    />
                                </td>
                            </tr>
                        @endforelse
                        <x-slot:foot>
                            <tr>
                                <td colspan="3" class="text-right font-semibold">{{ __('Total') }}</td>
                                <td class="text-right num font-semibold text-base">{{ number_format((float) ($order->total ?: $totalPrice), 0, ',', '.') }} {{ __('RSD') }}</td>
                            </tr>
                        </x-slot:foot>
                    </x-ds.table>
                </x-slot:body>
            </x-ds.card>

            {{-- Tickets --}}
            <x-ds.card :title="__('Tickets (:count)', ['count' => $order->tickets->count()])">
                <x-slot:body>
                    @if ($order->tickets->isEmpty())
                        <x-ds.empty-state
                            :title="__('No tickets yet')"
                            :message="__('Tickets are generated once the order is processed.')"
                        />
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($order->tickets as $ticket)
                                <div class="rounded-lg border border-[color:var(--ds-border)] p-3 bg-[color:var(--ds-surface-2)]">
                                    <div class="flex items-center gap-2 mb-2">
                                        <x-ds.badge :variant="$ticket->is_active ? 'success' : 'neutral'" size="sm" dot>
                                            {{ $ticket->is_active ? __('Active') : __('Used') }}
                                        </x-ds.badge>
                                        <code class="text-[10px] text-[color:var(--ds-text-muted)] truncate flex-1">{{ $ticket->code }}</code>
                                    </div>
                                    @if ($ticket->qr_code_path && file_exists(public_path($ticket->qr_code_path)))
                                        <img src="{{ asset($ticket->qr_code_path) }}" alt="QR {{ $ticket->code }}" class="w-full aspect-square object-contain bg-white rounded">
                                    @elseif ($ticket->image_path && file_exists(public_path($ticket->image_path)))
                                        <img src="{{ asset($ticket->image_path) }}" alt="Ticket {{ $ticket->code }}" class="w-full aspect-square object-contain bg-white rounded">
                                    @else
                                        <div class="w-full aspect-square rounded bg-[color:var(--ds-surface)] flex items-center justify-center text-[color:var(--ds-text-subtle)] text-xs">
                                            {{ __('No image yet') }}
                                        </div>
                                    @endif
                                    <div class="mt-2 text-[11px] text-[color:var(--ds-text-muted)]">
                                        {{ $ticket->ticketType?->name ?? '—' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-slot:body>
            </x-ds.card>
        </div>

        {{-- Side column ----------------------------------------------------- --}}
        <div class="space-y-5">
            <x-ds.card :title="__('Customer')">
                <x-slot:body>
                    <div class="space-y-2 text-sm">
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold">{{ __('Email') }}</div>
                            <div class="font-medium">{{ $order->email }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold">{{ __('Name') }}</div>
                            <div class="font-medium">{{ $order->orderedBy?->name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-[color:var(--ds-text-muted)] font-semibold">{{ __('Phone') }}</div>
                            <div class="font-medium">{{ $order->phone ?? '—' }}</div>
                        </div>
                    </div>
                </x-slot:body>
            </x-ds.card>

            <x-ds.card :title="__('Promoter')">
                <x-slot:body>
                    <div class="flex items-center gap-2.5">
                        <x-ds.avatar :name="$order->requestedBy?->name ?? '?'" size="sm" />
                        <div>
                            <div class="text-sm font-medium">{{ $order->requestedBy?->name ?? '—' }}</div>
                            <div class="text-xs text-[color:var(--ds-text-muted)]">{{ $order->requestedBy?->email }}</div>
                        </div>
                    </div>
                </x-slot:body>
            </x-ds.card>

            <x-ds.card :title="__('Failure reason')" :class="$order->job_failure_reason ? '!border-[color:var(--color-danger-200)]' : ''">
                <x-slot:body>
                    @if ($order->job_failure_reason)
                        <p class="text-sm text-[color:var(--color-danger-700)] whitespace-pre-line">{{ $order->job_failure_reason }}</p>
                    @else
                        <p class="text-sm text-[color:var(--ds-text-muted)]">{{ __('No errors — everything looks good.') }}</p>
                    @endif
                </x-slot:body>
            </x-ds.card>

            <x-ds.card :title="__('Timestamps')">
                <x-slot:body>
                    <dl class="text-xs space-y-1">
                        <div class="flex justify-between gap-2">
                            <dt class="text-[color:var(--ds-text-muted)]">{{ __('Created') }}</dt>
                            <dd>{{ $order->created_at->setTimezone('Europe/Belgrade')->format('d.m.Y H:i:s') }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-[color:var(--ds-text-muted)]">{{ __('Updated') }}</dt>
                            <dd>{{ $order->updated_at->setTimezone('Europe/Belgrade')->format('d.m.Y H:i:s') }}</dd>
                        </div>
                    </dl>
                </x-slot:body>
            </x-ds.card>
        </div>
    </div>
</x-layouts.app>
