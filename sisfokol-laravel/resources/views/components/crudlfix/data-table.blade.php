{{-- components/crudlfix/data-table.blade.php --}}
@props([
    'data'              => [],
    'columns'           => [],
    'searchable'        => true,
    'sortable'          => true,
    'paginated'         => true,
    'perPage'           => 15,
    'perPageOptions'    => [10, 25, 50, 100],
    'views'             => ['table', 'list', 'card'],
    'defaultView'       => 'table',
    'selectable'        => false,
    'bulkActions'       => [],
    'exportable'        => false,
    'liveEdit'          => false,
    'liveEditUrl'       => null,
    'nestedRows'        => false,
    'rowActions'        => [],
    'variant'           => 'standard',
    'striped'           => true,
    'hoverable'         => true,
    'compact'           => false,
    'emptyMessage'      => 'Tidak ada data',
])

@php
    $isSimple   = $variant === 'simple';
    $isStandard = $variant === 'standard';
    $isAdvanced = $variant === 'advanced';
    $showViewToggle = $isStandard || $isAdvanced;
    $showPerPage    = $isStandard || $isAdvanced;
    $showBulk       = $isAdvanced && $selectable;
    $showNested     = $isAdvanced && $nestedRows;
    $showLiveEdit   = $isAdvanced && $liveEdit;
    $colCount = count($columns) + ($showBulk ? 1 : 0) + ($showNested ? 1 : 0) + (count($rowActions) > 0 ? 1 : 0);
@endphp

<div x-data="{
    search: '',
    page: 1,
    perPage: {{ $perPage }},
    sort: null,
    sortDir: 'asc',
    view: '{{ $defaultView }}',
    selected: [],
    selectAll: false,
    expandedRows: [],
    editingCell: null,
    editValue: '',

    getNestedValue(obj, path) {
        return path.split('.').reduce((o, k) => o?.[k], obj) ?? '';
    },

    get rows() {
        let rows = {{ Js::from($data) }};
        if (this.search) {
            const q = this.search.toLowerCase();
            rows = rows.filter(row =>
                Object.values(row).some(v => String(v).toLowerCase().includes(q))
            );
        }
        if (this.sort) {
            rows.sort((a, b) => {
                const va = this.getNestedValue(a, this.sort);
                const vb = this.getNestedValue(b, this.sort);
                const cmp = String(va).localeCompare(String(vb));
                return this.sortDir === 'asc' ? cmp : -cmp;
            });
        }
        return rows;
    },

    get paginated() {
        if (!{{ $paginated ? 'true' : 'false' }}) return this.rows;
        const start = (this.page - 1) * this.perPage;
        return this.rows.slice(start, start + this.perPage);
    },

    get totalPages() {
        return Math.ceil(this.rows.length / this.perPage);
    },

    toggleSort(key) {
        if (!{{ $sortable ? 'true' : 'false' }}) return;
        if (this.sort === key) {
            this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.sort = key;
            this.sortDir = 'asc';
        }
    },

    toggleSelectAll() {
        this.selectAll = !this.selectAll;
        this.selected = this.selectAll ? this.paginated.map(r => r.id) : [];
    },

    toggleExpand(id) {
        const idx = this.expandedRows.indexOf(id);
        if (idx >= 0) this.expandedRows.splice(idx, 1);
        else this.expandedRows.push(id);
    },

    isExpanded(id) {
        return this.expandedRows.includes(id);
    },

    startEdit(row, key) {
        @if($showLiveEdit)
        this.editingCell = row.id + '-' + key;
        this.editValue = this.getNestedValue(row, key);
        this.$nextTick(() => {
            const input = this.$el.querySelector('[x-ref=editInput]');
            if (input) input.focus();
        });
        @endif
    },

    async saveEdit(row, key) {
        @if($showLiveEdit)
        const url = '{{ $liveEditUrl }}/' + row.id;
        try {
            const res = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ [key]: this.editValue }),
            });
            if (res.ok) row[key] = this.editValue;
        } catch (e) {
            console.error('Live edit failed:', e);
        }
        this.editingCell = null;
        @endif
    },

    cancelEdit() {
        this.editingCell = null;
    },

    exportCSV() {
        const cols = Object.keys({{ Js::from($columns) }});
        const headers = Object.values({{ Js::from($columns) }});
        let csv = headers.join(',') + '\\n';
        this.rows.forEach(row => {
            csv += cols.map(c => '\"' + this.getNestedValue(row, c) + '\"').join(',') + '\\n';
        });
        const blob = new Blob([csv], { type: 'text/csv' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'export.csv';
        a.click();
    }
}">

    {{-- TOOLBAR --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
        {{-- Search --}}
        @if($searchable)
        <div class="relative w-full sm:w-64">
            <input type="text" x-model="search" placeholder="Cari..."
                class="w-full pl-10 pr-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
        </div>
        @endif

        <div class="flex items-center gap-2 flex-wrap">
            {{-- View Toggle --}}
            @if($showViewToggle)
            <div class="flex gap-1 bg-slate-800/50 rounded-xl p-1">
                @foreach($views as $v)
                <button @click="view = '{{ $v }}'"
                    :class="view === '{{ $v }}' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition"
                    title="{{ ucfirst($v) }} view">
                    @if($v === 'table')<i class="fas fa-table"></i>
                    @elseif($v === 'list')<i class="fas fa-list"></i>
                    @else<i class="fas fa-th-large"></i>@endif
                </button>
                @endforeach
            </div>
            @endif

            {{-- Per Page --}}
            @if($showPerPage)
            <select x-model.number="perPage"
                class="bg-slate-800 border border-slate-700 rounded-lg text-xs text-slate-300 px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach($perPageOptions as $opt)
                <option value="{{ $opt }}">{{ $opt }}/hal</option>
                @endforeach
            </select>
            @endif

            {{-- Export --}}
            @if($exportable)
            <button @click="exportCSV()"
                class="px-3 py-1.5 bg-emerald-600/20 text-emerald-400 rounded-lg text-xs hover:bg-emerald-600/30 transition">
                <i class="fas fa-download mr-1"></i> Export
            </button>
            @endif

            {{-- Bulk Actions --}}
            @if($showBulk && count($bulkActions) > 0)
            <div x-show="selected.length > 0" x-transition class="flex gap-2">
                @foreach($bulkActions as $action)
                <button @click="$dispatch('bulk-{{ $action }}', selected)"
                    class="px-3 py-1.5 bg-rose-600/20 text-rose-400 rounded-lg text-xs hover:bg-rose-600/30 transition">
                    {{ ucfirst($action) }} (<span x-text="selected.length"></span>)
                </button>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- TABLE VIEW --}}
    <div x-show="view === 'table'" class="hidden lg:block overflow-x-auto rounded-xl border border-slate-800">
        <table class="w-full text-sm text-left text-slate-300">
            <thead class="text-xs text-slate-400 uppercase bg-slate-800/50 sticky top-0 z-10">
                <tr>
                    @if($showBulk)
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" @change="toggleSelectAll()"
                            class="rounded bg-slate-800 border-slate-700 text-indigo-500 focus:ring-indigo-500">
                    </th>
                    @endif
                    @if($showNested)
                    <th class="px-4 py-3 w-10"></th>
                    @endif
                    @foreach($columns as $key => $label)
                    <th @click="toggleSort('{{ $key }}')"
                        class="px-4 py-3 {{ $sortable ? 'cursor-pointer hover:text-white' : '' }} {{ $compact ? 'py-2' : '' }}">
                        <div class="flex items-center gap-1">
                            {{ $label }}
                            @if($sortable)
                            <span x-show="sort === '{{ $key }}'" class="text-indigo-400">
                                <i :class="sortDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fas text-xs"></i>
                            </span>
                            @endif
                        </div>
                    </th>
                    @endforeach
                    @if(count($rowActions) > 0)
                    <th class="px-4 py-3">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <template x-for="row in paginated" :key="row.id">
                    <tr class="border-b border-slate-800/50 {{ $hoverable ? 'hover:bg-slate-800/30' : '' }} {{ $striped ? 'even:bg-slate-900/30' : '' }} transition">
                        @if($showBulk)
                        <td class="px-4 py-3">
                            <input type="checkbox" :value="row.id" x-model="selected"
                                class="rounded bg-slate-800 border-slate-700 text-indigo-500 focus:ring-indigo-500">
                        </td>
                        @endif
                        @if($showNested)
                        <td class="px-4 py-3">
                            <button @click="toggleExpand(row.id)" class="text-slate-400 hover:text-white transition">
                                <i :class="expandedRows.includes(row.id) ? 'fa-chevron-down' : 'fa-chevron-right'" class="fas text-xs"></i>
                            </button>
                        </td>
                        @endif
                        @foreach($columns as $key => $label)
                        <td class="px-4 py-3 {{ $compact ? 'py-2' : '' }}">
                            @if($showLiveEdit)
                            <div @click="startEdit(row, '{{ $key }}')" class="cursor-pointer hover:bg-slate-700/50 rounded px-1 -mx-1 min-h-[24px]">
                                <template x-if="editingCell === row.id + '-{{ $key }}'">
                                    <input type="text" x-model="editValue" x-ref="editInput"
                                        @keydown.enter.prevent="saveEdit(row, '{{ $key }}')"
                                        @keydown.escape.prevent="cancelEdit()"
                                        @blur="saveEdit(row, '{{ $key }}')"
                                        class="w-full px-2 py-1 bg-slate-800 border border-indigo-500 rounded text-sm text-slate-200 focus:outline-none">
                                </template>
                                <template x-if="editingCell !== row.id + '-{{ $key }}'">
                                    <span x-text="getNestedValue(row, '{{ $key }}') || '-'"></span>
                                </template>
                            </div>
                            @else
                            <span x-text="getNestedValue(row, '{{ $key }}') || '-'"></span>
                            @endif
                        </td>
                        @endforeach
                        @if(count($rowActions) > 0)
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                {{ $slot }}
                            </div>
                        </td>
                        @endif
                    </tr>
                </template>

                {{-- Empty state --}}
                <template x-if="paginated.length === 0">
                    <tr>
                        <td colspan="{{ $colCount }}" class="px-4 py-12 text-center text-slate-500">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            <span>{{ $emptyMessage }}</span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- TABLE VIEW (Tablet — scrollable) --}}
    <div x-show="view === 'table'" class="hidden md:block lg:hidden overflow-x-auto rounded-xl border border-slate-800">
        <table class="w-full text-sm text-left text-slate-300" style="min-width: 600px;">
            <thead class="text-xs text-slate-400 uppercase bg-slate-800/50 sticky top-0 z-10">
                <tr>
                    @foreach($columns as $key => $label)
                    <th @click="toggleSort('{{ $key }}')"
                        class="px-4 py-3 {{ $sortable ? 'cursor-pointer hover:text-white' : '' }}">
                        <div class="flex items-center gap-1">
                            {{ $label }}
                            @if($sortable)
                            <span x-show="sort === '{{ $key }}'" class="text-indigo-400">
                                <i :class="sortDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down'" class="fas text-xs"></i>
                            </span>
                            @endif
                        </div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <template x-for="row in paginated" :key="'tablet-' + row.id">
                    <tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition">
                        @foreach($columns as $key => $label)
                        <td class="px-4 py-3">
                            <span x-text="getNestedValue(row, '{{ $key }}') || '-'"></span>
                        </td>
                        @endforeach
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- LIST VIEW (Mobile landscape) --}}
    <div x-show="view === 'list'" class="space-y-2">
        <template x-for="row in paginated" :key="'list-' + row.id">
            <div class="p-4 bg-slate-800/30 rounded-xl border border-slate-800 hover:border-slate-700 transition">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex-1 space-y-1">
                        @foreach($columns as $key => $label)
                        <div class="flex gap-2 text-sm">
                            <span class="text-slate-500 w-28 shrink-0">{{ $label }}:</span>
                            <span class="text-slate-300" x-text="getNestedValue(row, '{{ $key }}') || '-'"></span>
                        </div>
                        @endforeach
                    </div>
                    @if(count($rowActions) > 0)
                    <div class="flex items-center gap-2 shrink-0">
                        {{ $slot }}
                    </div>
                    @endif
                </div>
            </div>
        </template>
    </div>

    {{-- CARD VIEW (Mobile portrait) --}}
    <div x-show="view === 'card'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="row in paginated" :key="'card-' + row.id">
            <div class="p-4 bg-slate-800/30 rounded-xl border border-slate-800 backdrop-blur-md hover:border-slate-700 transition">
                @foreach($columns as $key => $label)
                <div class="mb-3">
                    <span class="text-xs text-slate-500 uppercase tracking-wider">{{ $label }}</span>
                    <p class="text-sm text-slate-200 mt-0.5" x-text="getNestedValue(row, '{{ $key }}') || '-'"></p>
                </div>
                @endforeach
                @if(count($rowActions) > 0)
                <div class="mt-3 pt-3 border-t border-slate-700/50 flex items-center gap-2">
                    {{ $slot }}
                </div>
                @endif
            </div>
        </template>
    </div>

    {{-- NESTED ROW CONTENT --}}
    @if($showNested)
    <template x-for="row in paginated" :key="'nested-' + row.id">
        <div x-show="expandedRows.includes(row.id)" x-transition
            class="p-4 bg-slate-900/50 border border-slate-800 rounded-xl mt-2">
            {{ $nested ?? '' }}
        </div>
    </template>
    @endif

    {{-- PAGINATION --}}
    @if($paginated)
    <div class="flex flex-col sm:flex-row justify-between items-center mt-4 gap-3">
        <span class="text-sm text-slate-400"
            x-text="'Menampilkan ' + Math.min((page-1)*perPage+1, rows.length) + '-' + Math.min(page*perPage, rows.length) + ' dari ' + rows.length + ' data'">
        </span>
        <div class="flex gap-1">
            <button @click="page = Math.max(1, page-1)" :disabled="page === 1"
                class="px-3 py-1.5 bg-slate-800 rounded-lg text-sm text-slate-300 disabled:opacity-30 hover:bg-slate-700 transition">
                <i class="fas fa-chevron-left text-xs"></i>
            </button>
            <template x-for="p in totalPages" :key="p">
                <button @click="page = p"
                    :class="page === p ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                    class="px-3 py-1.5 rounded-lg text-sm transition hidden sm:inline-block"
                    x-text="p"
                    x-show="p === 1 || p === totalPages || Math.abs(p - page) <= 2">
                </button>
            </template>
            {{-- Mobile: simple prev/next with page count --}}
            <span class="sm:hidden px-3 py-1.5 text-sm text-slate-400" x-text="page + ' / ' + totalPages"></span>
            <button @click="page = Math.min(totalPages, page+1)" :disabled="page === totalPages"
                class="px-3 py-1.5 bg-slate-800 rounded-lg text-sm text-slate-300 disabled:opacity-30 hover:bg-slate-700 transition">
                <i class="fas fa-chevron-right text-xs"></i>
            </button>
        </div>
    </div>
    @endif

</div>
