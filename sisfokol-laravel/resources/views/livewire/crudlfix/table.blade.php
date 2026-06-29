<div>
    {{-- Search & Filters --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center flex-1 gap-3 min-w-[280px]">
            {{-- Search --}}
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchQuery"
                    placeholder="Cari..."
                    class="w-full pl-10 pr-4 py-2 bg-slate-800/50 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                />
            </div>

            {{-- Per Page Selector --}}
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs text-slate-400">Tampilkan:</span>
                <select
                    wire:model.live="perPage"
                    class="px-3 py-2 bg-slate-800/50 border border-slate-700 rounded-xl text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-xs cursor-pointer"
                >
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="all">Semua</option>
                </select>
            </div>
        </div>

        {{-- Export --}}
        @if(!empty($exportColumns))
            <button
                wire:click="export"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition"
            >
                <i class="fas fa-download w-4 h-4 inline mr-1"></i>
                Export
            </button>
        @endif

        {{-- Bulk delete --}}
        @if(!empty($selected) && $this->checkCrudlfixAction('delete'))
            <button
                wire:click="confirmBulkDelete"
                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition"
            >
                <i class="fas fa-trash w-4 h-4 inline mr-1"></i>
                Hapus ({{ count($selected) }})
            </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-xl border border-slate-700">
        <table class="w-full text-sm text-left text-slate-300">
            <thead class="text-xs text-slate-400 uppercase bg-slate-800/50">
                <tr>
                    {{-- Select all --}}
                    <th class="px-4 py-3 w-10">
                        <input
                            type="checkbox"
                            wire:click="toggleSelectAll"
                            @if($selectAll) checked @endif
                            class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500"
                        />
                    </th>

                    {{-- Column headers --}}
                    @foreach($columns as $column => $label)
                        <th
                            class="px-4 py-3 cursor-pointer hover:text-slate-200 transition"
                            wire:click="sortBy('{{ $column }}')"
                        >
                            {{ $label }}
                            @if($sortField === $column)
                                @if($sortDirection === 'asc')
                                    <i class="fas fa-chevron-up w-3 h-3 inline ml-1"></i>
                                @else
                                    <i class="fas fa-chevron-down w-3 h-3 inline ml-1"></i>
                                @endif
                            @endif
                        </th>
                    @endforeach

                    {{-- Actions --}}
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-b border-slate-700/50 hover:bg-slate-800/30 transition">
                        {{-- Select --}}
                        <td class="px-4 py-3">
                            <input
                                type="checkbox"
                                wire:click="toggleSelect({{ $row->id }})"
                                @if(in_array($row->id, $selected)) checked @endif
                                class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500"
                            />
                        </td>

                        {{-- Data columns --}}
                        @foreach($columns as $column => $label)
                            <td class="px-4 py-3">
                                @if(str_contains($column, '.'))
                                    {{ data_get($row, $column) ?? '-' }}
                                @else
                                    {{ $row->$column ?? '-' }}
                                @endif
                            </td>
                        @endforeach

                        {{-- Actions --}}
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($routePrefix && $showDetail && $this->checkCrudlfixAction('view', $row))
                                    <a
                                        href="{{ route($routePrefix . '.show', $row->id) }}"
                                        class="px-2 py-1 text-xs bg-slate-700 hover:bg-slate-600 rounded-lg transition"
                                        title="Detail"
                                    >
                                        <i class="fas fa-eye w-3 h-3"></i>
                                    </a>
                                @endif
                                @if($showEdit && $this->checkCrudlfixAction('update', $row))
                                    @if($inlineEdit)
                                        <button
                                            wire:click="editRecord({{ $row->id }})"
                                            class="px-2 py-1 text-xs bg-indigo-600 hover:bg-indigo-500 rounded-lg transition"
                                            title="Edit"
                                        >
                                            <i class="fas fa-pen w-3 h-3"></i>
                                        </button>
                                    @else
                                        <a
                                            href="{{ route($routePrefix . '.edit', $row->id) }}"
                                            class="px-2 py-1 text-xs bg-indigo-600 hover:bg-indigo-500 rounded-lg transition"
                                            title="Edit"
                                        >
                                            <i class="fas fa-pen w-3 h-3"></i>
                                        </a>
                                    @endif
                                @endif
                                @if($this->checkCrudlfixAction('delete', $row))
                                    <button
                                        wire:click="confirmDelete({{ $row->id }})"
                                        class="px-2 py-1 text-xs bg-rose-600 hover:bg-rose-500 rounded-lg transition"
                                        title="Hapus"
                                    >
                                        <i class="fas fa-trash w-3 h-3"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="px-4 py-8 text-center text-slate-500">
                            Tidak ada data ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($rows->total() > 0)
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-slate-400">
                Menampilkan {{ $rows->firstItem() }} - {{ $rows->lastItem() }} dari {{ $rows->total() }} data
            </div>
            @if($rows->hasPages())
                <div class="flex items-center gap-1">
                    {{-- Previous --}}
                    @if($rows->onFirstPage())
                        <span class="px-3 py-1 text-sm rounded-lg bg-slate-800 text-slate-600 cursor-not-allowed">
                            <i class="fas fa-chevron-left w-3 h-3"></i>
                        </span>
                    @else
                        <button
                            wire:click="goToPage({{ $currentPage - 1 }})"
                            class="px-3 py-1 text-sm rounded-lg bg-slate-800 text-slate-400 hover:bg-slate-700 transition"
                        >
                            <i class="fas fa-chevron-left w-3 h-3"></i>
                        </button>
                    @endif

                    {{-- Page numbers --}}
                    @for($i = 1; $i <= $rows->lastPage(); $i++)
                        <button
                            wire:click="goToPage({{ $i }})"
                            class="px-3 py-1 text-sm rounded-lg transition {{ $i == $currentPage ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}"
                        >
                            {{ $i }}
                        </button>
                    @endfor

                    {{-- Next --}}
                    @if($rows->hasMorePages())
                        <button
                            wire:click="goToPage({{ $currentPage + 1 }})"
                            class="px-3 py-1 text-sm rounded-lg bg-slate-800 text-slate-400 hover:bg-slate-700 transition"
                        >
                            <i class="fas fa-chevron-right w-3 h-3"></i>
                        </button>
                    @else
                        <span class="px-3 py-1 text-sm rounded-lg bg-slate-800 text-slate-600 cursor-not-allowed">
                            <i class="fas fa-chevron-right w-3 h-3"></i>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-slate-900 rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl border border-slate-700">
                <h3 class="text-lg font-semibold text-slate-100 mb-2">Konfirmasi Hapus</h3>
                <p class="text-slate-400 mb-6">
                    @if($deleteType === 'bulk')
                        Yakin ingin menghapus {{ count($selected) }} data yang dipilih?
                    @else
                        Yakin ingin menghapus data ini?
                    @endif
                </p>
                <div class="flex items-center justify-end gap-3">
                    <button
                        wire:click="cancelDelete"
                        class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl transition"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="executeDelete"
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl transition"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
