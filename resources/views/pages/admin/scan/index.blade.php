<x-layouts.app :title="__('Scan tickets')">
    <x-ds.page-header
        :title="__('Scan tickets')"
        :subtitle="$festival?->displayName()"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('admin.dashboard', ['festival' => $festival->slug ?? null])" wire:navigate>
                ← {{ __('Dashboard') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <div class="grid lg:grid-cols-3 gap-5">
        {{-- Scanner ----------------------------------------------------- --}}
        <div class="lg:col-span-2 space-y-5">
            <x-ds.card :title="__('Scan a ticket')">
                <x-slot:body>
                    <div class="space-y-4">
                        <div id="scanner-region" class="relative aspect-video rounded-lg overflow-hidden bg-[color:var(--ds-bg-subtle)] border border-[color:var(--ds-border)] flex items-center justify-center">
                            <video id="scanner-video" class="w-full h-full object-cover" autoplay playsinline muted></video>
                            <canvas id="scanner-canvas" class="hidden"></canvas>
                            <div id="scanner-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 text-[color:var(--ds-text-muted)]">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="mb-2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="3" height="3"/><rect x="18" y="18" width="3" height="3"/><rect x="18" y="14" width="3" height="3"/></svg>
                                <p class="text-sm">{{ __('Camera will start when you click "Start scanner"') }}</p>
                            </div>
                            <div id="scanner-frame" class="absolute inset-8 border-2 border-dashed border-[color:var(--ds-accent)] rounded-lg pointer-events-none hidden"></div>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <x-ds.button variant="primary" type="button" id="scanner-start">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                {{ __('Start scanner') }}
                            </x-ds.button>
                            <x-ds.button variant="secondary" type="button" id="scanner-stop" class="hidden">
                                {{ __('Stop scanner') }}
                            </x-ds.button>
                            <span class="text-xs text-[color:var(--ds-text-muted)]">{{ __('Or paste/type the code below') }}</span>
                        </div>

                        <form id="manual-form" class="flex items-stretch gap-2">
                            <input type="text" id="manual-code" class="ds-input flex-1 font-mono" placeholder="{{ __('UUID / QR code') }}" autocomplete="off">
                            <x-ds.button variant="primary" type="submit">
                                {{ __('Check') }}
                            </x-ds.button>
                        </form>
                    </div>
                </x-slot:body>
            </x-ds.card>

            <x-ds.card :title="__('Result')" :id="'result-card'">
                <x-slot:body>
                    <div id="result-placeholder" class="text-center py-8 text-[color:var(--ds-text-subtle)]">
                        {{ __('Scan a ticket to see the result.') }}
                    </div>
                    <div id="result-ok" class="hidden space-y-3">
                        <x-ds.alert variant="success">
                            <div class="font-semibold">{{ __('Ticket valid') }}</div>
                            <div class="text-sm" id="result-ok-message"></div>
                        </x-ds.alert>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-[color:var(--ds-text-muted)]">{{ __('Code') }}</dt><dd class="font-mono" id="result-code"></dd>
                            <dt class="text-[color:var(--ds-text-muted)]">{{ __('Order #') }}</dt><dd id="result-order"></dd>
                            <dt class="text-[color:var(--ds-text-muted)]">{{ __('Email') }}</dt><dd id="result-email"></dd>
                        </dl>
                    </div>
                    <div id="result-error" class="hidden">
                        <x-ds.alert variant="danger">
                            <div class="font-semibold" id="result-error-title">{{ __('Could not scan') }}</div>
                            <div class="text-sm" id="result-error-message"></div>
                        </x-ds.alert>
                    </div>
                </x-slot:body>
            </x-ds.card>
        </div>

        {{-- Recent ------------------------------------------------------ --}}
        <div class="space-y-5">
            <x-ds.card :title="__('Recently scanned')">
                <x-slot:body>
                    @if ($recent->isEmpty())
                        <p class="text-sm text-[color:var(--ds-text-subtle)] text-center py-4">{{ __('Nothing yet.') }}</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($recent as $t)
                                <li class="flex items-center gap-2 text-sm">
                                    <code class="text-[10px] text-[color:var(--ds-text-muted)] flex-1 truncate">{{ $t->code }}</code>
                                    <span class="text-xs text-[color:var(--ds-text-muted)]">{{ $t->scanned_at?->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-slot:body>
            </x-ds.card>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/umd/zxing.min.js"></script>
    <script>
        (function () {
            const scanUrl = '{{ route('admin.scan.scan', ['festival' => $festival->slug ?? null]) }}';
            const resultOk = document.getElementById('result-ok');
            const resultErr = document.getElementById('result-error');
            const placeholder = document.getElementById('result-placeholder');
            const okMsg = document.getElementById('result-ok-message');
            const errTitle = document.getElementById('result-error-title');
            const errMsg = document.getElementById('result-error-message');
            const codeEl = document.getElementById('result-code');
            const orderEl = document.getElementById('result-order');
            const emailEl = document.getElementById('result-email');

            const renderOk = (t) => {
                placeholder.classList.add('hidden');
                resultErr.classList.add('hidden');
                resultOk.classList.remove('hidden');
                okMsg.textContent = `Marked as scanned at ${new Date().toLocaleTimeString()}`;
                codeEl.textContent = t.code;
                orderEl.textContent = t.order?.order_number ?? '—';
                emailEl.textContent = t.order?.email ?? '—';
            };
            const renderErr = (title, msg) => {
                placeholder.classList.add('hidden');
                resultOk.classList.add('hidden');
                resultErr.classList.remove('hidden');
                errTitle.textContent = title;
                errMsg.textContent = msg;
            };

            const send = async (code) => {
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                        || document.querySelector('input[name="_token"]')?.value;
                    const r = await fetch(scanUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ code }),
                    });
                    const data = await r.json();
                    if (data.ok) renderOk(data.ticket);
                    else renderErr(data.error || 'Unknown error', data.error || '');
                } catch (e) {
                    renderErr('Network error', e.message);
                }
            };

            // Manual entry
            const form = document.getElementById('manual-form');
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const code = document.getElementById('manual-code').value.trim();
                if (code) send(code);
            });

            // Camera scanner using @zxing/library (loaded async)
            let reader = null;
            const startBtn = document.getElementById('scanner-start');
            const stopBtn = document.getElementById('scanner-stop');
            const placeholderEl = document.getElementById('scanner-placeholder');
            const frameEl = document.getElementById('scanner-frame');
            const video = document.getElementById('scanner-video');

            startBtn.addEventListener('click', async () => {
                if (typeof ZXing === 'undefined') {
                    renderErr('Scanner library failed to load', 'Check your internet connection and try again.');
                    return;
                }
                placeholderEl.classList.add('hidden');
                frameEl.classList.remove('hidden');
                startBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');
                reader = new ZXing.BrowserMultiFormatReader();
                try {
                    await reader.decodeFromVideoDevice(null, video, (result, err) => {
                        if (result) {
                            send(result.getText());
                            // Pause briefly to avoid duplicate scans
                            reader.reset();
                            setTimeout(() => reader.decodeFromVideoDevice(null, video, arguments.callee), 1500);
                        }
                    });
                } catch (e) {
                    renderErr('Camera access denied', e.message);
                }
            });

            stopBtn.addEventListener('click', () => {
                if (reader) reader.reset();
                placeholderEl.classList.remove('hidden');
                frameEl.classList.add('hidden');
                startBtn.classList.remove('hidden');
                stopBtn.classList.add('hidden');
            });
        })();
    </script>
    @endpush
</x-layouts.app>
