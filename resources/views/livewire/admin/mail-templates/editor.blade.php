<div class="p-4 sm:p-6 space-y-4">

    {{-- ---------- Header ---------- --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                📧 {{ __('mail_templates.page_title') }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('mail_templates.page_intro') }}
            </p>
        </div>
        @if ($editing)
            <button wire:click="cancelEdit" class="text-sm text-gray-500 hover:text-gray-700">
                ← {{ __('mail_templates.editor.back_to_list') }}
            </button>
        @endif
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="rounded-md bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-200 px-4 py-2 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @error('html_body') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    @error('key')      <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    @error('name')     <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    @error('subject')  <p class="text-sm text-red-600">{{ $message }}</p> @enderror

    @if (!$editing && !$key)
        {{-- ============================================================
             LIST VIEW
             ============================================================ --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">{{ __('mail_templates.list.header_template') }}</th>
                        <th class="px-4 py-3">{{ __('mail_templates.list.header_festival') }}</th>
                        <th class="px-4 py-3">{{ __('mail_templates.list.header_subject') }}</th>
                        <th class="px-4 py-3">{{ __('mail_templates.list.header_version') }}</th>
                        <th class="px-4 py-3">{{ __('mail_templates.list.header_updated') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('mail_templates.list.header_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($this->templates as $tpl)
                        <tr wire:key="row-{{ $tpl->id }}">
                            <td class="px-4 py-3">
                                <div class="font-semibold">{{ $tpl->name }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $tpl->key }}</div>
                                @if (!$tpl->is_active)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">{{ __('mail_templates.list.disabled_badge') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($tpl->isGlobal())
                                    <span class="text-xs px-2 py-0.5 rounded bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-200">
                                        {{ __('mail_templates.list.global_badge') }}
                                    </span>
                                @else
                                    <div class="text-xs">
                                        <div class="font-semibold">{{ $tpl->festival?->displayName() ?? '—' }}</div>
                                        <div class="text-gray-500">{{ $tpl->festival?->location }}</div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs font-mono text-gray-700 dark:text-gray-300">
                                {{ Str::limit($tpl->subject, 60) }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">v{{ $tpl->version }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $tpl->updated_at?->diffForHumans() }}
                                @if ($tpl->editor)
                                    <div class="text-[10px] text-gray-400">by {{ $tpl->editor->name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <button wire:click="edit({{ $tpl->id }})" class="text-pink-600 hover:underline text-xs">
                                    {{ __('Edit') }}
                                </button>
                                <button
                                    wire:click="delete({{ $tpl->id }})"
                                    wire:confirm="{{ __('mail_templates.confirm.delete') }}"
                                    class="text-red-600 hover:underline text-xs">
                                    {{ __('Delete') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                {{ __('mail_templates.list.no_templates') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Quick-create: pick a key, then either as global or for a specific festival --}}
        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-4">
            <h2 class="font-semibold mb-3 text-sm text-gray-700 dark:text-gray-300">
                + {{ __('mail_templates.create_new') }}
            </h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($this->templateKeys as $key => $label)
                    <details class="relative">
                        <summary class="cursor-pointer list-none px-3 py-2 rounded-lg bg-pink-600 text-white text-xs font-medium hover:bg-pink-700">
                            + {{ $label }}
                        </summary>
                        <div class="absolute z-10 mt-1 right-0 w-72 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg p-2 text-sm">
                            <button wire:click="newGlobal('{{ $key }}')" class="block w-full text-left px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">
                                <span class="font-semibold">🌐 {{ __('mail_templates.as_global_default') }}</span>
                                <div class="text-xs text-gray-500">{{ __('mail_templates.as_global_help') }}</div>
                            </button>
                            <div class="border-t border-gray-100 dark:border-gray-800 my-1"></div>
                            <div class="text-[10px] uppercase text-gray-500 px-3 py-1">{{ __('mail_templates.override_for') }}</div>
                            @foreach ($this->festivals as $f)
                                <button wire:click="newForFestival('{{ $key }}', {{ $f->id }})" class="block w-full text-left px-3 py-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-xs">
                                    🎪 {{ $f->displayName() }}
                                </button>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        </div>

    @else
        {{-- ============================================================
             EDITOR VIEW
             ============================================================ --}}
        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-[1fr_460px] gap-4">

            {{-- ============= Left: form + source ============= --}}
            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-500">{{ __('mail_templates.editor.internal_name') }}</label>
                        <input type="text" wire:model="name" class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700" required>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">{{ __('mail_templates.editor.subject') }}</label>
                        <input type="text" wire:model.live="subject" class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700 font-mono text-sm" placeholder="Vaše ulaznice za {{ '$' }}{{ '$' }}festival_name">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs text-gray-500">{{ __('mail_templates.editor.from_section') }}</label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="from_name" placeholder="{{ __('mail_templates.editor.from_name_ph') }}" class="mt-1 w-1/3 px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700">
                            <input type="email" wire:model="from_address" placeholder="{{ __('mail_templates.editor.from_address_ph') }}" class="mt-1 flex-1 px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700">
                        </div>
                    </div>
                    <div class="sm:col-span-2 flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="is_active" class="rounded">
                            {{ __('mail_templates.editor.is_active') }}
                        </label>
                        <div class="text-xs text-gray-500">
                            {{ __('mail_templates.editor.template_key') }} <span class="font-mono">{{ $key }}</span>
                            @if ($festivalId)
                                · {{ __('mail_templates.editor.festival_label') }} <span class="font-semibold">{{ $this->festivals->firstWhere('id', $festivalId)?->displayName() ?? '#' . $festivalId }}</span>
                            @else
                                · <span class="font-semibold">{{ __('mail_templates.editor.global_label') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                        <div class="text-xs font-semibold uppercase text-gray-500 tracking-wider">
                            {{ __('mail_templates.editor.html_body') }} — {{ __('mail_templates.editor.blade') }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {!! __('Use the placeholders on the right, e.g.') !!} <code class="font-mono">@{{ $order_number }}</code>
                        </div>
                    </div>
                    <textarea
                        wire:model.live.debounce.500ms="html_body"
                        rows="22"
                        spellcheck="false"
                        class="block w-full font-mono text-xs leading-5 p-4 bg-gray-900 text-emerald-200 border-0 focus:ring-0 resize-y"
                        style="min-height: 380px;"
                        placeholder="<h1>Hvala!</h1>&#10;<p>Poštovani @{{ $customer_name }}, ...</p>"
                    ></textarea>
                </div>

                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
                    <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                        <div class="text-xs font-semibold uppercase text-gray-500 tracking-wider">
                            {{ __('mail_templates.editor.css_optional') }} — {{ __('mail_templates.editor.css_help') }}
                        </div>
                    </div>
                    <textarea
                        wire:model.live.debounce.500ms="css"
                        rows="6"
                        spellcheck="false"
                        class="block w-full font-mono text-xs leading-5 p-4 bg-gray-900 text-cyan-200 border-0 focus:ring-0 resize-y"
                        placeholder="/* body { font-family: -apple-system, sans-serif; } */"
                    ></textarea>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-pink-600 text-white rounded-lg text-sm font-medium hover:bg-pink-700">
                        💾 {{ __('mail_templates.editor.save') }}
                    </button>
                    <button type="button" wire:click="renderPreview" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600">
                        🔄 {{ __('mail_templates.editor.refresh_preview') }}
                    </button>
                    @if ($editing && $festivalId)
                        <button type="button" wire:click="duplicateAsGlobal" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:underline">
                            🌐 {{ __('mail_templates.editor.copy_to_global') }}
                        </button>
                    @endif
                    @if ($editing)
                        <button
                            type="button"
                            wire:click="delete({{ $editing }})"
                            wire:confirm="{{ __('mail_templates.confirm.delete_in_editor') }}"
                            class="px-4 py-2 text-sm text-red-600 hover:underline ml-auto"
                        >
                            🗑 {{ __('mail_templates.editor.delete') }}
                        </button>
                    @endif
                </div>
            </div>

            {{-- ============= Right: live preview + variable helper ============= --}}
            <div class="space-y-4">

                {{-- Live preview iframe --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                        <div class="text-xs font-semibold uppercase text-gray-500 tracking-wider">
                            {{ __('mail_templates.preview.live_preview') }}
                        </div>
                        @if ($previewStatus === 'rendered')
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                {{ __('mail_templates.preview.rendered') }}
                            </span>
                        @elseif ($previewStatus === 'error')
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-red-100 text-red-800">
                                {{ __('mail_templates.preview.error') }}
                            </span>
                        @endif
                    </div>
                    @if ($previewError)
                        <div class="p-3 text-xs text-red-700 bg-red-50 dark:bg-red-900/30 dark:text-red-200 font-mono">
                            {{ $previewError }}
                        </div>
                    @endif
                    <iframe
                        srcdoc="{{ $previewSource }}"
                        class="block w-full bg-white"
                        style="height: 560px; border: 0;"
                        sandbox="allow-same-origin"
                        title="{{ __('mail_templates.preview.live_preview') }}"
                    ></iframe>
                </div>

                {{-- Variable helper --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                    <h3 class="text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">
                        {{ __('mail_templates.variables.title') }}
                    </h3>
                    <p class="text-xs text-gray-500 mb-3">
                        {!! __('mail_templates.variables.intro') !!} <code class="font-mono">@{{ $name }}</code>.
                    </p>
                    <ul class="space-y-1 text-xs max-h-72 overflow-y-auto pr-2">
                        @foreach ($this->variables as $var => $desc)
                            <li class="flex flex-col py-1 border-b border-gray-100 dark:border-gray-800 last:border-0">
                                <code
                                    class="font-mono text-pink-700 dark:text-pink-300 cursor-pointer"
                                    title="{{ __('mail_templates.variables.copy_title') }}"
                                    onclick="navigator.clipboard?.writeText('{{ '$' }}{{ $var }}')"
                                >{{ '$' }}{{ $var }}</code>
                                <span class="text-gray-500">{{ $desc }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </form>
    @endif
</div>
