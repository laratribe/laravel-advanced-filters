{{-- Tailwind-themed override of the Advanced Filters panel. Published via:
     php artisan vendor:publish --tag=advanced-filters-theme
     Behaviour is identical to the headless default — same slots, same class prefix,
     same registry-driven inputs; only the CSS classes change. --}}
@props([
    'fields' => [],
    'active' => [],
])

@php($prefix = $prefix())
@php($cfg = $config())

<div
    {{ $attributes->merge(['class' => "{$prefix}-panel flex flex-col gap-3 text-sm text-gray-800"]) }}
    data-af-part="panel"
    x-data="advancedFiltersPanel(@js([
        'fields'  => $fields,
        'active'  => $active,
        'config'  => $cfg,
        'prefix'  => $prefix,
    ]))"
>
    {{-- Active filter chips --}}
    <div class="flex flex-wrap items-center gap-2" data-af-part="chips" x-show="active.length" x-cloak>
        <template x-for="(filter, i) in active" :key="chipKey(filter, i)">
            @isset($chip)
                {{ $chip }}
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 py-0.5 pl-2.5 pr-1 text-blue-800" data-af-part="chip">
                    <span>
                        <strong x-text="fieldLabel(filter.field)"></strong>
                        <span x-text="' ' + operatorLabel(filter.operator, filter.field) + ' '"></span>
                        <em class="not-italic font-semibold" x-show="chipValue(filter)" x-text="chipValue(filter)"></em>
                    </span>
                    <button type="button" class="flex h-4 w-4 items-center justify-center rounded-full hover:bg-black/10" aria-label="Remove filter" @click="remove(i)">&times;</button>
                </span>
            @endisset
        </template>
        <button type="button" class="px-2 py-1 text-gray-500 hover:text-gray-800 hover:underline" @click="clearAll()" x-show="active.length">{{ $clearLabel }}</button>
    </div>

    {{-- "Add filter" builder dropdown --}}
    <div class="relative inline-block" data-af-part="builder" @keydown.escape="closeMenu()">
        @isset($trigger)
            {{ $trigger }}
        @else
            <button type="button" class="rounded-md border border-dashed border-gray-300 bg-white px-3 py-1.5 font-medium text-blue-600 hover:border-blue-500" aria-haspopup="dialog" :aria-expanded="open" @click="toggleMenu()">+ {{ $addLabel }}</button>
        @endisset

        <div class="absolute left-0 top-full z-50 mt-1 max-h-[70vh] w-72 max-w-sm overflow-y-auto rounded-md border border-gray-200 bg-white p-2 shadow-xl" data-af-part="dropdown" role="dialog" x-show="open" x-cloak @click.outside="closeMenu()">
            <template x-if="step === 'field'">
                <div class="flex flex-col" data-af-part="field-list">
                    <p class="mx-1 mb-2 mt-1 text-[0.6875rem] font-semibold uppercase tracking-wide text-gray-500">Select column</p>
                    <template x-for="field in fields" :key="field.key">
                        <button type="button" class="rounded-md px-2 py-2 text-left hover:bg-blue-50" x-text="field.label" @click="selectField(field)"></button>
                    </template>
                </div>
            </template>

            <template x-if="step === 'configure' && selectedField()">
                <div class="flex flex-col gap-2.5" data-af-part="configure">
                    <p class="mx-1 mb-1 mt-1 text-[0.6875rem] font-semibold uppercase tracking-wide text-gray-500" x-text="selectedField().label"></p>

                    <label class="flex flex-col gap-1">
                        <span class="text-xs text-gray-500">Operator</span>
                        <select class="w-full rounded-md border border-gray-300 px-2 py-1.5" x-model="pending.operator" @change="onOperatorChange()">
                            <template x-for="op in availableOperators()" :key="op">
                                <option :value="op" x-text="operatorLabel(op)"></option>
                            </template>
                        </select>
                    </label>

                    {{-- Every registered input renders; each self-gates on the field's `input` name. --}}
                    <template x-if="needsValueInput()">
                        <div>
                            @foreach ($inputViews() as $inputView)
                                @include($inputView, ['prefix' => $prefix])
                            @endforeach
                        </div>
                    </template>

                    <div class="mt-1 flex items-center justify-between gap-2" data-af-part="actions">
                        <button type="button" class="px-2 py-1.5 text-gray-500 hover:text-gray-800" @click="goBackToFields()">Back</button>
                        @isset($applyButton)
                            {{ $applyButton }}
                        @else
                            <button type="button" class="rounded-md bg-blue-600 px-3.5 py-1.5 font-medium text-white disabled:cursor-not-allowed disabled:opacity-50" :disabled="!canAdd()" @click="addPending()">Apply</button>
                        @endisset
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
