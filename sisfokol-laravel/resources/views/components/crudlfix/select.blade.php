{{-- components/crudlfix/select.blade.php --}}
@props([
    'name',
    'options'   => [],
    'label'     => 'Pilih...',
    'selected'  => null,
    'required'  => false,
    'disabled'  => false,
    'valueKey'  => 'id',
    'labelKey'  => 'nama',
])

<div>
    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
        {{ $label }}
        @if($required)<span class="text-rose-500">*</span>@endif
    </label>
    <select name="{{ $name }}" id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm']) }}>
        <option value="">-- Pilih --</option>
        @foreach($options as $option)
            @php
                $val = is_array($option) ? $option[$valueKey] : $option->{$valueKey};
                $lbl = is_array($option) ? $option[$labelKey] : $option->{$labelKey};
            @endphp
            <option value="{{ $val }}" {{ old($name, $selected) == $val ? 'selected' : '' }}>
                {{ $lbl }}
            </option>
        @endforeach
    </select>
    @error($name)
        <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
    @enderror
</div>
