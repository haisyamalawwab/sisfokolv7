<?php

namespace App\Support\Crudlfix;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * CrudlfixView — Dynamic Blade view generator for CRUDLFIX.
 *
 * Generates table rows, form fields, and detail views dynamically
 * based on model attributes and configuration.
 */
class CrudlfixView
{
    /**
     * Generate form field HTML.
     */
    public static function formField(string $name, array $config, ?Model $model = null): string
    {
        $label = $config['label'] ?? Str::headline($name);
        $type = $config['type'] ?? 'text';
        $required = ($config['required'] ?? false) ? '<span class="text-rose-500">*</span>' : '';
        $value = old($name, $model?->$name ?? $config['default'] ?? '');
        $placeholder = $config['placeholder'] ?? '';

        $input = match ($type) {
            'textarea' => '<textarea name="'.$name.'" id="'.$name.'" rows="3"
                    class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm"
                >'.e($value).'</textarea>',

            'select' => self::selectField($name, $config, $value),

            'date' => '<input type="date" name="'.$name.'" id="'.$name.'" value="'.e($value).'"
                    class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm">',

            'checkbox' => self::checkboxField($name, $config, $value),

            default => '<input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.e($value).'" placeholder="'.e($placeholder).'"
                    class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm">',
        };

        return '<div>
            <label for="'.$name.'" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">'.$label.' '.$required.'</label>
            '.$input.'
            @error(\''.$name.'\')
                <p class="text-xs text-rose-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>';
    }

    /**
     * Generate select field.
     */
    private static function selectField(string $name, array $config, mixed $value): string
    {
        $options = '<option value="">-- Pilih --</option>';
        foreach ($config['options'] as $val => $lbl) {
            $selected = $value == $val ? 'selected' : '';
            $options .= '<option value="'.e($val).'" '.$selected.'>'.e($lbl).'</option>';
        }

        return '<select name="'.$name.'" id="'.$name.'"
            class="w-full px-4 py-2.5 bg-slate-950/50 border border-slate-800 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition text-sm">
            '.$options.'
        </select>';
    }

    /**
     * Generate checkbox field.
     */
    private static function checkboxField(string $name, array $config, mixed $value): string
    {
        $checked = $value ? 'checked' : '';
        $checkboxLabel = $config['checkbox_label'] ?? 'Aktif';

        return '<label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="'.$name.'" value="1" '.$checked.'
                        class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-500 focus:ring-indigo-500">
                    <span class="text-sm text-slate-300">'.e($checkboxLabel).'</span>
                </label>';
    }
}
