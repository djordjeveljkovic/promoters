<x-layouts.app :title="__('Edit festival')">
    <div class="p-6 max-w-4xl">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ __('Edit festival') }} — {{ $festival->displayName() }}</h1>
                <p class="text-sm text-gray-500">/{{ $festival->slug }}</p>
            </div>
            <a href="{{ route('superadmin.festivals.assignments', $festival) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ __('Manage users') }}
            </a>
        </div>

        <form action="{{ route('superadmin.festivals.update', $festival) }}" method="POST" enctype="multipart/form-data"
              class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            @include('pages.superadmin.festivals._form')
        </form>
    </div>
</x-layouts.app>