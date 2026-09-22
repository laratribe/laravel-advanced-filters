@props([
    'fields' => [],
    'active' => [],
])

@php($prefix = $prefix())
@php($cfg = $config())

<div
    {{ $attributes->merge(['class' => "{$prefix}-panel"]) }}
    data-af-part="panel"
    x-data="advancedFiltersPanel(@js([
        'fields'  => $fields,
        'active'  => $active,
        'config'  => $cfg,
        'prefix'  => $prefix,
    ]))"
>
    {{-- ===== Active filter chips ===== --}}
    <div class="{{ $prefix }}-chips" data-af-part="chips" x-show="active.length" x-cloak>
        <template x-for="(filter, i) in active" :key="chipKey(filter, i)">
            @isset($chip)
                {{ $chip }}
            @else
                <span class="{{ $prefix }}-chip" data-af-part="chip">
                    <span class="{{ $prefix }}-chip__text">
                        <strong x-text="fieldLabel(filter.field)"></strong>
                        <span x-text="' ' + operatorLabel(filter.operator, filter.field) + ' '"></span>
                        <em x-show="chipValue(filter)" x-text="chipValue(filter)"></em>
                    </span>
                    <button
                        type="button"
                        class="{{ $prefix }}-chip__remove"
                        aria-label="Remove filter"
                        @click="remove(i)"
                    >&times;</button>
                </span>
            @endisset
        </template>

        <button type="button" class="{{ $prefix }}-clear" @click="clearAll()" x-show="active.length">
            {{ $clearLabel }}
        </button>
    </div>

    {{-- ===== "Add filter" builder dropdown ===== --}}
    <div class="{{ $prefix }}-builder" data-af-part="builder" @keydown.escape="closeMenu()">
        @isset($trigger)
            {{ $trigger }}
        @else
            <button
                type="button"
                class="{{ $prefix }}-builder__trigger"
                aria-haspopup="dialog"
                :aria-expanded="open"
                @click="toggleMenu()"
            >+ {{ $addLabel }}</button>
        @endisset

        <div
            class="{{ $prefix }}-builder__panel"
            data-af-part="dropdown"
            role="dialog"
            x-show="open"
            x-cloak
            @click.outside="closeMenu()"
        >
            {{-- Step 1: column list --}}
            <template x-if="step === 'field'">
                <div class="{{ $prefix }}-field-list" data-af-part="field-list">
                    <p class="{{ $prefix }}-builder__title">Select column</p>
                    <template x-for="field in fields" :key="field.key">
                        <button
                            type="button"
                            class="{{ $prefix }}-field-item"
                            x-text="field.label"
                            @click="selectField(field)"
                        ></button>
                    </template>
                </div>
            </template>

            {{-- Step 2: operator + value --}}
            <template x-if="step === 'configure' && selectedField()">
                <div class="{{ $prefix }}-configure" data-af-part="configure">
                    <p class="{{ $prefix }}-builder__title" x-text="selectedField().label"></p>

                    <label class="{{ $prefix }}-field">
                        <span class="{{ $prefix }}-field__label">Operator</span>
                        <select
                            class="{{ $prefix }}-select"
                            x-model="pending.operator"
                            @change="onOperatorChange()"
                        >
                            <template x-for="op in availableOperators()" :key="op">
                                <option :value="op" x-text="operatorLabel(op)"></option>
                            </template>
                        </select>
                    </label>

                    {{-- Value input(s). Every registered input renders; each self-gates
                         on the field's `input` name, and Alpine shows the matching one. --}}
                    <template x-if="needsValueInput()">
                        <div class="{{ $prefix }}-value">
                            @foreach ($inputViews() as $inputView)
                                @include($inputView, ['prefix' => $prefix])
                            @endforeach
                        </div>
                    </template>

                    <div class="{{ $prefix }}-actions" data-af-part="actions">
                        <button type="button" class="{{ $prefix }}-back" @click="goBackToFields()">Back</button>
                        @isset($applyButton)
                            {{ $applyButton }}
                        @else
                            <button
                                type="button"
                                class="{{ $prefix }}-btn {{ $prefix }}-btn--primary"
                                :disabled="!canAdd()"
                                @click="addPending()"
                            >Apply</button>
                        @endisset
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
