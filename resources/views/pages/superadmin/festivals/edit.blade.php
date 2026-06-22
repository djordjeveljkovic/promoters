<x-layouts.app :title="__('Edit festival')">
    <x-ds.page-header
        :title="__('Edit festival')"
        :subtitle="$festival->displayName()"
    />

    <x-ds.card class="max-w-4xl">
        <form action="{{ route('superadmin.festivals.update', $festival) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @include('pages.superadmin.festivals._form')
        </form>
    </x-ds.card>
</x-layouts.app>
