<x-layouts.app :title="__('ticket_types.page_title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="max-w-full rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('ticket_types.main_heading') }}</h1>
                <a href="{{ route('admin.ticket-types.create', $festival) }}"
                   class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    {{ __('ticket_types.create_button') }}
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('ticket_types.table.header_name') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('ticket_types.table.header_price') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('ticket_types.table.header_photo') }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ __('ticket_types.table.header_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        @forelse ($ticketTypes as $ticketType)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $ticketType->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ number_format($ticketType->price, 2) }} {{ __('ticket_types.currency_symbol') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                    @if ($ticketType->photo_path)
                                        {{-- Assuming photo_path is relative to public directory if using asset directly --}}
                                        {{-- If using storage symlink, it might be asset('storage/' . $ticketType->photo_path) --}}
                                        <img src="{{ asset($ticketType->photo_path) }}" alt="{{ $ticketType->name }}" class="h-10 w-10 rounded-md object-cover">
                                    @else
                                        <span class="text-xs italic">{{ __('ticket_types.table.no_photo') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.ticket-types.edit', ['festival' => $festival->slug, 'id' => $ticketType]) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('ticket_types.table.action_edit') }}</a>
                                    <form action="{{ route('admin.ticket-types.destroy', ['festival' => $festival->slug, 'id' => $ticketType]) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('ticket_types.table.delete_confirm_message') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">{{ __('ticket_types.table.action_delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                    {{ __('ticket_types.table.no_data_message') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($ticketTypes->hasPages())
                <div class="mt-6">
                    {{ $ticketTypes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
