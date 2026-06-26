<div>
    <form wire:submit.prevent="save">
        <div class="space-y-4">
            @foreach($formFields as $field => $options)
                @php
                    $label = $options['label'] ?? ucfirst(str_replace('_', ' ', $field));
                    $type = $options['type'] ?? 'text';
                    $required = false;
                    if (!empty($validationRules[$field])) {
                        $rules = is_string($validationRules[$field]) ? explode('|', $validationRules[$field]) : [];
                        $required = in_array('required', $rules);
                    }
                    $placeholder = $options['placeholder'] ?? '';
                @endphp

                <div>
                    <label for="{{ $field }}" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        {{ $label }}
                        @if($required)
                            <span class="text-rose-500">*</span>
                        @endif
                    </label>

                    @if($type === 'textarea')
                        <textarea
                            wire:model.live="data.{{ $field }}"
                            id="{{ $field }}"
                            rows="{{ $options['rows'] ?? 3 }}"
                            placeholder="{{ $placeholder }}"
                            class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm @if(!empty($errors[$field])) border-rose-500 @endif"
                        ></textarea>

                    @elseif($type === 'select')
                        <select
                            wire:model.live="data.{{ $field }}"
                            id="{{ $field }}"
                            class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm @if(!empty($errors[$field])) border-rose-500 @endif"
                        >
                            <option value="">-- Pilih {{ $label }} --</option>
                            @foreach($options['options'] ?? [] as $value => $lbl)
                                <option value="{{ $value }}">{{ $lbl }}</option>
                            @endforeach
                        </select>

                    @elseif($type === 'checkbox')
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model.live="data.{{ $field }}"
                                id="{{ $field }}"
                                value="1"
                                class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-500 focus:ring-indigo-500"
                            />
                            <span class="text-sm text-slate-300">{{ $options['checkbox_label'] ?? 'Aktif' }}</span>
                        </label>

                    @elseif($type === 'date')
                        <input
                            type="date"
                            wire:model.live="data.{{ $field }}"
                            id="{{ $field }}"
                            class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm @if(!empty($errors[$field])) border-rose-500 @endif"
                        />

                    @else
                        <input
                            type="{{ $type }}"
                            wire:model.live="data.{{ $field }}"
                            id="{{ $field }}"
                            placeholder="{{ $placeholder }}"
                            class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm @if(!empty($errors[$field])) border-rose-500 @endif"
                        />
                    @endif

                    @if(!empty($errors[$field]))
                        <p class="text-xs text-rose-500 mt-1.5">{{ $errors[$field] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Submit --}}
        <div class="mt-6 flex items-center justify-end gap-3">
            <a
                href="{{ route($routePrefix . '.index') }}"
                class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl transition text-sm"
            >
                Batal
            </a>
            <button
                type="submit"
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition text-sm"
            >
                @if($isEdit)
                    <i class="fas fa-save w-4 h-4 inline mr-1"></i>
                    Simpan Perubahan
                @else
                    <i class="fas fa-plus w-4 h-4 inline mr-1"></i>
                    Simpan
                @endif
            </button>
        </div>
    </form>
</div>
