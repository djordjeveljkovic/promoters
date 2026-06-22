<x-layouts.app :title="__('Edit user')">
    <x-ds.page-header
        :title="__('Edit user')"
        :subtitle="$user->name"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('superadmin.users.index')" wire:navigate>← {{ __('Back to list') }}</x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.card class="max-w-2xl">
        <form method="POST" action="{{ route('superadmin.users.update', $user) }}" class="space-y-5">
            @method('PUT')
            @include('pages.superadmin.users._form')
        </form>
    </x-ds.card>
</x-layouts.app>
