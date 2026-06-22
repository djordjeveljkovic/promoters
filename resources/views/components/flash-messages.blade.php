{{--
    Flash messages — render any session success / error / info plus
    validation errors as a single inline stack of design-system alerts.
--}}
@php
    $messages = [];

    if (session('success')) $messages[] = ['variant' => 'success', 'text' => session('success')];
    if (session('error'))   $messages[] = ['variant' => 'danger',  'text' => session('error')];
    if (session('info'))    $messages[] = ['variant' => 'info',    'text' => session('info')];
    if (session('warning')) $messages[] = ['variant' => 'warning', 'text' => session('warning')];
    if (session('status'))  $messages[] = ['variant' => 'info',    'text' => session('status')];

    $hasErrors = $errors->any();
@endphp

@if (!empty($messages) || $hasErrors)
    <div
        x-data="{ visible: true }"
        x-show="visible"
        x-transition.opacity.duration.200ms
        class="ds-page pt-0"
        style="padding-top: 16px;"
    >
        <div class="ds-stack-sm">
            @foreach ($messages as $m)
                <x-ds.alert :variant="$m['variant']">
                    {{ $m['text'] }}
                </x-ds.alert>
            @endforeach

            @if ($hasErrors)
                <x-ds.alert variant="danger" title="Please correct the following:">
                    <ul class="list-disc list-inside space-y-0.5 mt-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </x-ds.alert>
            @endif
        </div>
    </div>
@endif
