<x-layouts.app :title="__('Manage festival users')">
    <div class="p-6 max-w-5xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ $festival->displayName() }} — {{ __('Users') }}</h1>
                <p class="text-sm text-gray-500">{{ __('Assign admins, promoters and sub-promoters to this festival.') }}</p>
            </div>
            <a href="{{ route('superadmin.festivals.edit', $festival) }}"
               class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                ← {{ __('Back to festival') }}
            </a>
        </div>

        <div class="grid lg:grid-cols-3 gap-4">
            @php
                $sections = [
                    ['title' => __('Admins'),       'users' => $festival->admins],
                    ['title' => __('Promoters'),    'users' => $festival->promoters],
                    ['title' => __('Sub-promoters'),'users' => $festival->subPromoters],
                ];
            @endphp
            @foreach ($sections as $section)
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h2 class="font-semibold mb-3">{{ $section['title'] }} ({{ $section['users']->count() }})</h2>
                    <ul class="space-y-2">
                        @forelse ($section['users'] as $u)
                            <li class="flex items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-800 pb-2">
                                <div>
                                    <div class="text-sm font-medium">{{ $u->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $u->email }}</div>
                                </div>
                                <form action="{{ route('superadmin.festivals.assignments.destroy', [$festival, $u]) }}"
                                      method="POST" onsubmit="return confirm('{{ __('Remove this user?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline text-xs">{{ __('Remove') }}</button>
                                </form>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">{{ __('No one yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="font-semibold mb-3">{{ __('Add a user to this festival') }}</h2>
            <form action="{{ route('superadmin.festivals.assignments.store', $festival) }}" method="POST"
                  class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-gray-500 block">{{ __('User') }}</label>
                    <select name="user_id" required class="w-full px-3 py-2 border rounded-lg">
                        <option value="">— {{ __('Choose a user') }} —</option>
                        @foreach ($candidates as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }} ({{ $u->role }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block">{{ __('Role on this festival') }}</label>
                    <select name="role_in_festival" required class="px-3 py-2 border rounded-lg">
                        <option value="admin">{{ __('Admin') }}</option>
                        <option value="promoter">{{ __('Promoter') }}</option>
                        <option value="sub_promoter">{{ __('Sub-promoter') }}</option>
                    </select>
                </div>
                <button class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">{{ __('Assign') }}</button>
            </form>
        </div>
    </div>
</x-layouts.app>