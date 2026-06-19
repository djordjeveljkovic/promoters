<x-layouts.app :title="__('promoters.page_title')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ __('promoters.main_heading') }}</h1>

            <a href="{{ route('admin.promoters.create', $festival) }}"
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ __('promoters.add_promoter_button') }}
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
            <div class="relative overflow-x-auto">
                <table id="promoters-table" class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-100 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="px-6 py-3">{{ __('promoters.table.header_name') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('promoters.table.header_joined_date') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoters.table.header_tickets_sold') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoters.table.header_made_for_organizers') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoters.table.header_commission_earned') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoters.table.header_paid_to_organizers') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoters.table.header_owed_to_organizers') }}</th>
                            <th scope="col" class="px-6 py-3 text-center">{{ __('promoters.table.header_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($promoters as $promoter)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $promoter->name }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-300">{{ $promoter->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ $promoter->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ $promoter->ticketsSoldCount ?? 0 }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    {{-- Assuming you want to format this as currency --}}
                                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ number_format($promoter->madeForOrganizers ?? 0.00, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ number_format($promoter->totalCommissionEarned ?? 0.00, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ number_format($promoter->amountPaidToOrganizers ?? 0.00, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-semibold {{ ($promoter->amountOwedToOrganizers ?? 0.00) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ number_format($promoter->amountOwedToOrganizers ?? 0.00, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="{{ route('admin.promoters.edit', ['festival' => $festival->slug, 'id' => $promoter->id]) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">{{ __('promoters.table.action_edit') }}</a>
                                    <form action="{{ route('admin.promoters.destroy', ['festival' => $festival->slug, 'id' => $promoter->id]) }}" method="POST" class="inline ml-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors" onclick="return confirm('{{ __('promoters.table.delete_confirm_message') }}')">{{ __('promoters.table.action_delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('promoters.table.no_promoters_header') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('promoters.table.no_promoters_message') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (method_exists($promoters, 'hasPages') && $promoters->hasPages())
            <div class="mt-8">
                {{ $promoters->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
