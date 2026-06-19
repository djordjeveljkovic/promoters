{{--
    Default layout for full-page Livewire components (Profile, Password, Login, etc.).

    Livewire's default `component_layout` is `layouts::app`, which maps to
    this file via the `layouts` namespace. We just forward to the existing
    `<x-layouts.app>` Blade component (which itself wraps the sidebar).
--}}
<x-layouts.app :title="$title ?? null">
    {{ $slot ?? '' }}
</x-layouts.app>
