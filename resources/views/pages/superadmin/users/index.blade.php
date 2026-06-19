<x-layouts.app :title="__('Users')">
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">👥 {{ __('Users') }}</h1>
            <a href="{{ route('superadmin.users.create') }}"
               class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">+ {{ __('New user') }}</a>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Name or email..." class="px-3 py-2 border rounded-lg">
            <select name="role" class="px-3 py-2 border rounded-lg">
                <option value="">{{ __('All roles') }}</option>
                @foreach (['superadmin', 'admin', 'promoter', 'sub_promoter', 'buyer'] as $r)
                    <option value="{{ $r }}" @selected(request('role') === $r)>{{ __($r) }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg">{{ __('Filter') }}</button>
        </form>

        <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full">
                <thead class="text-left text-xs uppercase text-gray-500 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('Email') }}</th>
                        <th class="px-4 py-3">{{ __('Role') }}</th>
                        <th class="px-4 py-3">{{ __('Festivals') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($users as $u)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $u->email }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs
                                @switch($u->role)
                                    @case('superadmin')    bg-purple-100 text-purple-800 @break
                                    @case('admin')        bg-pink-100 text-pink-800 @break
                                    @case('promoter')     bg-blue-100 text-blue-800 @break
                                    @case('sub_promoter') bg-yellow-100 text-yellow-800 @break
                                    @default              bg-gray-100 text-gray-800
                                @endswitch
                            ">{{ __($u->role) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @forelse ($u->festivals as $f)
                                <span class="inline-block px-2 py-0.5 mr-1 mb-1 rounded bg-gray-100 dark:bg-gray-800">
                                    {{ $f->displayName() }} <span class="text-gray-500">· {{ $f->pivot->role_in_festival }}</span>
                                </span>
                            @empty
                                <span class="text-gray-400">{{ __('none') }}</span>
                            @endforelse
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('superadmin.users.edit', $u) }}" class="text-pink-600 hover:underline text-sm">{{ __('Edit') }}</a>
                            @if ($u->id !== auth()->id())
                                <form action="{{ route('superadmin.users.destroy', $u) }}" method="POST" class="inline ml-3"
                                      onsubmit="return confirm('{{ __('Delete this user?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline text-sm">{{ __('Delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">{{ __('No users yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $users->links() }}</div>
        </div>
    </div>
</x-layouts.app>