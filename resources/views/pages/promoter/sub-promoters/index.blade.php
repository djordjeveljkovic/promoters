<x-layouts.app :title="__('sub_promoters.page_title')">

    <x-ds.page-header
        :title="$festival->displayName() . ' — ' . __('sub_promoters.page_title')"
        :subtitle="__('sub_promoters.list.subtitle')"
    >
        <x-slot:actions>
            <x-ds.button variant="secondary" :href="route('promoter.dashboard', ['festival' => $festival->slug])" wire:navigate>
                ← {{ __('Dashboard') }}
            </x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    @if (session('success'))
        <x-ds.alert variant="success" class="mb-4">{{ session('success') }}</x-ds.alert>
    @endif

    <x-ds.alert variant="warning" class="mb-4">
        <div>
            <div class="font-semibold mb-1">{{ __('sub_promoters.show.warning_title') }}</div>
            <p class="text-sm">{{ __('sub_promoters.show.warning_body') }}</p>
        </div>
    </x-ds.alert>

    <x-ds.card :title="__('sub_promoters.page_title')">
        <x-slot:body>
            <form method="POST" action="{{ route('promoter.sub-promoters.store', ['festival' => $festival->slug]) }}" class="grid sm:grid-cols-4 gap-3 items-end mb-6 p-4 rounded-lg border border-dashed border-[color:var(--ds-border)] bg-[color:var(--ds-bg-subtle)]">
                @csrf
                <div class="sm:col-span-1">
                    <x-ds.field name="name" :label="__('Name')" :required="true">
                        <input type="text" name="name" class="ds-input" required>
                    </x-ds.field>
                </div>
                <div class="sm:col-span-1">
                    <x-ds.field name="email" :label="__('Email')" :required="true">
                        <input type="email" name="email" class="ds-input" required>
                    </x-ds.field>
                </div>
                <div class="sm:col-span-1">
                    <x-ds.field name="password" :label="__('Password')" :required="true" :hint="__('Min 8 chars')">
                        <input type="password" name="password" class="ds-input" minlength="8" required>
                    </x-ds.field>
                </div>
                <div>
                    <x-ds.button variant="primary" type="submit" class="w-full">
                        + {{ __('Add sub-promoter') }}
                    </x-ds.button>
                </div>
            </form>

            <x-ds.table :padded="false">
                <x-slot:head>
                    <tr>
                        <th>{{ __('sub_promoters.list.header_name') }}</th>
                        <th>{{ __('sub_promoters.list.header_email') }}</th>
                        <th class="text-right">{{ __('sub_promoters.list.header_overrides') }}</th>
                        <th class="text-right">{{ __('sub_promoters.list.header_actions') }}</th>
                    </tr>
                </x-slot:head>
                @forelse ($subPromoters as $sp)
                    <tr wire:key="sp-{{ $sp->id }}">
                        <td>
                            <div class="flex items-center gap-2.5">
                                <x-ds.avatar :name="$sp->name" size="sm" />
                                <div class="row-title">{{ $sp->name }}</div>
                            </div>
                        </td>
                        <td class="row-meta">{{ $sp->email }}</td>
                        <td class="text-right num">
                            <x-ds.badge :variant="($overrides[$sp->festivalAssignments->first()?->id] ?? collect())->count() > 0 ? 'accent' : 'neutral'">
                                {{ ($overrides[$sp->festivalAssignments->first()?->id] ?? collect())->count() }} / {{ $ticketTypes->count() }}
                            </x-ds.badge>
                        </td>
                        <td>
                            <div class="row-actions">
                                <x-ds.button variant="ghost" size="sm" :href="route('promoter.sub-promoters.show', ['festival' => $festival->slug, 'subPromoter' => $sp->id])" wire:navigate>
                                    {{ __('sub_promoters.list.set_button') }}
                                </x-ds.button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <x-ds.empty-state
                                :title="__('sub_promoters.list.no_subs_title')"
                                :message="__('sub_promoters.list.no_subs_message')"
                            />
                        </td>
                    </tr>
                @endforelse
            </x-ds.table>
        </x-slot:body>
    </x-ds.card>
</x-layouts.app>
