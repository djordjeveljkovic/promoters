<x-layouts.app :title="__('New user')">
    <div class="p-6 max-w-5xl">
        <h1 class="text-2xl font-bold mb-6">+ {{ __('New user') }}</h1>
        <form action="{{ route('superadmin.users.store') }}" method="POST"
              class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            @include('pages.superadmin.users._form')
        </form>
    </div>
</x-layouts.app>