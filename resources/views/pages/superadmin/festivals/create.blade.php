<x-layouts.app :title="__('New festival')">
    <x-ds.page-header
        :title="__('New festival')"
        :subtitle="__('Create a festival edition (e.g. REFEST 2026, Lovefest 2027).')"
    />

    <x-ds.card class="max-w-4xl">
        <form action="{{ route('superadmin.festivals.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @include('pages.superadmin.festivals._form')
        </form>
    </x-ds.card>
</x-layouts.app>
