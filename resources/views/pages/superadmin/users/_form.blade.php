@csrf

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-ds.field :label="__('Name')" name="name" :required="true" :error="$errors->first('name')">
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="ds-input" required>
    </x-ds.field>
    <x-ds.field :label="__('Email')" name="email" :required="true" :error="$errors->first('email')">
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="ds-input" required>
    </x-ds.field>
</div>

<x-ds.field :label="__('Password')" name="password" :hint="isset($user) ? __('Leave blank to keep current password.') : __('Minimum 8 characters.')" :error="$errors->first('password')">
    <input type="password" name="password" class="ds-input" {{ isset($user) ? '' : 'required' }} minlength="8">
</x-ds.field>

<x-ds.field :label="__('Role')" name="role" :required="true" :error="$errors->first('role')">
    <select name="role" class="ds-select" required>
        @foreach (['superadmin', 'admin', 'promoter', 'sub_promoter', 'buyer'] as $r)
            <option value="{{ $r }}" @selected(old('role', $user->role ?? 'promoter') === $r)>{{ __(ucfirst($r)) }}</option>
        @endforeach
    </select>
</x-ds.field>

<fieldset class="rounded-lg border border-[color:var(--ds-border)] p-4 space-y-3">
    <legend class="px-2 text-sm font-medium text-[color:var(--ds-text)]">{{ __('Festival assignments') }}</legend>
    <p class="text-xs text-[color:var(--ds-text-muted)]">{{ __('Pick festivals and the role this user will have on each.') }}</p>
    <div class="space-y-2 max-h-72 overflow-y-auto">
        @php
            $assigned = isset($user) ? $user->festivals()->get()->keyBy('id') : collect();
        @endphp
        @foreach ($festivals as $f)
            @php
                $existingRole = old('roles.' . $f->id, $assigned[$f->id]->pivot->role_in_festival ?? null);
            @endphp
            <div class="flex items-center gap-3 py-1.5">
                <label class="inline-flex items-center gap-2 text-sm flex-1 min-w-0">
                    <input type="checkbox" name="festivals[]" value="{{ $f->id }}" class="ds-checkbox" @checked(in_array($f->id, old('festivals', $assigned->keys()->all())))>
                    <span class="truncate">{{ $f->displayName() }} <span class="text-xs text-[color:var(--ds-text-muted)]">· {{ $f->location }}</span></span>
                </label>
                <select name="roles[{{ $f->id }}]" class="ds-select" style="height: 32px; padding: 0 24px 0 8px; font-size: 12px; min-width: 130px;">
                    <option value="">{{ __('No role') }}</option>
                    @foreach (['admin', 'promoter', 'sub_promoter'] as $r)
                        <option value="{{ $r }}" @selected($existingRole === $r)>{{ __(ucfirst($r)) }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</fieldset>

<div class="flex items-center justify-end gap-2 pt-3 border-t border-[color:var(--ds-divider)]">
    <x-ds.button variant="secondary" :href="route('superadmin.users.index')" wire:navigate>{{ __('Cancel') }}</x-ds.button>
    <x-ds.button variant="primary" type="submit">{{ isset($user) ? __('Save changes') : __('Create user') }}</x-ds.button>
</div>
