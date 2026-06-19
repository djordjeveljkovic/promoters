<x-layouts.app :title="__('promoters.create_form.page_title')">
    {{-- Assuming the div below is for layout and doesn't need specific class for this page --}}
    {{-- <div class="max-w-3xl mx-auto w-full rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800"> --}}
        <div class="flex items-center justify-between mb-6"> {{-- Added mb-6 for consistency --}}
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('promoters.create_form.main_heading') }}</h1>
        </div>

        <form method="POST" action="{{ route('admin.promoters.store', $festival) }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoters.create_form.name_label') }}</label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name', '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                       placeholder="{{ __('promoters.create_form.name_placeholder') }}"
                       required />
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoters.create_form.email_label') }}</label>
                <input type="email"
                       name="email"
                       id="email"
                       value="{{ old('email', '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                       placeholder="you@example.com" {{-- Example format, typically not translated --}}
                       required />
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoters.create_form.password_label') }}</label>
                <input type="password"
                       name="password"
                       id="password"
                       class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm p-2.5"
                       placeholder="{{ __('promoters.create_form.password_placeholder_create') }}"
                       aria-describedby="password-help"
                       required {{-- Assuming password is required for new promoter --}}
                       />
                <p id="password-help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('promoters.create_form.password_help_text_create') }}</p>
                @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4">
                <a href="{{ route('admin.promoters.index', $festival) }}" {{-- Corrected from 'promoters' to be consistent with admin routes --}}
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800 cursor-pointer">
                    {{ __('promoters.create_form.cancel_button') }}
                </a>
                <button type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-black px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 cursor-pointer">
                    {{ __('promoters.create_form.create_button') }}
                </button>
            </div>
        </form>
    {{-- </div> --}}
</x-layouts.app>
