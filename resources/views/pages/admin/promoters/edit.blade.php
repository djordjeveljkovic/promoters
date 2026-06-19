<x-layouts.app :title="__('promoters.edit_form.page_title')">
    {{-- Assuming the div below is for layout and doesn't need specific class for this page --}}
    {{-- <div class="max-w-3xl mx-auto w-full rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800"> --}}
        <div class="flex items-center justify-between mb-6"> {{-- Added mb-6 for spacing similar to other pages --}}
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('promoters.edit_form.main_heading') }}</h1>
            {{-- Optional: Add a back button if standard on your edit pages --}}
            {{-- <a href="{{ route('admin.promoters.index', $festival) }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">&larr; Back to Promoters</a> --}}
        </div>

        <form method="POST" action="{{ route('admin.promoters.update', ['festival' => $festival->slug, 'id' => $promoter->id]) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoters.edit_form.name_label') }}</label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name', $promoter->name) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                       placeholder="{{ __('promoters.edit_form.name_placeholder') }}"
                       required />
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoters.edit_form.email_label') }}</label>
                <input type="email"
                       name="email"
                       id="email"
                       value="{{ old('email', $promoter->email) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                       placeholder="you@example.com" {{-- Example format, usually not translated --}}
                       required />
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoters.edit_form.password_label') }}</label>
                <input type="password"
                       name="password"
                       id="password"
                       class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                       placeholder="{{ __('promoters.edit_form.password_placeholder_edit') }}"
                       aria-describedby="password-help" />
                <p id="password-help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('promoters.edit_form.password_help_text') }}</p>
                @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="paid" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoters.edit_form.paid_label') }}</label>
                <input type="text"
                       name="paid"
                       id="paid"
                       value="{{ old('paid', $promoter->paid) }}" {{-- Assuming $promoter->paid exists --}}
                       class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                       placeholder="{{ __('promoters.edit_form.paid_placeholder') }}"
                       required /> {{-- Consider if 'paid' amount is always required on update --}}
                @error('paid')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4">
                <a href="{{ route('admin.promoters.index', $festival) }}" {{-- Corrected route from 'promoters' to 'admin.promoters.index' for consistency --}}
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800 cursor-pointer">
                    {{ __('promoters.edit_form.cancel_button') }}
                </a>
                <button type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-black px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 cursor-pointer">
                    {{ __('promoters.edit_form.update_button') }}
                </button>
            </div>
        </form>
    {{-- </div> --}} {{-- Closing the div if you wrap the form and heading in it --}}
</x-layouts.app>
