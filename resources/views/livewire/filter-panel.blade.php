@php
    $prefix = config('advanced-filters.class_prefix', 'af');
    $fields = $this->fields();
    $fieldsByKey = collect($fields)->keyBy('key');

    // Labels and value shapes come from the field definition (Clause::label()/valueShape(),
    // or a custom clause's), so custom operators render correctly with one source of truth.
    $clauseItem = function ($field, $operator) use ($fieldsByKey) {
        foreach ($fieldsByKey->get($field)['clauseItems'] ?? [] as $item) {
            if ($item['value'] === $operator) {
                return $item;
            }
        }

        return null;
    };

    $operatorLabel = fn ($field, $operator) => $clauseItem($field, $operator)['label'] ?? $operator;
    $operatorShape = fn ($field, $operator) => $clauseItem($field, $operator)['shape'] ?? 'single';

    $resolveLabel = function ($field, $raw) use ($fieldsByKey) {
        $def = $fieldsByKey->get($field);
        if (($def['type'] ?? null) === 'select' && ! empty($def['optionItems'])) {
            foreach ($def['optionItems'] as $opt) {
                if ((string) $opt['value'] === (string) $raw) {
                    return $opt['label'];
                }
            }
        }
        return $raw;
    };

    $chipValue = function ($filter) use ($resolveLabel, $operatorShape) {
        $shape = $operatorShape($filter['field'], $filter['operator']);
        if ($shape === 'none') return '';
        if ($shape === 'range') return ($filter['value'] ?? '').' – '.($filter['valueTo'] ?? '');
        $value = $filter['value'] ?? null;
        if (is_array($value)) {
            return implode(', ', array_map(fn ($v) => $resolveLabel($filter['field'], $v), $value));
        }
        return $resolveLabel($filter['field'], $value);
    };
@endphp

<div class="{{ $prefix }}-panel" data-af-part="panel">
    {{-- ===== Active filter chips (server-rendered) ===== --}}
    @if (count($active))
        <div class="{{ $prefix }}-chips" data-af-part="chips">
            @foreach ($active as $i => $filter)
                <span class="{{ $prefix }}-chip" data-af-part="chip" wire:key="chip-{{ $i }}">
                    <span class="{{ $prefix }}-chip__text">
                        <strong>{{ $fieldsByKey[$filter['field']]['label'] ?? $filter['field'] }}</strong>
                        {{ $operatorLabel($filter['field'], $filter['operator']) }}
                        @if ($chipValue($filter) !== '')
                            <em>{{ $chipValue($filter) }}</em>
                        @endif
                    </span>
                    <button
                        type="button"
                        class="{{ $prefix }}-chip__remove"
                        aria-label="Remove filter"
                        wire:click="removeFilter({{ $i }})"
                    >&times;</button>
                </span>
            @endforeach
            <button type="button" class="{{ $prefix }}-clear" wire:click="clear">Clear all</button>
        </div>
    @endif

    {{-- ===== "Add filter" builder (Alpine-enhanced; applies via $wire.addFilter) ===== --}}
    <div
        class="{{ $prefix }}-builder"
        data-af-part="builder"
        x-data="advancedFiltersBuilder({
            fields: @js($fields),
            prefix: @js($prefix),
            onAdd: row => $wire.addFilter(row.field, row.operator, row.value, row.valueTo),
        })"
        @keydown.escape="closeMenu()"
    >
        <button
            type="button"
            class="{{ $prefix }}-builder__trigger"
            aria-haspopup="dialog"
            :aria-expanded="open"
            @click="toggleMenu()"
        >+ Add filter</button>

        <div
            class="{{ $prefix }}-builder__panel"
            data-af-part="dropdown"
            role="dialog"
            x-show="open"
            x-cloak
            @click.outside="closeMenu()"
        >
            <template x-if="step === 'field'">
                <div class="{{ $prefix }}-field-list" data-af-part="field-list">
                    <p class="{{ $prefix }}-builder__title">Select column</p>
                    <template x-for="field in fields" :key="field.key">
                        <button type="button" class="{{ $prefix }}-field-item" x-text="field.label" @click="selectField(field)"></button>
                    </template>
                </div>
            </template>

            <template x-if="step === 'configure' && selectedField()">
                <div class="{{ $prefix }}-configure" data-af-part="configure">
                    <p class="{{ $prefix }}-builder__title" x-text="selectedField().label"></p>

                    <label class="{{ $prefix }}-field">
                        <span class="{{ $prefix }}-field__label">Operator</span>
                        <select class="{{ $prefix }}-select" x-model="pending.operator" @change="onOperatorChange()">
                            <template x-for="op in availableOperators()" :key="op">
                                <option :value="op" x-text="operatorLabel(op)"></option>
                            </template>
                        </select>
                    </label>

                    <template x-if="needsValueInput()">
                        <div class="{{ $prefix }}-value">
                            @foreach ($inputViews as $inputView)
                                @include($inputView, ['prefix' => $prefix])
                            @endforeach
                        </div>
                    </template>

                    <div class="{{ $prefix }}-actions" data-af-part="actions">
                        <button type="button" class="{{ $prefix }}-back" @click="goBackToFields()">Back</button>
                        <button type="button" class="{{ $prefix }}-btn {{ $prefix }}-btn--primary" :disabled="!canAdd()" @click="addPending()">Apply</button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
