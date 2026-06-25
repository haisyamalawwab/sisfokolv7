{{-- components/crudlfix/cascade-select.blade.php --}}
@props([
    'name',
    'url',
    'field',
    'dependsOn',
    'label'     => 'Pilih...',
    'selected'  => null,
    'required'  => false,
])

<div x-data="{
    options: [],
    selected: {{ $selected ?? 'null' }},
    loading: false,

    async loadOptions(parentValue) {
        if (!parentValue) {
            this.options = [];
            this.selected = null;
            return;
        }
        this.loading = true;
        try {
            const res = await fetch('{{ $url }}?type=cascade&field={{ $field }}&value=' + parentValue);
            this.options = await res.json();
        } catch (e) {
            this.options = [];
        }
        this.loading = false;
    }
}"
@cascade-{{ $dependsOn }}.window="loadOptions($event.detail)"
x-init="$nextTick(() => {
    const parentEl = document.querySelector('[name={{ $dependsOn }}]');
    if (parentEl && parentEl.value) loadOptions(parentEl.value);
})">

    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
        {{ $label }}
        @if($required)<span class="text-rose-500">*</span>@endif
    </label>

    <select name="{{ $name }}" id="{{ $name }}" x-model="selected"
        {{ $required ? 'required' : '' }}
        class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm">
        <option value="">-- Pilih --</option>
        <template x-for="opt in options" :key="opt.value">
            <option :value="opt.value" x-text="opt.label"></option>
        </template>
    </select>

    <div x-show="loading" class="mt-1">
        <i class="fas fa-spinner fa-spin text-slate-500 text-xs"></i>
        <span class="text-xs text-slate-500">Memuat...</span>
    </div>

    @error($name)
        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
    @enderror
</div>
