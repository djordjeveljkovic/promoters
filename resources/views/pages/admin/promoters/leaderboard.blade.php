<x-layouts.app :title="__('Promoter leaderboard')">
    <x-ds.page-header
        :title="__('Promoter leaderboard')"
        :subtitle="$festival?->displayName()"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('admin.dashboard', ['festival' => $festival->slug ?? null])" wire:navigate>
                ← {{ __('Dashboard') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.card>
        <x-slot:body :padded="false">
            @if ($leaderboard->isEmpty())
                <x-ds.empty-state
                    :title="__('No promoter activity yet')"
                    :message="__('As soon as promoters start selling, the top sellers will appear here.')"
                />
            @else
                <x-ds.table>
                    <x-slot:head>
                        <tr>
                            <th class="w-12">#</th>
                            <th>{{ __('Promoter') }}</th>
                            <th class="text-right">{{ __('Orders') }}</th>
                            <th class="text-right">{{ __('Tickets sold') }}</th>
                            <th class="text-right">{{ __('Revenue') }}</th>
                            <th class="text-right">{{ __('Commission') }}</th>
                        </tr>
                    </x-slot:head>
                    @foreach ($leaderboard as $i => $row)
                        @php
                            $rank = $i + 1;
                            $badge = match(true) {
                                $rank === 1 => ['🥇', 'accent'],
                                $rank === 2 => ['🥈', 'neutral'],
                                $rank === 3 => ['🥉', 'neutral'],
                                default     => ['#' . $rank, 'neutral'],
                            };
                        @endphp
                        <tr>
                            <td>
                                @if (in_array($rank, [1, 2, 3], true))
                                    <span class="text-2xl">{{ $badge[0] }}</span>
                                @else
                                    <span class="text-[color:var(--ds-text-muted)] text-sm">{{ $rank }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <x-ds.avatar :name="$row['promoter']->name" size="sm" />
                                    <div>
                                        <div class="row-title">{{ $row['promoter']->name }}</div>
                                        <div class="row-meta">{{ $row['promoter']->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-right num font-medium">{{ number_format($row['orders']) }}</td>
                            <td class="text-right num">{{ number_format($row['tickets']) }}</td>
                            <td class="text-right num font-semibold">{{ number_format($row['revenue'], 0) }} RSD</td>
                            <td class="text-right num font-semibold" style="color: var(--festival-primary);">
                                {{ number_format($row['commission'], 0) }} RSD
                            </td>
                        </tr>
                    @endforeach
                </x-ds.table>
            @endif
        </x-slot:body>
    </x-ds.card>
</x-layouts.app>
