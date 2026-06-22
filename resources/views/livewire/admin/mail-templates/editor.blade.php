{{-- This view is rendered inside the default app layout (the route
     declares `MailTemplateEditor::class` as a full-page Livewire
     component). Do NOT wrap with <x-layouts.app> here. --}}
<div>
    <x-ds.page-header
        :title="__('mail_templates.page_title')"
        :subtitle="__('mail_templates.page_intro')"
    >
        @if ($editing)
            <x-slot:actions>
                <x-ds.button variant="ghost" wire:click="cancelEdit">← {{ __('mail_templates.editor.back_to_list') }}</x-ds.button>
            </x-slot:actions>
        @endif
    </x-ds.page-header>

    @if (session('success'))
        <x-ds.alert variant="success" class="mb-4">{{ session('success') }}</x-ds.alert>
    @endif
    @error('html_body') <x-ds.alert variant="danger" class="mb-4">{{ $message }}</x-ds.alert> @enderror
    @error('key')      <x-ds.alert variant="danger" class="mb-4">{{ $message }}</x-ds.alert> @enderror
    @error('name')     <x-ds.alert variant="danger" class="mb-4">{{ $message }}</x-ds.alert> @enderror
    @error('subject')  <x-ds.alert variant="danger" class="mb-4">{{ $message }}</x-ds.alert> @enderror

    @if (!$editing && !$key)
        {{-- ============================================================
             LIST VIEW
             ============================================================ --}}
        <x-ds.card :padded="false" class="mb-4">
            <x-slot:body>
                <x-ds.table>
                    <x-slot:head>
                        <tr>
                            <th>{{ __('mail_templates.list.header_template') }}</th>
                            <th>{{ __('mail_templates.list.header_festival') }}</th>
                            <th>{{ __('mail_templates.list.header_subject') }}</th>
                            <th>{{ __('mail_templates.list.header_version') }}</th>
                            <th>{{ __('mail_templates.list.header_updated') }}</th>
                            <th class="text-right">{{ __('mail_templates.list.header_actions') }}</th>
                        </tr>
                    </x-slot:head>
                    @forelse ($this->templates as $tpl)
                        <tr wire:key="row-{{ $tpl->id }}">
                            <td>
                                <div class="row-title">{{ $tpl->name }}</div>
                                <div class="row-meta font-mono">{{ $tpl->key }}</div>
                                @if (!$tpl->is_active)
                                    <x-ds.badge variant="warning" size="sm" class="mt-1">{{ __('mail_templates.list.disabled_badge') }}</x-ds.badge>
                                @endif
                            </td>
                            <td>
                                @if ($tpl->isGlobal())
                                    <x-ds.badge variant="accent" size="sm" dot>{{ __('mail_templates.list.global_badge') }}</x-ds.badge>
                                @else
                                    <div class="row-title">{{ $tpl->festival?->displayName() ?? '—' }}</div>
                                    <div class="row-meta">{{ $tpl->festival?->location }}</div>
                                @endif
                            </td>
                            <td class="text-xs font-mono text-[color:var(--ds-text-muted)] truncate" style="max-width: 280px;">
                                {{ Str::limit($tpl->subject, 60) }}
                            </td>
                            <td class="font-mono text-xs">v{{ $tpl->version }}</td>
                            <td class="text-xs text-[color:var(--ds-text-muted)]">
                                {{ $tpl->updated_at?->diffForHumans() }}
                                @if ($tpl->editor)
                                    <div class="text-[10px] text-[color:var(--ds-text-subtle)]">by {{ $tpl->editor->name }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <x-ds.button variant="ghost" size="sm" wire:click="edit({{ $tpl->id }})">{{ __('Edit') }}</x-ds.button>
                                    <x-ds.button variant="danger-ghost" size="sm" wire:click="delete({{ $tpl->id }})" wire:confirm="{{ __('mail_templates.confirm.delete') }}">{{ __('Delete') }}</x-ds.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ds.empty-state
                                    :title="__('mail_templates.list.no_templates')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </x-ds.table>
            </x-slot:body>
        </x-ds.card>

        <x-ds.card :title="__('mail_templates.create_new')" class="border-dashed">
            <div class="flex flex-wrap gap-2">
                @foreach ($this->templateKeys as $key => $label)
                    <details class="relative">
                        <summary class="cursor-pointer list-none ds-btn ds-btn-primary">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            {{ $label }}
                        </summary>
                        <div class="absolute z-10 mt-1 right-0 w-72 rounded-lg border border-[color:var(--ds-border)] bg-[color:var(--ds-surface)] shadow-lg p-1.5 text-sm">
                            <button wire:click="newGlobal('{{ $key }}')" class="w-full text-left px-3 py-2 rounded hover:bg-[color:var(--ds-bg-subtle)]">
                                <span class="font-semibold">🌐 {{ __('mail_templates.as_global_default') }}</span>
                                <div class="text-xs text-[color:var(--ds-text-muted)]">{{ __('mail_templates.as_global_help') }}</div>
                            </button>
                            <div class="border-t border-[color:var(--ds-divider)] my-1"></div>
                            <div class="text-[10px] uppercase text-[color:var(--ds-text-muted)] px-3 py-1">{{ __('mail_templates.override_for') }}</div>
                            @foreach ($this->festivals as $f)
                                <button wire:click="newForFestival('{{ $key }}', {{ $f->id }})" class="w-full text-left px-3 py-1.5 rounded hover:bg-[color:var(--ds-bg-subtle)] text-xs">
                                    🎪 {{ $f->displayName() }}
                                </button>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        </x-ds.card>

    @else
        {{-- ============================================================
             EDITOR VIEW
             ============================================================ --}}
        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-[1fr_460px] gap-4">
            <div class="space-y-4">
                <x-ds.card>
                    <x-slot:body>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <x-ds.field :label="__('mail_templates.editor.internal_name')" name="name" :required="true">
                                <input type="text" wire:model="name" class="ds-input" required>
                            </x-ds.field>
                            <x-ds.field :label="__('mail_templates.editor.subject')" name="subject">
                                <input type="text" wire:model.live="subject" class="ds-input font-mono text-sm" placeholder="Vaše ulaznice za {{ '$' }}{{ '$' }}festival_name">
                            </x-ds.field>
                            <div class="sm:col-span-2">
                                <x-ds.field :label="__('mail_templates.editor.from_section')">
                                    <div class="flex gap-2">
                                        <input type="text" wire:model="from_name" placeholder="{{ __('mail_templates.editor.from_name_ph') }}" class="ds-input" style="flex: 0 0 30%;">
                                        <input type="email" wire:model="from_address" placeholder="{{ __('mail_templates.editor.from_address_ph') }}" class="ds-input" style="flex: 1;">
                                    </div>
                                </x-ds.field>
                            </div>
                            <div class="sm:col-span-2 flex items-center justify-between">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="is_active" class="ds-checkbox">
                                    {{ __('mail_templates.editor.is_active') }}
                                </label>
                                <div class="text-xs text-[color:var(--ds-text-muted)]">
                                    {{ __('mail_templates.editor.template_key') }} <span class="font-mono">{{ $key }}</span>
                                    @if ($festivalId)
                                        · {{ __('mail_templates.editor.festival_label') }} <span class="font-semibold">{{ $this->festivals->firstWhere('id', $festivalId)?->displayName() ?? '#' . $festivalId }}</span>
                                    @else
                                        · <span class="font-semibold">{{ __('mail_templates.editor.global_label') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-slot:body>
                </x-ds.card>

                <x-ds.card :padded="false">
                    <div class="flex items-center justify-between px-4 py-2.5 border-b border-[color:var(--ds-divider)]">
                        <div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--ds-text-muted)]">
                            {{ __('mail_templates.editor.html_body') }} — {{ __('mail_templates.editor.blade') }}
                        </div>
                        <div class="text-xs text-[color:var(--ds-text-muted)]">
                            {!! __('Use the placeholders on the right, e.g.') !!} <code class="font-mono">@{{ $order_number }}</code>
                        </div>
                    </div>
                    <textarea wire:model.live.debounce.500ms="html_body" rows="22" spellcheck="false"
                              class="block w-full font-mono text-xs leading-5 p-4 bg-zinc-900 text-emerald-200 border-0 focus:ring-0 resize-y"
                              style="min-height: 380px;"
                              placeholder="<h1>Hvala!</h1>&#10;<p>Poštovani @{{ $customer_name }}, ...</p>"></textarea>
                </x-ds.card>

                <x-ds.card :padded="false">
                    <div class="px-4 py-2.5 border-b border-[color:var(--ds-divider)]">
                        <div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--ds-text-muted)]">
                            {{ __('mail_templates.editor.css_optional') }} — {{ __('mail_templates.editor.css_help') }}
                        </div>
                    </div>
                    <textarea wire:model.live.debounce.500ms="css" rows="6" spellcheck="false"
                              class="block w-full font-mono text-xs leading-5 p-4 bg-zinc-900 text-cyan-200 border-0 focus:ring-0 resize-y"
                              placeholder="/* body { font-family: -apple-system, sans-serif; } */"></textarea>
                </x-ds.card>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ds.button variant="primary" type="submit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        {{ __('mail_templates.editor.save') }}
                    </x-ds.button>
                    <x-ds.button variant="secondary" type="button" wire:click="renderPreview">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                        {{ __('mail_templates.editor.refresh_preview') }}
                    </x-ds.button>
                    @if ($editing && $festivalId)
                        <x-ds.button variant="ghost" size="sm" type="button" wire:click="duplicateAsGlobal">
                            🌐 {{ __('mail_templates.editor.copy_to_global') }}
                        </x-ds.button>
                    @endif
                    @if ($editing)
                        <x-ds.button variant="danger-ghost" size="sm" type="button" wire:click="delete({{ $editing }})" wire:confirm="{{ __('mail_templates.confirm.delete_in_editor') }}" class="ml-auto">
                            🗑 {{ __('mail_templates.editor.delete') }}
                        </x-ds.button>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <x-ds.card :padded="false">
                    <div class="flex items-center justify-between px-4 py-2.5 border-b border-[color:var(--ds-divider)]">
                        <div class="text-xs font-semibold uppercase tracking-wider text-[color:var(--ds-text-muted)]">
                            {{ __('mail_templates.preview.live_preview') }}
                        </div>
                        @if ($previewStatus === 'rendered')
                            <x-ds.badge variant="success" size="sm" dot>{{ __('mail_templates.preview.rendered') }}</x-ds.badge>
                        @elseif ($previewStatus === 'error')
                            <x-ds.badge variant="danger" size="sm" dot>{{ __('mail_templates.preview.error') }}</x-ds.badge>
                        @endif
                    </div>
                    @if ($previewError)
                        <div class="p-3 text-xs text-rose-700 bg-rose-50 dark:bg-rose-900/30 dark:text-rose-200 font-mono">
                            {{ $previewError }}
                        </div>
                    @endif
                    <iframe srcdoc="{{ $previewSource }}" class="block w-full bg-white" style="height: 560px; border: 0;" sandbox="allow-same-origin" title="{{ __('mail_templates.preview.live_preview') }}"></iframe>
                </x-ds.card>

                <x-ds.card :title="__('mail_templates.variables.title')">
                    <p class="text-xs text-[color:var(--ds-text-muted)] mb-3">
                        {!! __('mail_templates.variables.intro') !!} <code class="font-mono">@{{ $name }}</code>.
                    </p>
                    <ul class="space-y-1 text-xs max-h-72 overflow-y-auto pr-1">
                        @foreach ($this->variables as $var => $desc)
                            <li class="flex flex-col py-1 border-b border-[color:var(--ds-divider)] last:border-b-0">
                                <code class="font-mono text-indigo-600 dark:text-indigo-300 cursor-pointer"
                                      title="{{ __('mail_templates.variables.copy_title') }}"
                                      onclick="navigator.clipboard?.writeText('{{ '$' }}{{ $var }}')">
                                    {{ '$' }}{{ $var }}
                                </code>
                                <span class="text-[color:var(--ds-text-muted)]">{{ $desc }}</span>
                            </li>
                        @endforeach
                    </ul>
                </x-ds.card>
            </div>
        </form>
    @endif
</div>
