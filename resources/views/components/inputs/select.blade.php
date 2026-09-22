{{-- Select / set value input (input "select") --}}
<template x-if="selectedField().input === 'select'">
    <div class="{{ $prefix }}-input-group" data-af-part="input">
        {{-- single value (clause shape "single") --}}
        <template x-if="!isMultiValue()">
            <select class="{{ $prefix }}-select" x-model="pending.value">
                <option value="">&mdash;</option>
                <template x-for="opt in optionItems()" :key="opt.value">
                    <option :value="opt.value" x-text="opt.label"></option>
                </template>
            </select>
        </template>

        {{-- list of values (clause shape "multi") --}}
        <template x-if="isMultiValue()">
            <select class="{{ $prefix }}-select {{ $prefix }}-select--multiple" multiple x-model="pending.value">
                <template x-for="opt in optionItems()" :key="opt.value">
                    <option :value="opt.value" x-text="opt.label"></option>
                </template>
            </select>
        </template>
    </div>
</template>
