<x-layouts.app :title="__('New festival')">
    <div class="p-6 max-w-4xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">+ {{ __('New festival') }}</h1>
            <p class="text-sm text-gray-500">{{ __('Create a festival edition (e.g. REFEST 2026, Lovefest 2027).') }}</p>
        </div>

        <form action="{{ route('superadmin.festivals.store') }}" method="POST" enctype="multipart/form-data"
              class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            @include('pages.superadmin.festivals._form')
        </form>
    </div>
</x-layouts.app>