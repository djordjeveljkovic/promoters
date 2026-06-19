<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

            <x-flash-messages/>

            <div class="max-w-full rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
                {{ $slot }}
            </div>
        </div>
    </flux:main>

</x-layouts.app.sidebar>
