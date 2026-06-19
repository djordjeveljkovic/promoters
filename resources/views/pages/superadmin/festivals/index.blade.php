<x-layouts.app :title="__('Festivals')">
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">🎪 {{ __('Festivals') }}</h1>
            <a href="{{ route('superadmin.festivals.create') }}"
               class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">
                + {{ __('New festival') }}
            </a>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-gray-500 block">{{ __('Search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="px-3 py-2 border rounded-lg" placeholder="REFEST, 2026...">
            </div>
            <div>
                <label class="text-xs text-gray-500 block">{{ __('Status') }}</label>
                <select name="status" class="px-3 py-2 border rounded-lg">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['draft', 'active', 'archived'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ __($s) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg">{{ __('Filter') }}</button>
        </form>

        <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full">
                <thead class="text-left text-xs uppercase text-gray-500 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3">{{ __('Festival') }}</th>
                        <th class="px-4 py-3">{{ __('Year') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Ticket types') }}</th>
                        <th class="px-4 py-3">{{ __('Orders') }}</th>
                        <th class="px-4 py-3">{{ __('Tickets') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($festivals as $f)
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $f->displayName() }}</div>
                            <div class="text-xs text-gray-500">{{ $f->location }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono">{{ $f->year }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $statusColors[$f->status] ?? '' }}">{{ __($f->status) }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $f->ticket_types_count }}</td>
                        <td class="px-4 py-3">{{ $f->orders_count }}</td>
                        <td class="px-4 py-3">{{ $f->tickets_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('superadmin.festivals.assignments', $f) }}" class="text-blue-600 hover:underline text-sm">{{ __('Users') }}</a>
                            <a href="{{ route('superadmin.festivals.edit', $f) }}" class="ml-3 text-pink-600 hover:underline text-sm">{{ __('Edit') }}</a>
                            @if ($f->status === 'draft')
                                <form action="{{ route('superadmin.festivals.destroy', $f) }}" method="POST" class="inline ml-3"
                                      onsubmit="return confirm('{{ __('Delete this draft festival?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline text-sm">{{ __('Delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">{{ __('No festivals yet — click "New festival" to create one.') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $festivals->links() }}</div>
        </div>
    </div>
</x-layouts.app>