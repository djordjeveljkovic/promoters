<x-layouts.app :title="__('Create user')">
    <x-ds.page-header
        :title="__('Create user')"
        :subtitle="__('Add a new user to the platform.')"
    >
        <x-slot:actions>
            <x-ds.button variant="ghost" :href="route('superadmin.users.index')" wire:navigate>← {{ __('Back to list') }}</x-ds.button>
        </x-slot:actions>
    </x-ds.page-header>

    <x-ds.card class="max-w-2xl">
        <form method="POST" action="{{ route('superadmin.users.store') }}" class="space-y-5">
            @include('pages.superadmin.users._form')
        </form>
    </x-ds.card>
</x-layouts.app>
