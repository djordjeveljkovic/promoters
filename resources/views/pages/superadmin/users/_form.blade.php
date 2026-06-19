@csrf
@if (isset($user) && $user->exists)
    @method('PUT')
@endif

<div class="grid lg:grid-cols-2 gap-6">
    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Name') }} *</label>
            <input type="text" name="name" required value="{{ old('name', $user->name ?? '') }}"
                   class="w-full px-3 py-2 border rounded-lg">
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Email') }} *</label>
            <input type="email" name="email" required value="{{ old('email', $user->email ?? '') }}"
                   class="w-full px-3 py-2 border rounded-lg">
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Password') }} {{ isset($user) && $user->exists ? __('(leave blank to keep current)') : '*' }}</label>
            <input type="password" name="password" minlength="8"
                   class="w-full px-3 py-2 border rounded-lg" autocomplete="new-password">
            @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Role') }} *</label>
            <select name="role" required class="w-full px-3 py-2 border rounded-lg">
                @foreach (['superadmin', 'admin', 'promoter', 'sub_promoter', 'buyer'] as $r)
                    <option value="{{ $r }}" @selected(old('role', $user->role ?? 'promoter') === $r)>{{ __($r) }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">
                {{ __('superadmin') }}: {{ __('global, manages all festivals') }}.
                {{ __('admin') }}: {{ __('per-festival, can be promoted to global by superadmin') }}.
            </p>
            @error('role') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Festival assignments') }}</label>
            <p class="text-xs text-gray-500 mb-2">{{ __('Tick the festivals this user can access, then choose their role on each one.') }}</p>
            <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                @foreach ($festivals as $f)
                    @php
                        $checked = isset($assignments) && $assignments->has($f->id);
                        $currentRole = isset($assignments) && $assignments->has($f->id)
                            ? $assignments->get($f->id)->pivot->role_in_festival
                            : 'promoter';
                    @endphp
                    <div class="flex items-center gap-3 p-2 rounded border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" name="festivals[]" value="{{ $f->id }}" id="f_{{ $f->id }}"
                               @checked(old("festivals.$f->id", $checked))>
                        <label for="f_{{ $f->id }}" class="flex-1 cursor-pointer">
                            <span class="font-medium">{{ $f->displayName() }}</span>
                            <span class="ml-2 text-xs text-gray-500">{{ $f->location }}</span>
                        </label>
                        <select name="roles[{{ $f->id }}]" class="text-sm px-2 py-1 border rounded">
                            @foreach (['admin', 'promoter', 'sub_promoter'] as $r)
                                <option value="{{ $r }}" @selected(old("roles.$f->id", $currentRole) === $r)>{{ __($r) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="px-5 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">
        {{ isset($user) && $user->exists ? __('Save changes') : __('Create user') }}
    </button>
    <a href="{{ route('superadmin.users.index') }}" class="px-5 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">{{ __('Cancel') }}</a>
</div>