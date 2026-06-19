@csrf
@if (isset($festival) && $festival->exists)
    @method('PUT')
@endif

<div class="grid lg:grid-cols-2 gap-6">
    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Name') }} *</label>
            <input type="text" name="name" required value="{{ old('name', $festival->name ?? '') }}"
                   class="w-full px-3 py-2 border rounded-lg" placeholder="REFEST, Lovefest…">
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Year') }} *</label>
            <input type="number" name="year" required min="2000" max="2100"
                   value="{{ old('year', $festival->year ?? date('Y')) }}"
                   class="w-full px-3 py-2 border rounded-lg">
            @error('year') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Tagline') }}</label>
            <input type="text" name="tagline" maxlength="160"
                   value="{{ old('tagline', $festival->tagline ?? '') }}"
                   class="w-full px-3 py-2 border rounded-lg" placeholder="Where the wild things dance.">
        </div>

        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Description') }}</label>
            <textarea name="description" rows="4" maxlength="5000"
                      class="w-full px-3 py-2 border rounded-lg">{{ old('description', $festival->description ?? '') }}</textarea>
        </div>

        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Location') }}</label>
            <input type="text" name="location" maxlength="160"
                   value="{{ old('location', $festival->location ?? '') }}"
                   class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm font-medium block mb-1">{{ __('Start date') }}</label>
                <input type="date" name="start_date"
                       value="{{ old('start_date', optional($festival->start_date ?? null)->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="text-sm font-medium block mb-1">{{ __('End date') }}</label>
                <input type="date" name="end_date"
                       value="{{ old('end_date', optional($festival->end_date ?? null)->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border rounded-lg">
                @error('end_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm font-medium block mb-1">{{ __('Primary colour') }}</label>
                <input type="color" name="primary_color"
                       value="{{ old('primary_color', $festival->primary_color ?? '#ff2d92') }}"
                       class="w-full h-10 border rounded-lg cursor-pointer">
            </div>
            <div>
                <label class="text-sm font-medium block mb-1">{{ __('Secondary colour') }}</label>
                <input type="color" name="secondary_color"
                       value="{{ old('secondary_color', $festival->secondary_color ?? '#5ce1ff') }}"
                       class="w-full h-10 border rounded-lg cursor-pointer">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Status') }} *</label>
            <select name="status" required class="w-full px-3 py-2 border rounded-lg">
                @foreach (['draft', 'active', 'archived'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $festival->status ?? 'draft') === $s)>{{ __($s) }}</option>
                @endforeach
            </select>
            @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="is_public" value="0">
                <input type="checkbox" name="is_public" value="1"
                       @checked(old('is_public', $festival->is_public ?? true))>
                <span class="text-sm">{{ __('Visible to the public (landing page)') }}</span>
            </label>
        </div>

        <div>
            <label class="text-sm font-medium block mb-1">{{ __('Logo') }}</label>
            <input type="file" name="logo" accept="image/*" class="w-full px-3 py-2 border rounded-lg">
            @if (!empty($festival->logo_path))
                <img src="{{ asset($festival->logo_path) }}" class="mt-2 h-16 rounded" alt="">
            @endif
            @error('logo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="text-xs text-gray-500">
            {{ __('Slug') }}: <span class="font-mono">{{ $festival->slug ?? '—' }}</span>
        </div>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="px-5 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">
        {{ isset($festival) && $festival->exists ? __('Save changes') : __('Create festival') }}
    </button>
    <a href="{{ route('superadmin.festivals.index') }}" class="px-5 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
        {{ __('Cancel') }}
    </a>
</div>