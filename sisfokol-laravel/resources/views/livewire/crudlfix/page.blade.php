<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">
                @if($mode === 'index')
                    {{ $title }}
                @elseif($mode === 'create')
                    Tambah {{ $title }}
                @elseif($mode === 'edit')
                    Edit {{ $title }}
                @elseif($mode === 'show')
                    Detail {{ $title }}
                @endif
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                @if($mode === 'index')
                    Kelola data {{ strtolower($title) }}
                @elseif($mode === 'create')
                    Isi form berikut untuk menambah data baru
                @elseif($mode === 'edit')
                    Ubah data yang diperlukan
                @elseif($mode === 'show')
                    Informasi detail data
                @endif
            </p>
        </div>

        @if($mode === 'index')
            <button
                wire:click="setMode('create')"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition text-sm"
            >
                <i class="fas fa-plus w-4 h-4 inline mr-1"></i>
                Tambah
            </button>
        @else
            <button
                wire:click="setMode('index')"
                class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl transition text-sm"
            >
                <i class="fas fa-arrow-left w-4 h-4 inline mr-1"></i>
                Kembali
            </button>
        @endif
    </div>

    {{-- Content --}}
    @if($mode === 'index')
        @livewire('crudlfix.crudlfix-table', [
            'model' => $modelClass,
            'route' => $routePrefix,
            'columns' => $columns,
            'search' => $searchFields,
            'with' => $withRelations,
            'filters' => $filterConfig,
            'perPage' => $perPage,
            'defaultSort' => $defaultSort,
            'defaultDir' => $defaultDir,
            'exportColumns' => null,
            'authorize' => $permissionPrefix,
            'authType' => $authMode,
        ], key('table-' . $routePrefix))

    @elseif($mode === 'create')
        @livewire('crudlfix.crudlfix-form', [
            'model' => $modelClass,
            'route' => $routePrefix,
            'formFields' => $formFields,
            'rules' => $validationRules,
            'viewData' => $extraViewData,
            'isEdit' => false,
        ], key('form-create-' . $routePrefix))

    @elseif($mode === 'edit')
        @livewire('crudlfix.crudlfix-form', [
            'model' => $modelClass,
            'route' => $routePrefix,
            'formFields' => $formFields,
            'rules' => $validationRules,
            'viewData' => $extraViewData,
            'isEdit' => true,
            'editId' => $editId,
        ], key('form-edit-' . $editId))

    @elseif($mode === 'show')
        {{-- Show mode - detail view --}}
        <div class="glass-card p-6 rounded-xl">
            <p class="text-slate-400">Detail view belum diimplementasikan.</p>
        </div>
    @endif
</div>
