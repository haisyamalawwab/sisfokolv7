{{-- components/crudlfix/search-select.blade.php --}}
@props([
    'name',
    'url',
    'field'         => null,
    'label'         => 'Cari...',
    'selected'      => null,
    'selectedLabel' => '',
    'required'      => false,
])

<div x-data="{
    open: false,
    search: '{{ $selectedLabel }}',
    selected: {{ $selected ?? 'null' }},
    options: [],
    loading: false,
    highlighted: -1,

    async fetchOptions() {
        this.loading = true;
        try {
            const res = await fetch('{{ $url }}?type=search&field={{ $field ?? $name }}&q=' + encodeURIComponent(this.search));
            this.options = await res.json();
        } catch (e) {
            this.options = [];
        }
        this.loading = false;
        this.highlighted = -1;
    },

    select(opt) {
        this.selected = opt.value;
        this.search = opt.label;
        this.open = false;
    },

    clear() {
        this.selected = null;
        this.search = '';
        this.options = [];
    },

    handleKeydown(e) {
        if (!this.open) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.highlighted = Math.min(this.highlighted + 1, this.options.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.highlighted = Math.max(this.highlighted - 1, 0);
        } else if (e.key === 'Enter' && this.highlighted >= 0) {
            e.preventDefault();
            this.select(this.options[this.highlighted]);
        } else if (e.key === 'Escape') {
            this.open = false;
        }
    }
}"
@click.away="open = false"
@keydown="handleKeydown($event)"
class="relative">

    <input type="hidden" name="{{ $name }}" :value="selected">

    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
        {{ $label }}
        @if($required)<span class="text-rose-500">*</span>@endif
    </label>

    <div class="relative">
        <input type="text" x-model="search"
            @focus="open = true; fetchOptions()"
            @input.debounce.300ms="fetchOptions()"
            placeholder="{{ $label }}"
            {{ $required ? 'required' : '' }}
            class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm pr-10">

        {{-- Clear button --}}
        <button type="button" x-show="selected !== null" @click="clear()"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>

    {{-- Dropdown --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        class="absolute z-50 mt-1 w-full bg-slate-900 border border-slate-700 rounded-xl shadow-xl max-h-60 overflow-auto">

        <div x-show="loading" class="px-4 py-3 text-center">
            <i class="fas fa-spinner fa-spin text-slate-500"></i>
        </div>

        <template x-for="(opt, idx) in options" :key="opt.value">
            <div @click="select(opt)"
                :class="idx === highlighted ? 'bg-indigo-600/30 text-white' : 'text-slate-300 hover:bg-slate-800'"
                class="px-4 py-2.5 cursor-pointer text-sm transition"
                x-text="opt.label">
            </div>
        </template>

        <div x-show="!loading && options.length === 0 && search.length > 0"
            class="px-4 py-3 text-slate-500 text-sm text-center">
            Tidak ada data ditemukan
        </div>
    </div>

    @error($name)
        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
    @enderror
</div>
